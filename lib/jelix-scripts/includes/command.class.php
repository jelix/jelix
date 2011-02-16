<?php
/**
* @package     jelix-scripts
* @author      Laurent Jouanneau
* @contributor Loic Mathaud
* @copyright   2005-2011 Laurent Jouanneau, 2008 Loic Mathaud
* @link        http://www.jelix.org
* @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
*/

/**
* base class for commands implementation
*/
abstract class JelixScriptCommand {

   /**
    * @var string the name of the command
    */
   public $name;

   /**
    * options available for the command
    * the array contains items like :
    *   key =  name of the option '-foo'
    *   value = boolean: true if the option need a value after it
    * @var array
    */
   public $allowed_options=array();

   /**
    * parameters needed for the command
    * the array contains items like :
    *   key =  name of the variable which will contains the parameter value
    *   value = boolean: false if the parameter is optional
    * Optional parameters should be declared at the end of the array
    * The last parameter declaration could have '...' as name, so it will contains
    * in an array any additional values given in the command line
    * @var array
    */
   public $allowed_parameters = array();

   /**
    * @var array readed options
    */
   protected $_options;
   
   /**
    * @var array readed parameters
    */
   protected $_parameters;

   /**
    * @var array|string  help text for the syntax
    */
   public $syntaxhelp = '';
   
   /**
    * @var array|string detailed help
    */
   public $help = 'No help for this command';

   /**
    * @var boolean indicate if the application must exist to execute the command
    */
   public $applicationMustExist = true;

   /**
    * @var boolean indicate if a name of an application is required
    */
   public $applicationRequired = true;

   function __construct() {}

   /**
    * @param array $opt readed options
    * @param array $parameters readed parameters
    */
   public function init($opt, $parameters) {
     $this->_options = $opt;
     $this->_parameters = $parameters;
   }

   /**
    * main method which execute the process for the command
    */
   abstract public function run();

   /**
    * helper method to retrieve the path of the module
    * @param string $module the name of the module
    * @return string the path of the module
    */
   protected function getModulePath($module) {
      jxs_init_jelix_env();

      global $gJConfig;
      if (!isset($gJConfig->_modulesPathList[$module])) {
        if (isset($gJConfig->_externalModulesPathList[$module]))
            return $gJConfig->_externalModulesPathList[$module];
        throw new Exception("The module $module doesn't exist");
      }
      return $gJConfig->_modulesPathList[$module];
   }

   /**
    * helper method to create a file from a template stored in the templates/
    * directory of jelix-scripts. it set the rights
    * on the file as indicated in the configuration of jelix-scripts
    * 
    * @param string $filename the path of the new file created from the template
    * @param string $template relative path to the templates/ directory, of the
    *               template file
    * @param array  $param template values, which will replace some template variables
    * @return boolean true if it is ok
    */
   protected function createFile($filename, $template, $tplparam=array()) {

      $defaultparams = array (
         'default_website'       => JELIXS_INFO_DEFAULT_WEBSITE,
         'default_license'       => JELIXS_INFO_DEFAULT_LICENSE,
         'default_license_url'   => JELIXS_INFO_DEFAULT_LICENSE_URL,
         'default_creator_name'  => JELIXS_INFO_DEFAULT_CREATOR_NAME,
         'default_creator_email' => JELIXS_INFO_DEFAULT_CREATOR_EMAIL,
         'default_copyright'     => JELIXS_INFO_DEFAULT_COPYRIGHT,
         'createdate'            => date('Y-m-d'),
         'jelix_version'         => file_get_contents(JELIXS_LIB_PATH.'jelix/VERSION'),
         'appname'               => $GLOBALS['APPNAME'],
         'default_timezone'      => JELIXS_INFO_DEFAULT_TIMEZONE,
         'default_locale'        => JELIXS_INFO_DEFAULT_LOCALE,
      );

      $v = explode('.', $defaultparams['jelix_version']);
      if (count($v) < 2)
        $v[1] = '0';

      $defaultparams['jelix_version_next'] = $v[0].'.'.$v[1].'.*';

      $tplparam = array_merge($defaultparams, $tplparam);

      if (file_exists($filename)) {
         echo "Warning: the file '".$filename."' already exists\n";
         return false;
      }
      $tplpath = JELIX_SCRIPT_PATH.'templates/'.$template;

      if (!file_exists($tplpath)) {
         echo "Error: template file '".$tplpath."' doesn't exist\n";
         return false;
      }
      $tpl = file($tplpath);
      $this->tplparam = $tplparam;

      foreach($tpl as $k=>$line){
         $tpl[$k]= preg_replace_callback('|\%\%([a-zA-Z0-9_]+)\%\%|',
                                         array(&$this, 'replaceCallback'),
                                         $line);
      }

      $f = fopen($filename, 'w');
      fwrite($f, implode("", $tpl));
      fclose($f);

      if (DO_CHMOD) {
         chmod($filename, CHMOD_FILE_VALUE);
      }

      if (DO_CHOWN) {
         chown($filename, CHOWN_USER);
         chgrp($filename, CHOWN_GROUP);
      }
      return true;
   }

