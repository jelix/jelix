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
        $profile = jProfiles::get ('jsoapclient', $profile);
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

        // we set the name to avoid two connections for a same profile, when the given name
        // is an alias of a real profile and when we call getConnection several times,
        // with no name, with the alias name or with the real name.
        $name = $profile['_name'];
        unset ($profile['_name']);
        $cnx = jProfiles::getFromPool('jsoapclient', $name);
        if (!$cnx) {
            $cnx = new SoapClient($wsdl, $profile);;
            jProfiles::storeInPool('jsoapclient', $name, $cnx);
        }
        return $cnx;
    }
}