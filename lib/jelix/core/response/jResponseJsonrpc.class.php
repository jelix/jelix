<?php
/**
* @package     jelix
* @subpackage  core
* @version     $Id$
* @author      Jouanneau Laurent
* @contributor
* @copyright   2005-2006 Jouanneau laurent
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

/**
* Genérateur de réponse jsonrpc
* @see jResponse
* @see http://json-rpc.org/specs.xhtml
*/

final class jResponseJsonRpc extends jResponse {
    /**
    * identifiant du générateur
    * @var string
    */
    protected $_type = 'jsonrpc';

    protected $errorCode = 0;
    protected $errorMessage = '';

    public $response = null;


    public function output(){
        global $gJCoord;
        if($this->errorCode != 0 || $this->errorMessage != '') return false;
        header("Content-Type: text/plain");
        if($gJCoord->request->params['id'] !== null){
            $content = jJsonRpc::encodeResponse($this->response, $gJCoord->request->params['id']);
            header("Content-length: ".strlen($content));
            echo $content;
        }else{
            header("Content-length: 0");
        }
        return true;
    }

    public function fetch(){
        global $gJCoord;
        if($this->errorCode != 0 || $this->errorMessage != '') return false;

        if($gJCoord->request->params['id'] !== null)
            return jJsonRpc::encodeResponse($this->response, $gJCoord->request->params['id']);
        else
            return ''; // dans le cas où la requete n'était qu'une notification
    }

    public function outputErrors(){
        global $gJCoord;
        header("Content-Type: text/plain");
        $content = jJsonRpc::encodeFaultResponse($this->errorCode,$this->errorMessage, $gJCoord->request->params['id']);
        header("Content-length: ".strlen($content));
        echo $content;
    }


    /**
     * indique au générateur qu'il y a un message d'erreur/warning/notice à prendre en compte
     * cette méthode stocke le message d'erreur
     * @return boolean    true= arret immediat ordonné, false = on laisse le gestionnaire d'erreur agir en conséquence
     */
    public function addErrorMsg($type, $code, $message, $file, $line){
        $this->errorCode = 1;
        $this->errorMessage = "[$type] $message (file: $file, line: $line)";
        return true;
    }

}

?>