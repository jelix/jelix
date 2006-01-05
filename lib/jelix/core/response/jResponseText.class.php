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


    /**
     * génère le contenu et l'envoi au navigateur.
     * @return boolean    true si la génération est ok, false sinon
     */
    public function output(){
        global $gJConfig;
        header('Content-Type: text/plain;charset='.$gJConfig->defaultCharset);
        header("Content-length: ".strlen($this->content));
        echo $this->content;
        return true;
    }

    /**
     * génère le contenu sans l'envoyer au navigateur
     * @return    string    contenu généré ou false si il y a une erreur de génération
     */
    public function fetch(){
        return $this->content;
    }


    public function outputErrors(){
        global $gJConfig;
        header('Content-Type: text/plain;charset='.$gJConfig->defaultCharset);
        echo implode("\n",$this->_errorMessages);
    }


    /**
     * indique au générateur qu'il y a un message d'erreur/warning/notice à prendre en compte
     * cette méthode stocke le message d'erreur
     * @return boolean    true= arret immediat ordonné, false = on laisse le gestionnaire d'erreur agir en conséquence
     */
    public function addErrorMsg($type, $code, $message, $file, $line){
        $this->_errorMessages[] = "[$type $code] $message \t$file \t$line\n";
        return false;
    }
}
?>