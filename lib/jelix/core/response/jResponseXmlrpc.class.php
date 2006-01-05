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
* Genérateur de réponse xmlrpc
* @see jResponse
*/

final class jResponseXmlRpc extends jResponse {
    /**
    * identifiant du générateur
    * @var string
    */
    protected $_type = 'xmlrpc';

    protected $errorCode = 0;
    protected $errorMessage = '';

    public $response = null;

    public function output(){
        if($this->errorCode != 0 || $this->errorMessage != '') return false;

        header("Content-Type: text/xml;charset=".$GLOBALS['gJConfig']->defaultCharset);
        $content = jXmlRpc::encodeResponse($this->response, $GLOBALS['gJConfig']->defaultCharset);
        header("Content-length: ".strlen($content));
        echo $content;
        return true;
    }

    public  function fetch(){
        if($this->errorCode != 0 || $this->errorMessage != '') return false;
        return jXmlRpc::encodeResponse($this->response, $GLOBALS['gJConfig']->defaultCharset);
    }

    public function outputErrors(){
        header("Content-Type: text/xml;charset=".$GLOBALS['gJConfig']->defaultCharset);
        $content = jXmlRpc::encodeFaultResponse($this->errorCode,$this->errorMessage, $GLOBALS['gJConfig']->defaultCharset);
        header("Content-length: ".strlen($content));
        echo $content;
    }


    /**
     * indique au générateur qu'il y a un message d'erreur/warning/notice à prendre en compte
     * cette méthode stocke le message d'erreur
     * @return boolean    true= arret immediat ordonné, false = on laisse le gestionnaire d'erreur agir en conséquence
     */
    public function addErrorMsg($type, $code, $message, $file, $line){
        $this->errorCode = $code;
        $this->errorMessage = "[$type] $message (file: $file, line: $line)";
        return true;
    }

}

?>