   /**
    * helper method to create a new directory. it set the rights
    * on the directory as indicated in the configuration of jelix-scripts
    *
    * @param string $dirname the path of the directory
    */
   protected function createDir($dirname) {
      if (!file_exists($dirname)) {
         $this->createDir(dirname($dirname));
         mkdir($dirname);
         if (DO_CHMOD) {
            chmod($dirname, CHMOD_DIR_VALUE);
         }

         if (DO_CHOWN) {
            chown($dirname, CHOWN_USER);
            chgrp($dirname, CHOWN_GROUP);
         }
      }
   }

   /**
    * @internal callback function used by createFile
    */
   protected function replaceCallback($matches){
      if (isset($this->tplparam[$matches[1]])) {
         return $this->tplparam[$matches[1]];
      } else
         return '';
   }

   /**
    * helper function to retrieve a command parameter
    * @param string $param the parameter name
    * @param string $defaultvalue the default value to return if
    *                the parameter does not exist
    * @return string the value
    */
   protected function getParam($param, $defaultvalue=null){
      if (isset($this->_parameters[$param])) {
         return $this->_parameters[$param];
      }
      else{
         return $defaultvalue;
      }
   }

   /**
    * helper function to retrieve a command option
    * @param string $name the option name
    * @return string the value of the option, or false if it doesn't exist
    */
   protected function getOption($name){
      if (isset($this->_options[$name])) {
         return $this->_options[$name];
      }
      else {
         return false;
      }
   }

    protected function removeOption($name) {
        if (isset($this->_options[$name])) {
            unset($this->_options[$name]);
        }
    }

   /**
    * @var DOMDocument the content of the project.xml file, loaded by loadProjectXml
    */
   protected $projectXml = null;

   /**
    * load the content of the project.xml file, and store the corresponding DOM
    * into the $projectXml property
    */
   protected function loadProjectXml() {

      if ($this->projectXml)
         return;

      $doc = new DOMDocument();

      if (!$doc->load(JELIX_APP_PATH.'project.xml')){
         throw new Exception("cannot load project.xml");
      }

      if ($doc->documentElement->namespaceURI != JELIX_NAMESPACE_BASE.'project/1.0'){
         throw new Exception("bad namespace in project.xml");
      }
      $this->projectXml = $doc;
   }


   protected function getEntryPointsList() {
      $this->loadProjectXml();
      $listEps = $this->projectXml->documentElement->getElementsByTagName("entrypoints");
      if (!$listEps->length) {
         return array();
      }
        
      $listEp = $listEps->item(0)->getElementsByTagName("entry");
      if(!$listEp->length) {
         return array();
      }
        
      $list = array();
      for ($i=0; $i < $listEp->length; $i++) {
         $epElt = $listEp->item($i);
         $ep = array(
            'file'=>$epElt->getAttribute("file"),
            'config'=>$epElt->getAttribute("config"),
            'isCli'=> ($epElt->getAttribute("type") == 'cmdline'),
            'type'=>$epElt->getAttribute("type"),
         );
         if (($p = strpos($ep['file'], '.php')) !== false)
            $ep['id'] = substr($ep['file'],0,$p);
         else
            $ep['id'] = $ep['file'];

         $list[] = $ep;
      }
      return $list;
   }
   
   protected function getEntryPointInfo($name) {
      $this->loadProjectXml();
      $listEps = $this->projectXml->documentElement->getElementsByTagName("entrypoints");
      if (!$listEps->length) {
         return null;
      }
        
      $listEp = $listEps->item(0)->getElementsByTagName("entry");
      if(!$listEp->length) {
         return null;
      }

      for ($i=0; $i < $listEp->length; $i++) {
         $epElt = $listEp->item($i);
         $ep = array(
            'file'=>$epElt->getAttribute("file"),
            'config'=>$epElt->getAttribute("config"),
            'isCli'=> ($epElt->getAttribute("type") == 'cmdline'),
            'type'=>$epElt->getAttribute("type"),
         );
         if (($p = strpos($ep['file'], '.php')) !== false)
            $ep['id'] = substr($ep['file'],0,$p);
         else
            $ep['id'] = $ep['file'];
         if ($ep['id'] == $name)
            return $ep;
      }
      return null;
   }

    protected function getSupportedJelixVersion() {
        $this->loadProjectXml();

        $deps = $this->projectXml->getElementsByTagName('dependencies');
        $minversion = '';
        $maxversion = '';
        if($deps && $deps->length > 0) {
            $jelix = $deps->item(0)->getElementsByTagName('jelix');
            if ($jelix && $jelix->length > 0) {
                $minversion = $jelix->item(0)->getAttribute('minversion');
                $maxversion = $jelix->item(0)->getAttribute('maxversion');
            }
        }
        return array($minversion, $maxversion);
    }
 
   
}

