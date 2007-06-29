<?php
/**
* @package     jelix-scripts
* @author      Jouanneau Laurent
* @contributor Loic Mathaud
* @copyright   2005-2007 Jouanneau laurent, 2007 Loic Mathaud
* @link        http://www.jelix.org
* @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
*/


class createmoduleCommand extends JelixScriptCommand {

    public  $name = 'createmodule';
    public  $allowed_options=array('-nosubdir'=>false, '-nocontroller'=>false, '-cmdline'=>false);
    public  $allowed_parameters=array('module'=>true);

    public  $syntaxhelp = "[-nosubdir] [-nocontroller] [-withcmdline] MODULE";
    public  $help=array(
        'fr'=>"
    Créer un nouveau module, avec son fichier module.xml, et un controleur
    par défaut, ainsi que tous les sous-repertoires courants
    (zones, templates, daos, locales, classes...).

    -nosubdir (facultatif) : ne créer pas tous les sous-repertoires courant..
    -nocontroller (facultatif) : ne créer pas de fichier controleur par défaut
    -withcmdline (facultatif) : crée le module avec un controleur pour la ligne de commande
    MODULE : le nom du module à créer.",
        'en'=>"
    Create a new module, with all necessary files and sub-directories.

    -nosubdir (optional): don't create sub-directories.
    -nocontroller (optional): don't create a default controller.
    -withcmdline (optional): create a controller for command line (jControllerCmdLine)
    MODULE: name of the new module."
    );


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
          $this->createDir($path.'forms/');
          $this->createDir($path.'locales/');
          $this->createDir($path.'locales/en_EN/');
          $this->createDir($path.'locales/fr_FR/');
       }

       if(!$this->getOption('-nocontroller')){
         $agcommand = jxs_load_command('createctrl');
         $options = array();
         if ($this->getOption('-withcmdline')) {
            $options = array('-cmdline'=>true);
         }
         $agcommand->init($options,array('module'=>$this->_parameters['module'], 'name'=>'default','method'=>'index'));
         $agcommand->run();
       }
    }
}


?>
