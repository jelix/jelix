<?php
/**
* @package     jelix
* @subpackage  core_response
* @author      Sylvain de Vathaire
* @contributor 
* @copyright   2008 Sylvain de Vathaire
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

/**
* Response for soap web services
* @package  jelix
* @subpackage core_response
* @see jResponse
*/
final class jResponseSoap extends jResponse {
    /**
    * @var string
    */
    protected $_type = 'soap';
    protected $_acceptSeveralErrors=false;

    /**
     * PHP data you want to return
     * @var mixed
     */
    public $data = null;


    public function output(){
        if($this->hasErrors()) return false;
        return true;
    }

    public function outputErrors(){
        global $gJCoord, $gJConfig;
 
       if(count($gJCoord->errorMessages)){
            $e = $gJCoord->errorMessages[0];
            $errorCode = $e[1];
            $errorMessage = '['.$e[0].'] '.$e[2].' (file: '.$e[3].', line: '.$e[4].')';
        }else{
            $errorMessage = 'Unknow error';
            $errorCode = -1;
        }

        //soapFault param have to be UTF-8 encoded (soapFault seems to not use the encoding param of the SoapServer)
        if($gJConfig->charset != 'UTF-8'){
            $errorCode  = utf8_encode($errorCode);
            $errorMessage = utf8_encode($errorMessage);
        }
        $soapServer = $gJCoord->getSoapServer();
        $soapServer->fault($errorCode, $errorMessage);
    }
}
