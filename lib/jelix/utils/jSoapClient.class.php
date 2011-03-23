<?php
/**
* @package     jelix
* @subpackage  utils
* @author      Laurent Jouanneau
* @copyright   2011 Laurent Jouanneau
* @link        http://jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

/**
* provide a soap client where configuration information are stored in a config file
* @package     jelix
* @subpackage  utils
*/
class jSoapClient {

    /**
     * loaded profiles
     * @var array
     */
    protected static $_profiles = null;

    /**
     * list of current soap client
     */
    protected static $_soapPool = array();

    /**
     * @param string $profile  the profile name
     */
    public static function get($profile = '') {
        $profile = self::getProfile($profile);
        $wsdl = null;
        if (isset($profile['wsdl'])) {
            $wsdl = $profile['wsdl'];
            if ($wsdl == '')
                $wsdl = null;
            unset ($profile['wsdl']);
        }

        // we set the name to avoid two connections for a same profile, when the given name
        // is an alias of a real profile and when we call getConnection several times,
        // with no name, with the alias name or with the real name.
        $name = $profile['name'];
        unset ($profile['name']);

        if (!isset(self::$_soapPool[$name])) {
            self::$_soapPool[$name] = new SoapClient($wsdl, $profile);
        }
        return self::$_soapPool[$name];
    }

    /**
    * load properties of a connector profile
    *
    * a profile is a section in the soapprofiles.ini.php file
    *
    * the given name can be a profile name (it should correspond to a section name
    * in the ini file), or an alias of a profile. An alias is a parameter name
    * in the global section of the ini file, and the value of this parameter
    * should be a profile name.
    *
    * @param string   $name  profile name or alias of a profile name. if empty, use the default profile
    * @param boolean  $noDefault  if true and if the profile doesn't exist, throw an error instead of getting the default profile
    * @return array  properties
    */
    public static function getProfile ($name='', $noDefault = false) {
        if (self::$_profiles === null) {
            $file = jApp::configPath('soapprofiles.ini.php');
            self::$_profiles = parse_ini_file($file, true);
        }

        if ($name == '')
            $name = 'default';
        $targetName = $name;

        // the name attribute created in this method will be the name of the connection
        // in the connections pool. So profiles of aliases and real profiles should have
        // the same name attribute.

        if (isset(self::$_profiles[$name])) {
            if (is_string(self::$_profiles[$name])) {
                $targetName = self::$_profiles[$name];
            }
            else { // this is an array, and so a section
                self::$_profiles[$name]['name'] = $name;
                return self::$_profiles[$name];
            }
        }
        // if the profile doesn't exist, we take the default one
        elseif (!$noDefault && isset(self::$_profiles['default'])) {
            if (is_string(self::$_profiles['default'])) {
                $targetName = self::$_profiles['default'];
            }
            else {
                self::$_profiles['default']['name'] = 'default';
                return self::$_profiles['default'];
            }
        }
        else {
            if ($name == 'default')
                throw new jException('jelix~soap.error.default.profile.unknown');
            else
                throw new jException('jelix~soap.error.profile.type.unknown',$name);
        }

        if (isset(self::$_profiles[$targetName]) && is_array(self::$_profiles[$targetName])) {
            self::$_profiles[$targetName]['name'] = $targetName;
            return self::$_profiles[$targetName];
        }
        else {
            throw new jException('jelix~soap.error.profile.unknown', $targetName);
        }
    }
}