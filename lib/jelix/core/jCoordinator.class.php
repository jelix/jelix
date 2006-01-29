<?php
/**
* @package    jelix
* @subpackage core
* @version    $Id$
* @author     Jouanneau Laurent
* @contributor
* @copyright  2005-2006 Jouanneau laurent
* @link        http://www.jelix.org
* @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/


class jCoordinator {

   /**
    * liste des plugins utilisés
    * @var  array
    */
    public $plugins=array();

    /**
     * Reponse courante
     * @var jResponse
     */
    public $response = null;

    /**
     * @var jRequest
     */
    public $request = null;

    /**
     * @var jActionDesc
     */
    public $action = null;
    /**
     * @var string
     */
    private $configFile;

    /**
     * @var string
     */
    public $moduleName;
    /**
     * @var string
     */
    public $actionName;

    /**
     * @param  string $configFile name of the ini file to configure the framework
     */
    function __construct ($configFile) {
        global $gJCoord, $gJConfig;

        $gJCoord = $this;

        if(!is_writable(JELIX_APP_TEMP_PATH)){
            trigger_error('Temp directory is not writable',E_USER_ERROR);
        }
        // load configuration data
        $this->configFile = $configFile;
        $gJConfig = jConfig::load($configFile);


        // set Error and exception handler
        // ne devrait être désactivé que lors de certains tests de jelix
        if($gJConfig->use_error_handler){
            set_error_handler('jErrorHandler');
            set_exception_handler('JExceptionHandler');
        }

        $this->_loadPlugins();
    }

    /**
     * instanciation des plugins
     */
    private function _loadPlugins(){

        foreach($GLOBALS['gJConfig']->plugins as $name=>$conf){
            if($conf && isset($GLOBALS['gJConfig']->pluginsPathList[$name])){
                if($conf=='1')
                    $conf=$name.'.plugin.ini.php';
                if(file_exists(JELIX_APP_CONFIG_PATH.$conf)){
                   $conf = parse_ini_file(JELIX_APP_CONFIG_PATH.$conf);
                }else{
                    $conf = array();
                }
                include( $GLOBALS['gJConfig']->pluginsPathList[$name]);
                $class= $name.'Plugin';
                $this->plugins[strtolower($name)] = new $class($conf);
            }
        }
    }


    /**
    * Fonction principale du coordinateur à appeler dans le index.php pour démarrer
    * le traitement de l'action
    * @param  jRequest  $request the request data
    */
    public function process ($request){
        global $gJConfig;

        $this->request = $request;
        session_start();

        $this->moduleName = $this->request->getParam('module', $gJConfig->defaultModule,true);
        $this->actionName = $this->request->getParam('action', $gJConfig->defaultAction,true);

        // verification du module
        if(!in_array($this->moduleName,$gJConfig->trustedModules)){
            trigger_error(jLocale::get('jelix~errors.module.untrusted',$this->moduleName), E_USER_ERROR);
            return;
        }

        jContext::push ($this->moduleName);

        $selector = new jSelectorAct($this->actionName);

        $result = jIncluder::inc($selector);
        if($result['compileok'] == false || $this->action===null){
           trigger_error(jLocale::get('jelix~errors.action.unknow',$this->actionName), E_USER_ERROR);
           return;
        }


        foreach ($this->plugins as $name => $obj){
            $this->plugins[$name]->beforeProcess ($this->action);
        }

        //try{
            $this->response = $this->action->perform();
        /*}catch(jException $e){
            trigger_error($e->getLocaleMessage(), E_USER_ERROR);
            return ;
        }catch(Exception $e){
            trigger_error($e->getMessage(),E_USER_ERROR);
            return;
        }*/

        if($this->response == null){
            trigger_error(jLocale::get('jelix~errors.response.missing',$selector->toString()), E_USER_ERROR);
            return;
        }

        foreach ($this->plugins as $name => $obj){
           $this->plugins[$name]->beforeOutput ();
        }

        // envoie de la réponse
        if(!$this->response->output()){
           $this->response->outputErrors();
        }

        foreach ($this->plugins as $name => $obj){
            $this->plugins[$name]->afterProcess ();
        }

        jContext::pop();
    }


    /**
     *
     * @param string $name
     */
    public function initDefaultResponseOfRequest(){
        global $gJConfig;

        $type= $this->request->defaultResponseType;

        if(!isset($gJConfig->responses[$type])){
            trigger_error(jLocale::get('jelix~errors.default.response.type.unknow',array($this->moduleName.'~'.$this->actionName,$type)),E_USER_ERROR);
            return null;
        }

        $respclass = $gJConfig->responses[$type];
        if(file_exists($path=JELIX_LIB_RESPONSE_PATH.$respclass.'.class.php')){
           require_once ($path);
        }elseif(file_exists($path=JELIX_APP_PATH.'responses/'.$respclass.'.class.php')){
           require_once ($path);
        }else{
           trigger_error(jLocale::get('jelix~errors.default.response.not.loaded',array($this->moduleName.'~'.$this->actionName,$type)),E_USER_ERROR);
           return null;
        }

        $this->response = new $respclass();
    }


    /**
    * permet à un traitement exterieur (page, zone) de recuperer un element de configuration d'un plugin
    * @param string   $plugin_name   nom du plugin
    * @param string   $plugin_parameter_name   nom de la propriete de l'objet de configuration du plugin
    */
    /*function getPluginConf ($pluginName , $plugin_parameter_name){
        $pluginName = strtolower ($pluginName);
        if (isset ($this->plugins[$pluginName])&& isset($this->plugins[$pluginName]->config->$plugin_parameter_name) ) {
               return $this->plugins[$pluginName]->config->$plugin_parameter_name;
        }
        return null;
    }*/

    /**
    * gets a given plugin if registered
    * @param string   $plugin_name   nom du plugin
    * @param boolean  $required  if the plugin is required or not. If true, will trigger a fatal_error if the plugin is not registered.
    */
    function getPlugin ($pluginName, $required = true){
        $pluginName = strtolower ($pluginName);
        if (isset ($this->plugins[$pluginName])){
            $plugin = $this->plugins[$pluginName];
        }else{
            if ($required){
                trigger_error (jLocale::get ('jelix~errors.plugin.unregister', $pluginName), E_USER_ERROR);
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
    * @return boolean true : plugin is ok
    */
    public function isModuleEnabled ($moduleName){
        return in_array($moduleName, $GLOBALS['gJConfig']->trustedModules);
    }

}
?>
