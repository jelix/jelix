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
    public  $allowed_options=array('-nosubdir'=>false, '-nocontroller'=>false);
    public  $allowed_parameters=array('module'=>true);

    public  $syntaxhelp = "[-nosubdir] [-nocontroller]  MODULE";
    public  $help="
    Créer un nouveau module, avec son fichier module.xml, et un controleur
    par défaut, ainsi que tous les sous-repertoires courants
    (zones, templates, daos, locales, classes...).

    -nosubdir (facultatif) : ne créer pas tous les sous-repertoires courant..
    -nocontroller (facultatif) : ne créer pas de fichier controller par défaut
    MODULE : le nom du module à créer.";


    public function run(){
       $path= $this->getModulePath($this->_parameters['module'], false);

       if(file_exists($path)){
          die("Error: module '".$this->_parameters['module']."' already exists");
       }
       $this->createDir($path);
       $this->createFile($path.'module.xml','module.xml.tpl',array('name'=>$this->_parameters['module']));

       if(!$this->getOption('-nosubdir')){
          $this->createDir($path.'classes/');
          $this->createDir($path.'zones/');
          $this->createDir($path.'controllers/');
          $this->createDir($path.'templates/');
          $this->createDir($path.'classes/');
          $this->createDir($path.'daos/');
          $this->createDir($path.'locales/');
          $this->createDir($path.'locales/en_EN/');
          $this->createDir($path.'locales/fr_FR/');
       }

       if(!$this->getOption('-nocontroller')){
         $agcommand = jxs_load_command('createctrl');
         $agcommand->init(array(),array('module'=>$this->_parameters['module'], 'name'=>'default','method'=>'index'));
         $agcommand->run();
       }
    }
}


?>