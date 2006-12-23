<?php
/**
* @package     jelix
* @subpackage  core_response
* @author      Laurent Jouanneau
* @contributor
* @copyright   2005-2006 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

/**
* Response for jsonrpc protocol
* @package  jelix
* @subpackage core_response
* @see jResponse
* @see http://json-rpc.org/specs.xhtml
*/

final class jResponseJsonRpc extends jResponse {
    /**
    * @var string
    */
    protected $_type = 'jsonrpc';
    protected $_acceptSeveralErrors=false;

    /**
     * PHP datas you want to return
     * @var mixed
     */
    public $response = null;


    public function output(){
        global $gJCoord;
        if($this->hasErrors()) return false;
        header("Content-Type: text/plain");
        if($gJCoord->request->jsonRequestId !== null){
            $content = jJsonRpc::encodeResponse($this->response, $gJCoord->request->jsonRequestId);
            header("Content-length: ".strlen($content));
            echo $content;
        }else{
            header("Content-length: 0");
        }
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
        header("Content-Type: text/plain");
        $content = jJsonRpc::encodeFaultResponse($errorCode,$errorMessage, $gJCoord->request->jsonRequestId);
        header("Content-length: ".strlen($content));
        echo $content;
    }
}

?>