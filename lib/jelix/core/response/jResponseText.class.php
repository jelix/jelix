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
* Générateur de réponse Text
*/

class jResponseText extends jResponse {
    /**
    * identifiant du générateur de sortie
    * @var string
    */
    protected $_type = 'text';

    /**
     * contenu
     * @var string
     */
    public $content = '';

    protected $_charset;

    /**
    * Contruction et initialisation
    */
    function __construct ($attributes=array()){
        $this->_charset = $GLOBALS['gJConfig']->defaultCharset;
        parent::__construct($attributes);
    }

    /**
     * génère le contenu et l'envoi au navigateur.
     * @return boolean    true si la génération est ok, false sinon
     */
    public function output(){
        global $gJConfig;
        $this->_httpHeaders['Content-Type']='text/plain;charset='.$this->_charset;
        $this->_httpHeaders['Content-length']=strlen($this->content);
        $this->sendHttpHeaders();
        echo $this->content;
        return true;
    }

    public function outputErrors(){
        global $gJConfig;
        header('Content-Type: text/plain;charset='.$gJConfig->defaultCharset);
        if($this->hasErrors()){
            foreach( $GLOBALS['gJCoord']->errorMessages  as $e){
               echo '['.$e[0].' '.$e[1].'] '.$e[2]." \t".$e[3]." \t".$e[4]."\n";
            }
        }else{
            echo "[unknow error]\n";
        }
    }
}
?>