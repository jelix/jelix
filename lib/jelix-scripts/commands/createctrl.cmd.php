<?php

/**
* @package     jelix-scripts
* @version     $Id$
* @author      Jouanneau Laurent
* @contributor
* @copyright   2005-2006 Jouanneau laurent
* @link        http://www.jelix.org
* @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
*/

class createctrlCommand extends JelixScriptCommand {

    public  $name = 'createctrl';
    public  $allowed_options=array();
    public  $allowed_parameters=array('module'=>true,'name'=>true, 'method'=>false);

    public  $syntaxhelp = "MODULE NOM_CONTROLEUR [NOM_METHOD]";
    public  $help="
    Permet de créer un nouveau fichier d'une classe jController

    MODULE : le nom du module concerné.
    NOM_CONTROLEUR :  nom du controleur que vous voulez créer.
    NOM_METHOD (facultatif) : nom de la première méthode. Par défaut, elle a
                              le nom index.";


    public function run(){
       $path= $this->getModulePath($this->_parameters['module']);

       $agfilename= $path.'controllers/';
       $this->createDir($agfilename);

       $agfilename.=strtolower($this->_parameters['name']).'.classic.php';

       $method = $this->getParam('method','index');

       $param= array('name'=>$this->_parameters['name'] , 'method'=>$method);

       $this->createFile($agfilename,'controller.tpl',$param);

    }
}


?>