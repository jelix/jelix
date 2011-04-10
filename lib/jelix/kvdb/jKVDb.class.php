<?php
/**
 * @package     jelix
 * @subpackage  kvdb
 * @author      Yannick Le Guédart
 * @contributor  Laurent Jouanneau
 * @copyright   2009 Yannick Le Guédart, 2010-2011 Laurent Jouanneau
 *
 * @link     http://www.jelix.org
 * @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */

/**
 * main class to access to key-value storage databases
 */
class jKVDb {

	protected function __construct() { } // the class is only static

    /**
	 * get the jKVConnection object associated to a given profile name
	 *
	 * @param string $name
	 * @return jKVConnection
	 */
	public static function getConnection($name = null) {
		$profile = jProfiles::get ('jkvdb', $name);

        // we set the name to avoid two connections for a same profile, when the given name
        // is an alias of a real profile and when we call getConnection several times,
        // with no name, with the alias name or with the real name.
        $name = $profile['_name'];
        $cnx = jProfiles::getFromPool('jkvdb', $name);
        if (!$cnx) {
            $cnx = self::_createConnector($profile);
            jProfiles::storeInPool('jkvdb', $name, $cnx);
        }
		return $cnx;
	}

    /**
	 * get the profile from the INI file. If no $name paramter is given, then
	 * the default profile is returned, if defined.
	 *
	 * @param string $name
	 * @return array
	 * @deprecated use jProfiles::get instead
	 */
	public static function getProfile($name = null) {
		return jProfiles::get('jkvdb', $name);
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

        // If no driver is specified, let's throw an exception
        if (! isset($profile['driver'])) {
            throw new jException(
                'jelix~kvstore.error.driver.notset', $profile['name']);
        }

        $connector = jApp::loadPlugin($profile['driver'], 'kvdb', '.kvdriver.php', $profile['driver'] . 'KVDriver', $profile);
        //if (is_null($connector)) {
        //    throw new jException('jelix~errors.kvdb.driver.notfound',$profile['driver']);
        //}

        return $connector;
	}
}