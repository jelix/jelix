<?php
/**
* @package    jelix
* @subpackage core
* @author     Laurent Jouanneau
* @contributor
* @copyright  2005-2006 laurent Jouanneau
* @link       http://www.jelix.org
* @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

/**
 * the main class of the jelix core
 *
 * this is the "chief orchestra" of the framework. Its goal is
 * to load the configuration, to get the request parameters
 * used to instancie the correspondant controllers and to run the right method.
 * @package  jelix
 * @subpackage core
 */
class jCoordinator {

   /**
    * plugin list
    * @var  array
    */
    public $plugins=array();

    /**
     * current response object
     * @var jResponse
     */
    public $response = null;

    /**
     * current request object
     * @var jRequest
     */
    public $request = null;

    /**
     * the selector of the current action
     * @var jSelectorAct
     */
    public $action = null;

    /**
     * the current module name
     * @var string
     */
    public $moduleName;

    /**
     * the current action name
     * @var string
     */
    public $actionName;

    /**
     * List of all errors
     * @var array
     */
    public $errorMessages=array();

    /**
     * @param  string $configFile name of the ini file to configure the framework
     */
    function __construct ($configFile) {
        global $gJCoord, $gJConfig;

        $gJCoord =  $this;

        if(JELIX_APP_TEMP_PATH=='/'){ // le realpath dans application.ini.php a renvoyé false...
            die('Jelix Error: Application temp directory doesn\'t exist !');
        }
        if(!is_writable(JELIX_APP_TEMP_PATH)){
            die('Jelix Error: Application temp directory is not writable');
        }
        // load configuration data
        $gJConfig = jConfig::load($configFile);

        //make sure that the session cookie is only for the current application
        if(!$gJConfig->shared_session)
            session_set_cookie_params ( 0 , $gJConfig->urlengine['basePath']);

        // set Error and exception handler
        // ne devrait être désactivé que lors de certains tests de jelix
        if($gJConfig->use_error_handler){
            set_error_handler('jErrorHandler');
            set_exception_handler('JExceptionHandler');
        }
#if PHP50
        if(function_exists('date_default_timezone_set')){
            date_default_timezone_set($gJConfig->defaultTimeZone);
        }
#else
        date_default_timezone_set($gJConfig->defaultTimeZone);
#endif
        $this->_loadPlugins();
    }

    /**
     * load the plugins and their configuration file
     */
    private function _loadPlugins(){
        global $gJConfig;

        foreach($gJConfig->plugins as $name=>$conf){
            if($conf && isset($gJConfig->_pluginsPathList[$name])){
                $conff= $conf=='1' ? $name.'.plugin.ini.php': $conf;
                if(file_exists(JELIX_APP_CONFIG_PATH.$conff)){
                   if( false === ($conf = @parse_ini_file(JELIX_APP_CONFIG_PATH.$conff,true)))
                        die("Jelix Error: Error in the configuration file of plugin $name ($conff)!");
                }else{
                    $conf = array();
                }
                include( $gJConfig->_pluginsPathList[$name].$name.'.plugin.php');
                $class= $name.'Plugin';
                $this->plugins[strtolower($name)] = new $class($conf);
            }
        }
    }

    /**
     * Store an error/warning/notice message. Responses object should take care
     * of the errorMessages properties to display them.
     * @param  string $type  error type : 'error', 'warning', 'notice'
     * @param  integer $code  error code
     * @param  string $message error message
     * @param  string $file    the file name where the error appear
     * @param  integer $line  the line number where the error appear
     * @return boolean    true= the process should stop now, false = the error manager do its job
     */
    public function addErrorMsg($type, $code, $message, $file, $line){
        $this->errorMessages[] = array($type, $code, $message, $file, $line);
        if(!$this->response){
            return $this->initDefaultResponseOfRequest();
        }
        return !$this->response->acceptSeveralErrors();
    }

