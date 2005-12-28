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

final class jResponseRedirect extends jResponse {
    /**
    * identifiant du générateur
    * @var string
    */
    protected $_type = 'redirect';

    /**
     * selecteur d'action vers lequel rediriger
     */
    public $selector = '';

    public $selectorParams = array();

    public function output(){
        if(count($this->_errorMessage)) return false;
        header ('location: '.jUrl::get($this->selector, $this->selectorParams));
        return true;
    }

    public function fetch(){
        if(count($this->_errorMessage)) return false;
        return 'location: '.jUrl::get($this->selector, $this->selectorParams);
    }

    public function outputErrors(){

    }

}

?>