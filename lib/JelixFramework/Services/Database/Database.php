<?php
/**
 * @author      Laurent Jouanneau
 *
 * @copyright   2005-2024 Laurent Jouanneau
 *
 * @see      http://www.jelix.org
 * @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */
namespace Jelix\Services\Database;

use Jelix\Core\Profiles;

/**
 * factory for database connector and other db utilities.
 *
 * @package  jelix
 * @subpackage db
 */
class Database
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
        return Profiles::getConnector('jdb', $name);
    }

    /**
     * create a new Helpers object.
     *
     * @param string $name profile name to use. if empty, use the default one
     *
     * @return \Jelix\Database\Helpers
     */
    public static function getHelpers($name = null)
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
        } catch (\Exception $e) {
            $ok = false;
        }

        return $ok;
    }


}
