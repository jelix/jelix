<?php
/**
* @package     jelix
* @subpackage  core_response
* @author      Laurent Jouanneau
* @contributor
* @copyright   2005-2007 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/


/**
* xmlrpc response
* @package  jelix
* @subpackage core_response
* @see jResponse
*/
final class jResponseXmlRpc extends jResponse {
    /**
    * @var string
    */
    protected $_type = 'xmlrpc';
    protected $_acceptSeveralErrors=false;

    /**
     * PHP Data to send into the response
     */
    public $response = null;

    public function output(){

        $content = jXmlRpc::encodeResponse($this->response, $GLOBALS['gJConfig']->charset);

        if($this->hasErrors()) return false;

        $this->_httpHeaders["Content-Type"]="text/xml;charset=".$GLOBALS['gJConfig']->charset;
        $this->_httpHeaders["Content-length"]=strlen($content);
        $this->sendHttpHeaders();
        echo $content;
        return true;
    }

    public function outputErrors(){
        global $gJCoord;
        if(count($gJCoord->errorMessages)){
            $e = $gJCoord->errorMessages[0];
            $errorCode = $e[1];
            $errorMessage = '['.$e[0].'] '.$e[2].' (file: '.$e[3].', line: '.$e[4].')';
        }else{
            $errorMessage = 'Unknow error';
            $errorCode = -1;
        }
        $this->clearHttpHeaders();
        $content = jXmlRpc::encodeFaultResponse($errorCode,$errorMessage, $GLOBALS['gJConfig']->charset);

        header("HTTP/1.0 500 Internal Server Error");
        header("Content-Type: text/xml;charset=".$GLOBALS['gJConfig']->charset);
        header("Content-length: ".strlen($content));
        echo $content;
    }
}

?>