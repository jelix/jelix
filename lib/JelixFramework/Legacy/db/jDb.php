<?php
/**
 * @package     jelix
 * @subpackage  db
 *
 * @author      Laurent Jouanneau
 *
 * @copyright   2005-2025 Laurent Jouanneau
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
     * @return \Jelix\Database\ConnectionInterface the connector
     */
    public static function getConnection($name = '')
    {
        /** @var \Jelix\Database\ConnectionInterface $conn */
        $conn = Profiles::getConnector('jdb', $name);
        return $conn;
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
            \Jelix\Database\Connection::createWithNormalizedParameters($profile);
            $ok = true;
        } catch (Exception $e) {
            $ok = false;
        }

        return $ok;
    }

    /**
     * @param mixed $value
     * @deprecated use \Jelix\Database\Utilities::floatToStr() instead
     * @see \Jelix\Database\Utilities::floatToStr()
     */
    public static function floatToStr($value)
    {
        return Utilities::floatToStr($value);
    }
}
