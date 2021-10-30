<?php
/**
 * @package     jelix
 * @subpackage  db
 *
 * @author      Laurent Jouanneau
 * @contributor Yannick Le Guédart, Laurent Raufaste, Julien Issler
 * @contributor Christophe Thiriot
 *
 * @copyright   2005-2021 Laurent Jouanneau, 2008 Laurent Raufaste
 * @copyright   2011 Julien Issler
 *
 * API ideas of this class were get originally from the Copix project (CopixDbFactory, Copix 2.3dev20050901, http://www.copix.org)
 * No lines of code are copyrighted by CopixTeam
 *
 * @see      http://www.jelix.org
 * @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */

use Jelix\Database\Log\QueryLoggerInterface;
use Jelix\Database\Utilities;
use Psr\Log\LoggerInterface;
use Jelix\Core\Profiles;

require_once JELIX_LIB_PATH.'db/jSQLLogMessage.php';
require_once JELIX_LIB_PATH.'db/jDbLogger.php';

/**
 * factory for database connector and other db utilities.
 *
 * @package  jelix
 * @subpackage db
 */
class jDb
{
    /**
     * return a database connector. It uses a temporary pool of connection to reuse
     * currently opened connections.
     *
     * @param string $name profile name to use. if empty, use the default one
     *
     * @return Jelix\Database\ConnectionInterface the connector
     */
    public static function getConnection($name = '')
    {
        return Profiles::getOrStoreInPool('jdb', $name, array('jDb', '_createConnector'));
    }

    /**
     * create a new Helpers object.
     *
     * @param string $name profile name to use. if empty, use the default one
     *
     * @return \Jelix\Database\Helpers
     */
    public static function getDbWidget($name = null)
    {
        return new \Jelix\Database\Helpers(self::getConnection($name));
    }

    /**
     * call it to test a profile (during an install for example).
     *
     * @param array $profile profile properties
     *
     * @return bool true if properties are ok
     */
    public static function testProfile($profile)
    {
        try {
            self::_createConnector($profile);
            $ok = true;
        } catch (Exception $e) {
            $ok = false;
        }

        return $ok;
    }

    /**
     * create a connector. internal use (callback method for jProfiles).
     *
     * @param array $profile profile properties
     *
     * @throws \Jelix\Database\Exception
     *
     * @return \Jelix\Database\ConnectionInterface database connector
     */
    public static function _createConnector($profile)
    {
        $connector = \Jelix\Database\Connection::createWithNormalizedParameters($profile);

        if (isset($profile['debug']) && $profile['debug']) {
            $logger = new jDbLogger();
            $queryLogger = new jSQLLogMessage($logger);
            $connector->setQueryLogger($queryLogger);
        }

        return $connector;
    }

    /**
     * @deprecated use \Jelix\Database\Utilities::floatToStr() instead
     * @param mixed $value
     * @see \Jelix\Database\Utilities::floatToStr()
     */
    public static function floatToStr($value)
    {
        return Utilities::floatToStr($value);
    }

    /**
     * @param $path
     *
     * @return string
     * @throws Exception
     * @see jdbProfilesCompiler
     */
    public static function parseSqlitePath($path)
    {
        if (preg_match('/^(app|lib|var|temp|www)\:/', $path)) {
            $path = \jFile::parseJelixPath($path);
        } elseif ($path[0] == '/' || // *nix path
                  preg_match('!^[a-z]\\:(\\\\|/)[a-z]!i', $path) // windows path
        ) {
            if (!file_exists($path) && !file_exists(dirname($path))) {
                throw new \Exception('sqlite3 connector: unknown database path scheme');
            }
        } else {
            $path = \Jelix\Core\App::varPath('db/sqlite3/'.$path);
        }
        return $path;
    }
}
