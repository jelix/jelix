<?php
/**
* @package    jelix
* @subpackage utils
* @author     Brice Tencé
* @contributor 
* @copyright  
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
     * constructor.
     */
    function __construct($params=array()){
    }

    /**
    * get the root url for a given ressource type
    * @param string $ressourceType Name of the ressource
    * @return string the root URL corresponding to this ressource, or basePath if unknown
    */
    public static function get ($ressourceType){
        global $gJConfig;

        if( isset($gJConfig->rootUrls) && isset($gJConfig->rootUrls[$ressourceType]) ) {
            $rootUrl = $gJConfig->rootUrls[$ressourceType];

            if( substr( $rootUrl, 0, 7) !== 'http://' ) {
                #url is not absolute. Let's prepend basePath :
                $rootUrl = $gJConfig->urlengine['basePath'] . $rootUrl;
            }
        } else {
            #basePath by default :
            $rootUrl = $gJConfig->urlengine['basePath'];
        }

        return $rootUrl;
    }
}

