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

/**
* classe representant une commande
*/

abstract class JelixScriptCommand {
   public $name;
   public $allowed_options=array();
   public $allowed_parameters=array();

   protected $_options;
   protected $_parameters;

   public $syntaxhelp = '';
   public $help = 'No help for this command';
   
   function __construct(){}

   public function init($opt, $parameters){
     $this->_options = $opt;
     $this->_parameters = $parameters;
   }

   abstract public function run();


   protected function getModulePath($module, $shouldexist=true){
      $path=JELIX_APP_PATH.'modules/'.strtolower($module).'/';
      if(!file_exists($path) && $shouldexist){
         die("Error: module '".$module."' don't exist ($path)\n");
      }
      return $path;
   }

   protected function createFile($filename, $template, $tplparam=array()){
      if(file_exists($filename)){
         echo "Warning: the file '".$filename."' already exists\n";
         return false;
      }
      $tplpath = JELIX_SCRIPT_PATH.'templates/'.$template;

      if(!file_exists($tplpath)){
          echo "Error: template file '".$tplpath."' doesn't exists\n";
         return false;
      }
      $tpl = file($tplpath);
      $this->tplparam = $tplparam;

      foreach($tpl as $k=>$line){
         $tpl[$k]= preg_replace_callback('|\{\$(\w+)\}|',array(&$this,'replaceCallback'),$line);
      }

      $f = fopen($filename,'w');
      fwrite($f,implode("",$tpl));
      fclose($f);

      if(DO_CHMOD){
         chmod($filename, CHMOD_FILE_VALUE);
      }

      if(DO_CHOWN){
         chown($filename, CHOWN_USER);
         chgrp($filename, CHOWN_GROUP);
      }
      return true;
   }

   protected function createDir($dirname){
      if(!file_exists($dirname)){
         mkdir($dirname);
         if(DO_CHMOD){
            chmod($dirname, CHMOD_DIR_VALUE);
         }

         if(DO_CHOWN){
            chown($dirname, CHOWN_USER);
            chgrp($dirname, CHOWN_GROUP);
         }
      }
   }


   protected function replaceCallback($matches){
      if(isset($this->tplparam[$matches[1]])){
         return $this->tplparam[$matches[1]];
      }else
         return '';
   }

   protected function getParam($param, $defaultvalue=null){
      if(isset($this->_parameters[$param])){
          return $this->_parameters[$param];
       }else{
          return $defaultvalue;
       }
   }

   protected function getOption($name){
      if(isset($this->_options[$name])){
          return $this->_options[$name];
       }else{
          return false;
       }
   }
}
?>