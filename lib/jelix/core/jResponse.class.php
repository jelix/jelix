<?php
/**
* @package    jelix
* @subpackage core
* @version    $Id$
* @author     Jouanneau Laurent
* @contributor
* @copyright  2005-2006 Jouanneau laurent
* @link        http://www.jelix.org
* @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/


/**
* classe de base pour l'objet  chargé de controler et de formater
* la réponse renvoyée au navigateur
*/

abstract class jResponse {
    /**
    * identifiant du générateur de sortie
    * @var string
    */
    protected  $_type = null;

    protected $_errorMessages=array();

    protected $_attributes = array();

    /**
    * Contruction et initialisation
    */
    function __construct ($attributes){
       $this->_attributes = $attributes;
    }

    /**
     * génère le contenu et l'envoi au navigateur.
     * Il doit tenir compte des appels éventuels à addErrorMsg
     * @return boolean    true si la génération est ok, false sinon
     */
    abstract public function output();

    /**
     * génère le contenu sans l'envoyer au navigateur
     * @return    string    contenu généré ou false si il y a une erreur de génération
     */
    abstract public function fetch();

    /**
     * affiche les erreurs graves
     */
    abstract public function outputErrors();


    /**
     * indique au générateur qu'il y a un message d'erreur/warning/notice à prendre en compte
     * cette méthode stocke le message d'erreur
     * @param  string $type  type d'erreur dont la valeur est l'une du tableau codeString de la config du gestionnaire d'erreur
     * @param  string $code  code d'erreur (non utilisé en PHP4)
     * @return boolean    true= arret immediat ordonné, false = on laisse le gestionnaire d'erreur agir en conséquence
     */
    public function addErrorMsg($type, $code, $message, $file, $line){
        $this->_errorMessages[] = array($type, $code, $message, $file, $line);
        return false;
    }

    public final function getType(){ return $this->_type;}
}
?>