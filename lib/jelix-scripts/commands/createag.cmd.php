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

class createagCommand extends JelixScriptCommand {

    public  $name = 'createag';
    public  $allowed_options=array();
    public  $allowed_parameters=array('module'=>true,'name'=>true, 'method'=>false);

    public  $syntaxhelp = "MODULE NOM_ACTIONGROUP [NOM_METHOD]";
    public  $help="
    Permet de créer un nouveau fichier d'une classe actiongroup

    MODULE : le nom du module concerné.
    NOM_ACTIONGROUP :  nom de l'actiongroup que vous voulez créer.
    NOM_METHOD (facultatif) : nom de la première méthode. Par défaut, elle a le nom getDefault.";


    public function run(){
       $path= $this->getModulePath($this->_parameters['module']);

       $agfilename= $path.'actiongroups/';
       $this->createDir($agfilename);

       $agfilename.=strtolower($this->_parameters['name']).'.ag.php';

       $method = $this->getParam('method','getDefault');

       $param= array('name'=>$this->_parameters['name'] , 'method'=>$method);

       $this->createFile($agfilename,'actiongroup.tpl',$param);

    }
}


?>