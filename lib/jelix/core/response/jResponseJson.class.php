<?php
/**
* @package     jelix
* @subpackage  core_response
* @author      Laurent Jouanneau
* @contributor Loic Mathaud
* @copyright   2006-2010 Laurent Jouanneau
* @copyright   2007-2008 Loic Mathaud
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

#ifnot ENABLE_PHP_JSON
/**
 *
 */
require_once (LIB_PATH.'json/JSON.php');
#endif


/**
* Json response
* @package  jelix
* @subpackage core_response
* @see jResponse
* @since 1.0b1
*/
final class jResponseJson extends jResponse {

    /**
     * data in PHP you want to send
     * @var mixed
     */
    public $data = null;


    public function output(){
        global $gJCoord;
        $this->_httpHeaders['Content-Type'] = "application/json";
#if ENABLE_PHP_JSON
        $content = json_encode($this->data);
#else
        $json = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);
        $content = $json->encode($this->data);
#endif
        $this->_httpHeaders['Content-length'] = strlen($content);
        $this->sendHttpHeaders();
        echo $content;
        return true;
    }

    public function outputErrors(){
        global $gJCoord;
        $message = array();
        $message['errorMessage'] = $gJCoord->getGenericErrorMessage();
        $e = $gJCoord->getErrorMessage();
        if($e){
            $message['errorCode'] = $e->getCode();
        }else{
            $message['errorCode'] = -1;
        }
        $this->clearHttpHeaders();
        $this->_httpStatusCode ='500';
        $this->_httpStatusMsg ='Internal Server Error';
        $this->_httpHeaders['Content-Type'] = "application/json";
#if ENABLE_PHP_JSON
        $content = json_encode($message);
#else
        $json = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);
        $content = $json->encode($message);
#endif
        $this->_httpHeaders['Content-length'] = strlen($content);
        $this->sendHttpHeaders();
        echo $content;
    }
}

