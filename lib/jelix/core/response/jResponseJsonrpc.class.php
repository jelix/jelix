<?php
/**
* @package     jelix
* @subpackage  core_response
* @author      Laurent Jouanneau
* @contributor Loic Mathaud
* @copyright   2005-2009 Laurent Jouanneau
* @copyright   2007 Loic Mathaud
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

/**
* Response for jsonrpc protocol
* @package  jelix
* @subpackage core_response
* @see jResponse
* @see http://json-rpc.org/wiki/specification
*/

final class jResponseJsonRpc extends jResponse {
    /**
    * @var string
    */
    protected $_type = 'jsonrpc';
    protected $_acceptSeveralErrors=false;

    /**
     * PHP data you want to return
     * @var mixed
     */
    public $response = null;


    public function output(){
        global $gJCoord;

        $this->_httpHeaders['Content-Type'] = "application/json";
        if($gJCoord->request->jsonRequestId !== null){
            $content = jJsonRpc::encodeResponse($this->response, $gJCoord->request->jsonRequestId);
            if($this->hasErrors()) return false;
            $this->_httpHeaders['Content-length'] = strlen($content);
            $this->sendHttpHeaders();
            echo $content;
        }else{
            if($this->hasErrors()) return false;
            $this->_httpHeaders['Content-length'] = '0';
            $this->sendHttpHeaders();
        }
        return true;
    }

    public function outputErrors(){
        global $gJCoord;
        if(count($gJCoord->errorMessages)){
            $e = $gJCoord->errorMessages[0];
            $errorCode = $e[1];
            $errorMessage = '['.$e[0].'] '.$e[2].' (file: '.$e[3].', line: '.$e[4].')';
            if ($e[5])
               $errorMessage .= "\n".$e[5];
        }else{
            $errorMessage = 'Unknown error';
            $errorCode = -1;
        }
        $this->clearHttpHeaders();
        $this->_httpStatusCode ='500';
        $this->_httpStatusMsg ='Internal Server Error';
        $this->_httpHeaders['Content-Type'] = "application/json";
        $content = jJsonRpc::encodeFaultResponse($errorCode,$errorMessage, $gJCoord->request->jsonRequestId);
        $this->_httpHeaders['Content-length'] = strlen($content);
        $this->sendHttpHeaders();
        echo $content;
    }
}

