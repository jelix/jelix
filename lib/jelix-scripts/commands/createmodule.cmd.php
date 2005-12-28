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


class createmoduleCommand extends JelixScriptCommand {

    public  $name = 'createmodule';
    public  $allowed_options=array('-nosubdir'=>false, '-noag'=>false);
    public  $allowed_parameters=array('module'=>true);

    public  $syntaxhelp = "[-nosubdir] [-noag]  MODULE";
    public  $help="
    Créer un nouveau module, avec son fichier module.xml, action.xml
    et un actiongroup par défaut, ainsi que tous les sous-repertoires courants
    (zones, templates, daos, locales, classes...).

    -nosubdir (facultatif) : ne créer pas tous les sous-repertoires courant..
    -noag (facultatif) : ne créer pas le fichier desc et actiongroup par défaut
    MODULE : le nom du module à créer.";


    public function run(){
       $path= $this->getModulePath($this->_parameters['module'], false);

       if(file_exists($path)){
          die("Error: module '".$module."' already exists");
       }
       $this->createDir($path);
       $this->createFile($path.'module.xml','module.xml.tpl',array('name'=>$this->_parameters['module']));
       $this->createFile($path.'actions.xml','actions.xml.tpl',array('module'=>$this->_parameters['module']));

       if(!$this->getOption('-nosubdir')){
          $this->createDir($path.'classes/');
          $this->createDir($path.'zones/');
          $this->createDir($path.'actiongroups/');
          $this->createDir($path.'templates/');
          $this->createDir($path.'classes/');
          $this->createDir($path.'daos/');
          $this->createDir($path.'locales/');
          $this->createDir($path.'locales/en_EN/');
          $this->createDir($path.'locales/fr_FR/');
       }       
       
       if(!$this->getOption('-noag')){
         $agcommand = jxs_load_command('createag');
         $agcommand->init(array(),array('module'=>$this->_parameters['module'], 'name'=>'default','method'=>'getDefault'));
         $agcommand->run();

          /*
         $desccommand = jxs_load_command('createaction');
         $desccommand->init(array(),array('module'=>$this->_parameters['module'], 'name'=>'default','action'=>'default', 'actiongroup'=>'default', 'method'=>'getDefault'));
         $desccommand->run();
         */

       }
    }
}


?>