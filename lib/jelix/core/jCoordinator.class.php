<?php
/**
* @package      jelix
* @subpackage   core
* @author       Laurent Jouanneau
* @contributor  Thibault PIRONT < nuKs >, Julien Issler
* @copyright    2005-2008 laurent Jouanneau
* @copyright    2007 Thibault PIRONT
* @copyright    2008 Julien Issler
* @link         http://www.jelix.org
* @licence      GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
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
     * List of all log messages
     * @var array
     * @since 1.0
     */
    public $logMessages=array();

    /**
     * @param  string $configFile name of the ini file to configure the framework
     */
    function __construct ($configFile) {
        global $gJCoord, $gJConfig;

        $gJCoord =  $this;

        // load configuration data
        $gJConfig = jConfig::load($configFile);

        // set Error and exception handler
        // ne devrait être désactivé que lors de certains tests de jelix
        if($gJConfig->use_error_handler){
            set_error_handler('jErrorHandler');
            set_exception_handler('JExceptionHandler');
        }
#if PHP50
        if(function_exists('date_default_timezone_set')){
            date_default_timezone_set($gJConfig->timeZone);
        }
#else
        date_default_timezone_set($gJConfig->timeZone);
#endif
        $this->_loadPlugins();
    }

    /**
     * load the plugins and their configuration file
     */
    private function _loadPlugins(){
        global $gJConfig;

        foreach($gJConfig->coordplugins as $name=>$conf){
            if($conf && isset($gJConfig->_pluginsPathList_coord[$name])){
                if($conf=='1'){
                    $conf = array();
                }else{
                   $conff = $conf;
                   if(!file_exists(JELIX_APP_CONFIG_PATH.$conff))
                        die("Jelix Error: Error in the main configuration. Configuration file '$conff' for coord plugin $name doesn't exist!");
                   if( false === ($conf = parse_ini_file(JELIX_APP_CONFIG_PATH.$conff,true)))
                        die("Jelix Error: Error in the configuration file of plugin $name ($conff)!");
                }
                include( $gJConfig->_pluginsPathList_coord[$name].$name.'.coord.php');
                $class= $name.'CoordPlugin';
                $this->plugins[strtolower($name)] = new $class($conf);
            }
        }
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
        jSession::start();

        $this->moduleName = $this->request->getParam('module');
        $this->actionName = $this->request->getParam('action');

        if(empty($this->moduleName)){
            $this->moduleName = $gJConfig->startModule;
        }
        if(empty($this->actionName)){
            if($this->moduleName == $gJConfig->startModule)
                $this->actionName = $gJConfig->startAction;
            else {
                $this->actionName = 'default:index';
            }
        }

        // verification du module
        if(!in_array($this->moduleName,$gJConfig->_trustedModules)){
            throw new jException('jelix~errors.module.untrusted',$this->moduleName);
        }

        jContext::push ($this->moduleName);
        try{
            $this->action = new jSelectorActFast($this->request->type, $this->moduleName, $this->actionName);
            $ctrl = $this->getController($this->action);
        }catch(jException $e){
            if ($gJConfig->urlengine['notfoundAct'] =='') {
                throw $e;
            }
            try {
                $this->action = new jSelectorAct($gJConfig->urlengine['notfoundAct']);
                $ctrl = $this->getController($this->action);
            }catch(jException $e2){
                throw $e;
            }
        }

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
        jSession::end();
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
        require_once ($gJConfig->responses[$type.'.path']);

        $this->response = new $respclass();

        return false;
    }

    /**
     * Handle an error event. Called by error handler and exception handler.
     * Responses object should take care of the errorMessages property to display errors.
     * @param string  $toDo    a string which contains keyword indicating what to do with the error
     * @param string  $type    error type : 'error', 'warning', 'notice'
     * @param integer $code    error code
     * @param string  $message error message
     * @param string  $file    the file name where the error appear
     * @param integer $line    the line number where the error appear
     * @param array   $trace   the stack trace
     * @since 1.1
     */
    public function handleError($toDo, $type, $code, $message, $file, $line, $trace){
        global $gJConfig;
        $conf = $gJConfig->error_handling;

        $doEchoByResponse = true;

        if($this->request == null){
            $message = 'JELIX PANIC ! Error during initialization !! '.$message;
            $doEchoByResponse = false;
            $toDo.= ' EXIT';

        }elseif($this->response == null){
            $ret = $this->initDefaultResponseOfRequest();
            if(is_string($ret)){
                $message = 'Double error ! 1)'. $ret.'; 2)'.$message;
                $doEchoByResponse = false;
            }
        }

        // When we are in cmdline we need to fix the remoteAddr
        $remoteAddr = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1';

        // formatage du message
        $messageLog = strtr($conf['messageLogFormat'], array(
            '%date%' => date("Y-m-d H:i:s"),
            '%ip%'   => $remoteAddr,
            '%typeerror%'=>$type,
            '%code%' => $code,
            '%msg%'  => $message,
            '%file%' => $file,
            '%line%' => $line,
            '\t' =>"\t",
            '\n' => "\n"
        ));

        if(strpos($toDo , 'TRACE') !== false){
            $arr = debug_backtrace();
            $messageLog.="\ttrace:";
            foreach($arr as $k=>$t){
                $messageLog.="\n\t$k\t".(isset($t['class'])?$t['class'].$t['type']:'').$t['function']."()\t";
                $messageLog.=(isset($t['file'])?$t['file']:'[php]').' : '.(isset($t['line'])?$t['line']:'');
            }
            $messageLog.="\n";
        }


        $echoAsked = false;
        // traitement du message
        if(strpos($toDo , 'ECHOQUIET') !== false){
            $echoAsked = true;
            if(!$doEchoByResponse){
                header("HTTP/1.1 500 Internal jelix error");
                header('Content-type: text/plain');
                echo 'JELIX PANIC ! Error during initialization !! ';
            }elseif($this->addErrorMsg($type, $code, $conf['quietMessage'], '', '')){
                $toDo.=' EXIT';
            }
        }elseif(strpos($toDo , 'ECHO') !== false){
            $echoAsked = true;
            if(!$doEchoByResponse){
                header("HTTP/1.1 500 Internal jelix error");
                header('Content-type: text/plain');
                echo $messageLog;
            }elseif($this->addErrorMsg($type, $code, $message, $file, $line)){
                $toDo.=' EXIT';
            }
        }

        if(strpos($toDo , 'LOGFILE') !== false){
            @error_log($messageLog,3, JELIX_APP_LOG_PATH.$conf['logFile']);
        }
        if(strpos($toDo , 'MAIL') !== false){
            error_log(wordwrap($messageLog,70),1, $conf['email'], $conf['emailHeaders']);
        }
        if(strpos($toDo , 'SYSLOG') !== false){
            error_log($messageLog,0);
        }

        if(strpos($toDo , 'EXIT') !== false){
            if($doEchoByResponse) {
                if ($this->response)
                    $this->response->outputErrors();
                else if($echoAsked) {
                    header("HTTP/1.1 500 Internal jelix error");
                    header('Content-type: text/plain');
                    foreach($this->errorMessages as $msg)
                        echo $msg."\n";
                }
            }
            jSession::end();
            exit;
        }
    }

    /**
     * Store an error/warning/notice message.
     * @param  string $type  error type : 'error', 'warning', 'notice'
     * @param  integer $code  error code
     * @param  string $message error message
     * @param  string $file    the file name where the error appear
     * @param  integer $line  the line number where the error appear
     * @return boolean    true= the process should stop now, false = the error manager do its job
     */
    protected function addErrorMsg($type, $code, $message, $file, $line){
        $this->errorMessages[] = array($type, $code, $message, $file, $line);
        return !$this->response->acceptSeveralErrors();
    }

    /**
     * Store a log message. Responses object should take care
     * of the logMessages properties to display them.
     * @param  string $message error message
     * @since 1.0
     */
    public function addLogMsg($message, $type='default'){
        $this->logMessages[$type][] = $message;
    }

    /**
    * gets a given plugin if registered
    * @param string   $pluginName   the name of the plugin
    * @param boolean  $required  says if the plugin is required or not. If true, will generate an exception if the plugin is not registered.
    * @return jICoordPlugin
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
