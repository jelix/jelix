<?php
/**
 * @package     jelix
 * @subpackage  db
 *
 * @author      Laurent Jouanneau
 *
 * @copyright   2005-2024 Laurent Jouanneau
 *
 * @see      http://www.jelix.org
 * @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */

use Jelix\Database\Utilities;
use Jelix\Core\Profiles;

/**
 * factory for database connector and other db utilities.
 *
 * @package  jelix
 * @subpackage db
 * @deprecated use Jelix\Services\Database\Database instead
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
        return Profiles::getConnector('jdb', $name);
    }

    /**
     * create a new Helpers object.
     *
     * @param string $name profile name to use. if empty, use the default one
     *
     * @return \Jelix\Database\Helpers
     * @deprecated use Jelix\Services\Database\Database::getHelpers() instead
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
            $connector = \Jelix\Database\Connection::createWithNormalizedParameters($profile);
            $ok = true;
        } catch (Exception $e) {
            $ok = false;
        }

        return $ok;
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
     * @deprecated use \Jelix\Services\Database\DbProfilePlugin::parseSqlitePath() instead)
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
