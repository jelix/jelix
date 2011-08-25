<?php
/**
* @package    jelix
* @subpackage utils
* @author     Brice Tencé
* @contributor 
* @copyright    2011 Laurent Jouanneau
*
* @link        http://www.jelix.org
* @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*/

/**
 * jRootUrl is a class to retrieve a root URL from a ressource name. Root URLs are stored in config file.
 * @package    jelix
 * @subpackage utils
 */
class jRootUrl {
    /**
    * get the root url for a given ressource type
    * @param string $ressourceType Name of the ressource
    * @return string the root URL corresponding to this ressource, or basePath if unknown
    */
    public static function get($ressourceType){

        global $gJConfig;

        $rootUrl = jRootUrl::getRessourceValue($ressourceType);
        if( $rootUrl !== null ) {
            if( substr($rootUrl, 0, 7) !== 'http://' && substr($rootUrl, 0, 8) !== 'https://' // url is not absolute.
                && substr($rootUrl, 0, 1) !== '/' ) { //and is not relative to root
                   // so let's prepend basePath :
                    $rootUrl = $gJConfig->urlengine['basePath'] . $rootUrl;
            }
        } else {
            // basePath by default :
            $rootUrl = $gJConfig->urlengine['basePath'];
        }

        return $rootUrl;
    }


    /**
    * get the config value of an item in [rootUrls] section of config
    * @param string $ressourceType Name of the ressource
    * @return string the config value of this value, null if it does not exist
    */
    public static function getRessourceValue($ressourceType) {

        global $gJConfig;

        if( ! isset($gJConfig->rootUrls[$ressourceType]) ) {
            return null;
        } else {
            return $gJConfig->rootUrls[$ressourceType];
        }
    }
}

