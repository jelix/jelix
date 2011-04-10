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
* provide a soap client where configuration information are stored in the profile file
* @package     jelix
* @subpackage  utils
*/
class jSoapClient {

    /**
     * @param string $profile  the profile name
     */
    public static function get($profile = '') {
        return jProfiles::getOrStoreInPool('jsoapclient', $profile, array('jSoapClient', '_getClient'));
    }

    /**
     * callback method for jprofiles. Internal use.
     */
    public static function _getClient($profile) {
        $wsdl = null;
        if (isset($profile['wsdl'])) {
            $wsdl = $profile['wsdl'];
            if ($wsdl == '')
                $wsdl = null;
            unset ($profile['wsdl']);
        }
        if (isset($profile['trace'])) {
            $profile['trace'] = intval($profile['trace']); // SoapClient recognize only true integer
        }
        if (isset($profile['exceptions'])) {
            $profile['exceptions'] = intval($profile['exceptions']); // SoapClient recognize only true integer
        }
        if (isset($profile['connection_timeout'])) {
            $profile['connection_timeout'] = intval($profile['connection_timeout']); // SoapClient recognize only true integer
        }
        unset ($profile['_name']);
        return new SoapClient($wsdl, $profile);
    }
}