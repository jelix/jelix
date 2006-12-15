<?php
/**
* @package     jelix
* @subpackage  core
* @author      Jouanneau Laurent
* @contributor
* @copyright   2006 Jouanneau laurent
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

#ifndef ENABLE_PHP_JSON
/**
 *
 */
require_once (LIB_PATH.'json/JSON.php');
#endif


/**
* Json response
* @package  jelix
* @subpackage core
* @see jResponse
* @since 1.0b1
*/
final class jResponseJson extends jResponse {
    protected $_acceptSeveralErrors=false;

    /**
     * datas in PHP you want to send
     * @var mixed
     */
    public $datas = null;


    public function output(){
        global $gJCoord;
        if($this->hasErrors()) return false;
        header("Content-Type: text/plain");
#ifdef ENABLE_PHP_JSON
        $content = json_encode($this->datas);
#else
        $json = new JSON(JSON_LOOSE_TYPE);
        $content = $json->encode($this->datas);
#endif
        header("Content-length: ".strlen($content));
        echo $content;
        return true;
    }

    public function outputErrors(){
        global $gJCoord;
        $message = array();
        if(count($gJCoord->errorMessages)){
           $e = $gJCoord->errorMessages[0];
           $message['errorCode'] = $e[1];
           $message['errorMessage'] = '['.$e[0].'] '.$e[2].' (file: '.$e[3].', line: '.$e[4].')';
        }else{
            $message['errorMessage'] = 'Unknow error';
            $message['errorCode'] = -1;
        }
        header("Content-Type: text/plain");
#ifdef ENABLE_PHP_JSON
        $content = json_encode($message);
#else
        $json = new JSON(JSON_LOOSE_TYPE);
        $content = $json->encode($message);
#endif
        header("Content-length: ".strlen($content));
        echo $content;
    }
}

?>