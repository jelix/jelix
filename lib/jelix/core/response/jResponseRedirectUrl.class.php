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
* Genérateur de réponse pour la redirection
* @see jResponse
*/

final class jResponseRedirectUrl extends jResponse {
    protected $_type = 'redirectUrl';

    /**
     * url vers laquelle rediriger
     */
    public $url = '';

    public function output(){
        if($this->hasErrors()) return false;
        header ('location: '.$this->url);
        return true;
    }

    /**
     * génère le contenu sans l'envoyer au navigateur
     * @return    string    contenu généré, ou false si il y a une erreur de génération
     */
    public function fetch(){
       if($this->hasErrors()) return false;
           return 'location: '.$this->url;
    }

    public function outputErrors(){

    }

}

?>