<?php
/**
 * @package    jelix
 * @subpackage core
 *
 * @author     Julien Issler
 * @contributor Laurent Jouanneau
 *
 * @copyright  2007-2009 Julien Issler, 2008-2025 Laurent Jouanneau
 *
 * @see       http://www.jelix.org
 * @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 *
 * @since 1.0
 */

/**
 * session management class of the jelix core.
 *
 * @package  jelix
 * @subpackage core
 *
 * @since 1.0
 */
class jSession
{
    protected static $_params;

    /**
     * start a session.
     */
    public static function start()
    {
        $params = &jApp::config()->sessions;

        // do not start the session if the request is made from the command line or if sessions are disabled in configuration
        if (jApp::coord()->request instanceof jCmdLineRequest || !$params['start']) {
            return false;
        }

        $cookieOptions = array(
            'path' => '/',
            'secure' => $params['cookieSecure'], // true to send the cookie only on a secure channel
            'httponly' => $params['cookieHttpOnly'],
            'lifetime' => $params['cookieLifetime'],
        );

        if (!$params['shared_session']) {
            //make sure that the session cookie is only for the current application
            $cookieOptions['path'] = jApp::urlBasePath();
        }

        if (PHP_VERSION_ID < 70300) {
            session_set_cookie_params($cookieOptions['lifetime'], $cookieOptions['path'], '', $cookieOptions['secure'], $cookieOptions['httponly']);
        } else {
            if ($params['cookieSameSite'] != '') {
                $cookieOptions['samesite'] = $params['cookieSameSite'];
            }
            session_set_cookie_params($cookieOptions);
        }

        if ($params['storage'] != '') {

            /* on debian/ubuntu (maybe others), garbage collector launch probability is set to 0
               and replaced by a simple cron job which is not enough for jSession (different path, db storage, ...),
               so we set it to 1 as PHP's default value */
            if (!ini_get('session.gc_probability')) {
                ini_set('session.gc_probability', '1');
            }

            switch ($params['storage']) {
                case 'dao':
                    session_set_save_handler(
                        array(__CLASS__, 'daoOpen'),
                        array(__CLASS__, 'daoClose'),
                        array(__CLASS__, 'daoRead'),
                        array(__CLASS__, 'daoWrite'),
                        array(__CLASS__, 'daoDestroy'),
                        array(__CLASS__, 'daoGarbageCollector')
                    );
                    self::$_params = $params;

                    break;

                case 'files':
                    session_save_path($params['files_path']);

                    break;
            }
        }

        if ($params['name'] != '') {
            if (!preg_match('#^[a-zA-Z0-9]+$#', $params['name'])) {
                // regexp check because session name can only be alpha numeric according to the php documentation
                throw new jException('jelix~errors.jsession.name.invalid');
            }
            session_name($params['name']);
        }

        if (isset($params['_class_to_load'])) {
            foreach ($params['_class_to_load'] as $file) {
                require_once $file;
            }
        }

        session_start();
        if (isset($_SESSION['_destroyed'])) {
            if ($_SESSION['_destroyed'] < time()-180) {
                // Should not happen usually. This could be attack or due to unstable network.
                session_destroy();
                session_start();
            }
            else if (isset($_SESSION['_new_session_id'])) {
                // Not fully expired yet. Could be lost cookie by unstable network.
                // Try again to set proper session ID cookie.
                session_write_close();
                session_id($_SESSION['_new_session_id']);
                // New session ID should exist
                session_start();
            }
        }
        return true;
    }

    /**
     * end a session.
     */
    public static function end()
    {
        session_write_close();

        return true;
    }

    public static function regenerateId($keepSessionData)
    {
        // New session ID is required to set proper session ID
        // when session ID is not set due to unstable network.
        $newSessionId = session_create_id();
        $_SESSION['_new_session_id'] = $newSessionId;

        // Set destroy timestamp
        $_SESSION['_destroyed'] = time();

        // Write and close current session;
        self::end();

        // We have to turn off strict mode here
        // because new session_id won't be on file
        // and we'll get another session_id
        // from session_start()
        $wasInStrictMode = false;
        if (1 == ini_get('session.use_strict_mode')) {
            ini_set('session.use_strict_mode', 0);
            $wasInStrictMode = true;
        }

        $sessionVars = $_SESSION;

        // Start session with new session ID
        session_id($newSessionId);
        self::start();

        // New session does not need them
        unset($sessionVars['_destroyed']);
        unset($sessionVars['_new_session_id']);

        if ($keepSessionData) {
            $_SESSION = $sessionVars;
        }

        if ($wasInStrictMode) {
            @ini_set('session.use_strict_mode', 1);
        }
    }

    public static function isStarted()
    {
        if (php_sapi_name() !== 'cli') {
            return session_status() === PHP_SESSION_ACTIVE;
        }

        return false;
    }

    protected static function _getDao()
    {
        if (isset(self::$_params['dao_db_profile']) && self::$_params['dao_db_profile']) {
            $dao = jDao::get(self::$_params['dao_selector'], self::$_params['dao_db_profile']);
        } else {
            $dao = jDao::get(self::$_params['dao_selector']);
        }

        return $dao;
    }

    /**
     * dao handler for session stored in database.
     *
     * @param mixed $save_path
     * @param mixed $session_name
     */
    public static function daoOpen($save_path, $session_name)
    {
        return true;
    }

    /**
     * dao handler for session stored in database.
     */
    public static function daoClose()
    {
        return true;
    }

    /**
     * dao handler for session stored in database.
     *
     * @param mixed $id
     */
    public static function daoRead($id)
    {
        $session = self::_getDao()->get($id);

        if (!$session) {
            return '';
        }

        return $session->data;
    }

    /**
     * dao handler for session stored in database.
     *
     * @param mixed $id
     * @param mixed $data
     */
    public static function daoWrite($id, $data)
    {
        $dao = self::_getDao();

        $session = $dao->get($id);
        if (!$session) {
            $session = $dao->createRecord();
            $session->id = $id;
            $session->data = $data;
            $now = date('Y-m-d H:i:s');
            $session->creation = $now;
            $session->access = $now;
            $dao->insert($session);
        } else {
            $session->data = $data;
            $session->access = date('Y-m-d H:i:s');
            $dao->update($session);
        }

        return true;
    }

    /**
     * dao handler for session stored in database.
     *
     * @param mixed $id
     */
    public static function daoDestroy($id)
    {
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 42000, '/');
        }

        self::_getDao()->delete($id);

        return true;
    }

    /**
     * dao handler for session stored in database.
     *
     * @param mixed $maxlifetime
     */
    public static function daoGarbageCollector($maxlifetime)
    {
        $date = new jDateTime();
        $date->now();
        $date->sub(0, 0, 0, 0, 0, $maxlifetime);
        self::_getDao()->deleteExpired($date->toString(jDateTime::DB_DTFORMAT));

        return true;
    }
}
