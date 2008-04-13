<?php
/**
* @package     jelix
* @subpackage  utils
* @author      Sylvain de Vathaire
* @contributor 
* @copyright   2008 Sylvain de Vathaire
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/


require_once(LIB_PATH.'wshelper/WSDLStruct.class.php');
require_once(LIB_PATH.'wshelper/WSDLException.class.php');
require_once(LIB_PATH.'wshelper/WSException.class.php');
require_once(LIB_PATH.'wshelper/IPXMLSchema.class.php');
require_once(LIB_PATH.'wshelper/IPPhpDoc.class.php');
require_once(LIB_PATH.'wshelper/IPReflectionClass.class.php');
require_once(LIB_PATH.'wshelper/IPReflectionCommentParser.class.php');
require_once(LIB_PATH.'wshelper/IPReflectionMethod.class.php');
require_once(LIB_PATH.'wshelper/IPReflectionProperty.class.php');



/**
 * object to generate WSDL files and web services documentation
 * we have 1 WSDL file for each soap web service, each service is implemented by 1 Jelix controller
 * @package    jelix
 * @subpackage utils
 */
class jWSDL {

    /**
    * module name
    */
    public $module;

    /**
    * controller name
    */
    public $controller;

    /**
    * controller class name
    */
    private $controllerClassName;

    /**
    * WSDL file path (cached file)
    */
    public $WSDLfilePath;


    private $_ctrlpath;

    private $_dirname = 'WSDL';
    private $_cacheSuffix = '.wsdl';


    public function __construct($module, $controller){

        $this->module = $module;
        $this->controller = $controller;

        $this->_createPath();
        $this->_createCachePath();
     }

    /**
     * create the path for the cache file
     */
    private function _createPath(){
        global $gJConfig;

        //Check module availability
        if(!isset($gJConfig->_modulesPathList[$this->module])){
            throw new jExceptionSelector('jelix~errors.module.unknow', $this->module);
        }

        //Build controller path
        $this->_ctrlpath = $gJConfig->_modulesPathList[$this->module].'controllers/'.$this->controller.'.soap.php';

        //Check controller availability
        if(!file_exists($this->_ctrlpath)){
            throw new jException('jelix~errors.action.unknow',$this->controller);
        }

        //Check controller declaration
        require_once($this->_ctrlpath);
        $this->controllerClassName = $this->controller.'Ctrl';
        if(!class_exists($this->controllerClassName,false)){
            throw new jException('jelix~errors.ad.controller.class.unknow', array('jWSDL', $this->controllerClassName, $this->_ctrlpath));
        }

    }

    /**
     * Build the WSDL cache file path
     */
    private function _createCachePath(){
        $this->_cachePath = JELIX_APP_TEMP_PATH.'compiled/'.$this->_dirname.'/'.$this->module.'~'.$this->controller.$this->_cacheSuffix;
    }

    /**
     * Return the WSDL cache file path (WSDL is updated if necessary)
     */
    public function getWSDLFilePath(){
        $this->_updateWSDL();
        return $this->_cachePath;
    }

    /**
     * Return the WSDL file content (WSDL is updated if necessary)
     */
    public function getWSDLFile(){
        $this->_updateWSDL();
        return file_get_contents($this->_cachePath);
    }

    /**
     * Return array of params object for the operation $operationName
     * @param $operationName string Name of the operation (controller method)
     * @return array of params object (empty if no params)
     */
    public function getOperationParams($operationName){

       $IPReflectionMethod = new IPReflectionMethod($this->controllerClassName, $operationName);
       return $IPReflectionMethod->parameters;
    }

    /**
     * Update the WSDL cache file
     */
    private function _updateWSDL(){

        global $gJConfig;
        static $updated = FALSE;

        if($updated){
            return;
        }

        $mustCompile = $gJConfig->compilation['force'] || !file_exists($this->_cachePath);
        if($gJConfig->compilation['checkCacheFiletime'] && !$mustCompile){
            if( filemtime($this->_ctrlpath) > filemtime($this->_cachePath)){
                $mustCompile = true;
            }
        }

        if($mustCompile){
            jFile::write($this->_cachePath, $this->_compile());
        }
        $updated = TRUE;
    }

    /**
     * Generate the WSDL content
     */
    private function _compile(){

        global $gJConfig;

        $serviceURL = "http://".$_SERVER['HTTP_HOST'].$gJConfig->urlengine['basePath'].'soap.php?service='.$this->module.'~'.$this->controller;
        $serviceNameSpace = "http://".$_SERVER['HTTP_HOST'].$gJConfig->urlengine['basePath'];

        $wsdl = new WSDLStruct($serviceNameSpace, $serviceURL, SOAP_RPC, SOAP_ENCODED);
        $wsdl->setService(new IPReflectionClass($this->controllerClassName));

        try {
            $gendoc = $wsdl->generateDocument();
        } catch (WSDLException $exception) {
            throw new JException('jWSDL~errors.wsdl.generation', $exception->msg);
        }

        return $gendoc;
    }

    /**
     * Load the class or service definition for doc purpose
     * @param string $classname Name of the class for witch we want the doc, default doc is the service one (controller)
     */
    public function doc($className=""){

        if($className != ""){
            if(!class_exists($className,false)){
                throw new jException('jelix~errors.ad.controller.class.unknow', array('WSDL generation', $className, $this->_ctrlpath));
            }
            $classObject = new IPReflectionClass($className);
        }else{
            $classObject = new IPReflectionClass($this->controllerClassName);
        }

        $documentation = Array();
        $documentation['menu'] = Array();

        if($classObject){
            $classObject->properties = $classObject->getProperties(false, false, false);
            $classObject->methods = $classObject->getMethods(false, false, false);
            foreach((array)$classObject->methods as $method) {
                $method->params = $method->getParameters();
            }

            $documentation['class'] = $classObject;
            $documentation['service'] = $this->module.'~'.$this->controller;
        }
        return $documentation;
    }

    /**
     * Return an array of all the soap controllers class available in the application
     * @return array Classname of controllers
     */
    public static function getSoapControllers(){

        global $gJConfig;

        $modules = $gJConfig->_modulesPathList;
        $controllers = array();

        foreach($modules as $module){
            if(is_dir($module.'controllers')){
                if ($handle = opendir($module.'controllers')) {
                    $moduleName = basename($module);
                    while (false !== ($file = readdir($handle))) {
                        if (substr($file, strlen($file) - strlen('.soap.php')) == '.soap.php') {
                            $controller = array();
                            $controller['class'] = substr($file, 0, strlen($file) - strlen('.soap.php'));
                            $controller['module'] = $moduleName;
                            $controller['service'] = $moduleName.'~'.$controller['class'];
                            array_push($controllers, $controller);
                        }
                    }
                    closedir($handle);
                }
            }
        }
        return $controllers;
    }
}
