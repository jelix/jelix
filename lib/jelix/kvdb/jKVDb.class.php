<?php
/**
 * @package     jelix
 * @subpackage  kvdb
 * @author      Yannick Le Guédart
 * @contributor  Laurent Jouanneau
 * @copyright   2009 Yannick Le Guédart, 2010 Laurent Jouanneau
 *
 * @link     http://www.jelix.org
 * @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */

define('KVDB_PROFILE_FILE', JELIX_APP_CONFIG_PATH . 'kvprofiles.ini.php');

/**
 * main class to access to key-value storage databases
 */
class jKVDb {

    /**
	 * Array of the profiles in the profile file.
	 *
	 * @var array
	 * @see KVDB_PROFILE_FILE
	 */    
    static private $_profiles = null;
    
    /**
	 * Array of the jKVConnection.
	 * @var array 
	 */
 	static private $_cnxPool = array();

	protected function __construct() { } // the class is only static

    /**
	 * get the jKVConnection object associated to a given profile name
	 *
	 * @param string $name
	 * @return jKVConnection
	 */
	public static function getConnection($name = null) {
		$profile = self::getProfile($name);

        // If the name is not set, and no exception was thrown while getting the
        // profile, then we got the default profile. Torhandle the $_cnxPool
        // array acorrectly, we sets the $name to the default profile name.
		if (is_null($name)) {
			$name = $profile['name'];
		}

        // If the connector to the requested KVDb doesn't exists yet, let's
        // create it
		if(! isset(self::$_cnxPool[$name])) {
			self::$_cnxPool[$name] = self::_createConnector($profile);
		}

        return self::$_cnxPool[$name];
	}

    /**
	 * get the profile from the INI file. If no $name paramter is given, then
	 * the default profile is returned, if defined.
	 *
	 * @param string $name
	 * @return array
	 */
	public static function getProfile($name = null) {

        // The profile file has not been parsed yet, so we do that. The result
        // is stored in the $_profiles static private variable.
 		if (is_null(self::$_profiles)) {
			self::$_profiles = parse_ini_file(KVDB_PROFILE_FILE, true);
		}

        // If no name is provided, we look for the default profile an set the
        // name accordingly
		if (is_null($name)) {
			if (isset (self::$_profiles['default'])) {
				$name = self::$_profiles['default'];
            }
			else {
				throw new jException (
                    'jelix~kvstore.error.default.profile.unknown');
            }
		}

        // Verifying the requested profile
		if (! isset(self::$_profiles[$name])
                or ! is_array(self::$_profiles[$name]))
        {
            throw new jException('jelix~kvstore.error.profile.unknown', $name);
		}

        // Returning the requested profile
        self::$_profiles[$name]['name'] = $name;

        return self::$_profiles[$name];
	}

    /**
	 * Creates a jKVConnection object for the given profile, stores it in the
	 * singleton and the returns it.
	 *
	 * @param array $profile
	 *
	 * @return object jKVConnection
	 */    
	private static function _createConnector($profile) {
        global $gJCoord;

        // If no driver is specified, let's throw an exception
        if (! isset($profile['driver'])) {
            throw new jException(
                'jelix~kvstore.error.driver.notset', $profile['name']);
        }

        $connector = $gJCoord->loadPlugin($profile['driver'], 'kvdb', '.kvdriver.php', $profile['driver'] . 'KVDriver', $profile);
        //if (is_null($connector)) {
        //    throw new jException('jelix~errors.kvdb.driver.notfound',$profile['driver']);
        //}

        return $connector;
	}
}