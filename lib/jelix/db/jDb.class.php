<?php
/**
 * @package     jelix
 * @subpackage  db
 *
 * @author      Laurent Jouanneau
 * @contributor Laurent Raufaste
 * @contributor Julien Issler
 *
 * @copyright   2005-2025 Laurent Jouanneau, 2008 Laurent Raufaste
 * @copyright   2011 Julien Issler
 *
 * API ideas of this class were get originally from the Copix project (CopixDbFactory, Copix 2.3dev20050901, http://www.copix.org)
 * No lines of code are copyrighted by CopixTeam
 *
 * @see      http://www.jelix.org
 * @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */
use Jelix\Core\Profiles;

/**
 * factory for database connector and other db utilities.
 *
 * @package  jelix
 * @subpackage db
 */
class jDb
{
    /**
     * Return a database connector. It uses a temporary pool of connection to reuse
     * currently opened connections.
     *
     * @param string $name profile name to use. if empty, use the default one
     *
     * @return \Jelix\Database\ConnectionInterface|jDbConnection the connector
     */
    public static function getConnection($name = '')
    {
        return Profiles::getConnectorFromCallback('jdb', $name, array('jDb', '_createConnector'));
    }

    /**
     * create a new Helpers object.
     *
     * @param string $name profile name to use. if empty, use the default one
     *
     * @return \Jelix\Database\Helpers|jDbWidget
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
     * @return \Jelix\Database\ConnectionInterface|jDbConnection database connector
     */
    public static function _createConnector($profile)
    {
        return \Jelix\Database\Connection::createWithNormalizedParameters($profile);
    }

    /**
     * perform a convertion float to str. It takes care about the decimal separator
     * which should be a '.' for SQL. Because when doing a native convertion float->str,
     * PHP uses the local decimal separator, and so, we don't want that.
     *
     * @since 1.1.11
     * @deprecated Use \Jelix\Database\Utilities::floatToStr() instead
     *
     * @param mixed $value
     */
    public static function floatToStr($value)
    {
        return \Jelix\Database\Utilities::floatToStr($value);
    }
}
