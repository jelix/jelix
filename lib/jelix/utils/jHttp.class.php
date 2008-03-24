<?php
/**
* @package     jelix
* @subpackage  utils
* @author      Laurent Jouanneau
* @contributor 
* @copyright   2008 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

require(LIB_PATH.'clearbricks/net/class.net.socket.php');
require(LIB_PATH.'clearbricks/net.http/class.net.http.php');

/**
 * To send http request
 * @package    jelix
 * @subpackage utils
 * @see netHttp
 */
class jHttp extends netHttp {
    protected $user_agent = 'Clearbricks/Jelix HTTP Client';

    protected function debug($msg,$object=false){
        if ($this->debug) {
            if($object) {
                jLog::dump($object, 'jhttp debug, '.$msg);
            }
            else {
                jLog::log('jhttp debug, '.$msg);
            }
        }
    }
}

