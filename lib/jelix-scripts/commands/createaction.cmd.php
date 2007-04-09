<?php
/**
* @package     jelix-scripts
* @author      Jouanneau Laurent
* @contributor
* @copyright   2005-2007 Jouanneau laurent
* @link        http://www.jelix.org
* @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
*/


class createactionCommand extends JelixScriptCommand {

    public  $name = 'createaction';
    public  $allowed_options=array();
    public  $allowed_parameters=array('module'=>true,'name'=>true, 'controller'=>false);

    public  $syntaxhelp = "MODULE ACTION [CONTROLLER]";
    public  $help=array(
            "fr"=>"
    NON IMPLEMENTEE !

    Permet d'ajouter une nouvelle action

    MODULE : le nom du module concerné.
    ACTION (facultatif) : nom de l'action que vous voulez ajouter
    CONTROLLER (facultatif) :  nom du controleur concerné par l'action que
                               vous avez spécifié.",
            "en"=>"
    NOT IMPLEMENTED!

    You can add a new action with this command

    MODULE : the module name on which you want to add an action.
    ACTION (optional) : action name
    CONTROLLER (optional) :  controller name which will contain the action",
            );


    public function run(){
       $path= $this->getModulePath($this->_parameters['module']);

       $controller = $this->getParam('controller','default');

       $param= compact('controller');
       $param['name'] = $this->_parameters['name'];
       $param['module'] = $this->_parameters['module'];

       // TODO
        die("not implemented.");

    }
}


?>