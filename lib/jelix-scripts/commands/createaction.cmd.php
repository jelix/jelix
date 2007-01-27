<?php
/**
* @package     jelix-scripts
* @author      Jouanneau Laurent
* @contributor
* @copyright   2005-2006 Jouanneau laurent
* @link        http://www.jelix.org
* @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
*/


class createactionCommand extends JelixScriptCommand {

    public  $name = 'createaction';
    public  $allowed_options=array();
    public  $allowed_parameters=array('module'=>true,'name'=>true, 'controller'=>false, 'method'=>false);

    public  $syntaxhelp = "MODULE ACTION [CONTROLLER] [METHOD]";
    public  $help="
    Permet d'ajouter une nouvelle action

    MODULE : le nom du module concerné.
    ACTION (facultatif) : nom de l'action que vous voulez ajouter
    CONTROLLER (facultatif) :  nom du controleur concerné par l'action que
                               vous avez spécifié.
    METHOD (facultatif) : nom de la méthode du controleur que vous avez
                          spécifié.";


    public function run(){
       $path= $this->getModulePath($this->_parameters['module']);

       $controller = $this->getParam('controller','default');
       $method = $this->getParam('method','getDefault');

       $param= compact('controller','method');
       $param['name'] = $this->_parameters['name'];
       $param['module'] = $this->_parameters['module'];

       // TODO

    }
}


?>