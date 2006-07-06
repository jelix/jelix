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
    public $action = '';

    public $params = array();

    public function output(){
       if($this->hasErrors()) return false;

        header ('location: '.jUrl::get($this->action, $this->params));
        return true;
    }

    public function outputErrors(){
         include_once(JELIX_LIB_RESPONSE_PATH.'jResponseHtml.class.php');
         $resp = new jResponseHtml();
         $resp->outputErrors();
    }

}

?>