    /**
    * main method : launch the execution of the action.
    *
    * This method should be called in a entry point.
    * @param  jRequest  $request the request object
    */
    public function process ($request){
        global $gJConfig;

        $this->request = $request;
        $this->request->init();
        session_start();

        $this->moduleName = $this->request->getParam('module');
        $this->actionName = $this->request->getParam('action');

        if(empty($this->moduleName)){
            $this->moduleName = $gJConfig->defaultModule;
            if(empty($this->actionName))
               $this->actionName = $gJConfig->defaultAction;
        }
        if(empty($this->actionName)){
            $this->actionName = 'default_index';
        }


        // verification du module
        if(!in_array($this->moduleName,$gJConfig->_trustedModules)){
            throw new jException('jelix~errors.module.untrusted',$this->moduleName);
        }

        jContext::push ($this->moduleName);
        try{
            $this->action = new jSelectorAct($this->actionName);
        }catch(jExceptionSelector $e){
            if($e->getCode() == 12){
                throw new jException('jelix~errors.module.unknow',$this->moduleName);
            }elseif($e->getCode() == 10){
                throw new jException('jelix~errors.action.unknow',$this->actionName);
            }
        }

        $ctrl = $this->getController($this->action);

        $pluginparams = array();
        if(isset($ctrl->pluginParams['*'])){
            $pluginparams = $ctrl->pluginParams['*'];
        }

        if(isset($ctrl->pluginParams[$this->action->method])){
            $pluginparams = array_merge($pluginparams, $ctrl->pluginParams[$this->action->method]);
        }

        foreach ($this->plugins as $name => $obj){
            $result = $this->plugins[$name]->beforeAction ($pluginparams);
            if($result){
               $this->action = $result;
               jContext::pop();
               jContext::push($result->module);
               $this->moduleName = $result->module;
               $this->actionName = $result->resource;
               $ctrl = $this->getController($this->action);
               break;
            }
        }

        $this->response = $ctrl->{$this->action->method}();

        if($this->response == null){
            throw new jException('jelix~errors.response.missing',$this->action->toString());
        }

        foreach ($this->plugins as $name => $obj){
           $this->plugins[$name]->beforeOutput ();
        }

        // envoi de la réponse
        if(!$this->response->output()){
           $this->response->outputErrors();
        }

        foreach ($this->plugins as $name => $obj){
            $this->plugins[$name]->afterProcess ();
        }

        jContext::pop();
    }

    /**
     * get the controller corresponding to the selector
     * @param jSelectorAct $selector
     */
    private function getController($selector){

        $ctrlpath = $selector->getPath();

        if(!file_exists($ctrlpath)){
            throw new jException('jelix~errors.ad.controller.file.unknow',array($this->actionName,$ctrlpath));
        }
        require_once($ctrlpath);
        $class = $selector->getClass();
        if(!class_exists($class,false)){
            throw new jException('jelix~errors.ad.controller.class.unknow',array($this->actionName,$class, $ctrlpath));
        }

        $ctrl = new $class($this->request);
        if($ctrl instanceof jIRestController){
            $method = $selector->method = strtolower($_SERVER['REQUEST_METHOD']);
        }elseif(!method_exists($ctrl,$selector->method)){
            throw new jException('jelix~errors.ad.controller.method.unknow',array($this->actionName,$selector->method, $class, $ctrlpath));
        }
        return $ctrl;
    }


    /**
     * instancy a response object corresponding to the default response type
     * of the current resquest
     * @return mixed  error string or false
     */
    public function initDefaultResponseOfRequest(){
        global $gJConfig;

        $type= $this->request->defaultResponseType;

        if(!isset($gJConfig->responses[$type])){
            return jLocale::get('jelix~errors.default.response.type.unknow',array($this->moduleName.'~'.$this->actionName,$type));
        }

        $respclass = $gJConfig->responses[$type];
        if(file_exists($path=JELIX_LIB_RESPONSE_PATH.$respclass.'.class.php')){
           require_once ($path);
        }elseif(file_exists($path=JELIX_APP_PATH.'responses/'.$respclass.'.class.php')){
           require_once ($path);
        }else{
           return jLocale::get('jelix~errors.default.response.not.loaded',array($this->moduleName.'~'.$this->actionName,$type));
        }

        $this->response = new $respclass();

        return false;
    }

    /**
    * gets a given plugin if registered
    * @param string   $pluginName   the name of the plugin
    * @param boolean  $required  says if the plugin is required or not. If true, will generate an exception if the plugin is not registered.
    * @return jIPlugin
    */
    public function getPlugin ($pluginName, $required = true){
        $pluginName = strtolower ($pluginName);
        if (isset ($this->plugins[$pluginName])){
            $plugin = $this->plugins[$pluginName];
        }else{
            if ($required){
                throw new jException('jelix~errors.plugin.unregister', $pluginName);
            }
            $plugin = null;
        }
        return $plugin;
    }

    /**
    * Says if the given plugin $name is enabled
    * @param string $pluginName
    * @return boolean true : plugin is ok
    */
    public function isPluginEnabled ($pluginName){
        return isset ($this->plugins[strtolower ($pluginName)]);
    }

    /**
    * Says if the given module $name is enabled
    * @param string $moduleName
    * @return boolean true : module is ok
    */
    public function isModuleEnabled ($moduleName){
        return in_array($moduleName, $GLOBALS['gJConfig']->_trustedModules);
    }

    /**
     * return the real path of a module
     * @param string $module a module name
     * @return string the corresponding path
     */

    public function getModulePath($module){
        global $gJConfig;
        if(!isset($gJConfig->_modulesPathList[$module])){
            throw new Exception('getModulePath : invalid module name');
        }
        return $gJConfig->_modulesPathList[$module];
    }
}
?>
