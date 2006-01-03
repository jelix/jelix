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
     * @var array
     */
    private $pluginsPathList;
    /**
     * @var array
     */
    private $modulesPathList;
    /**
     * @var string
     */
    private $moduleName;
    /**
     * @var string
     */
    private $actionName;

    /**
     * @param  string $configFile name of the ini file to configure the framework
     */
    function __construct ($configFile) {
        global $gJCoord, $gJConfig;

        $gJCoord = $this;

        // load configuration data
        $this->configFile = $configFile;
        $this->_initConfig();

        // set Error and exception handler
        set_error_handler('jErrorHandler');
        set_exception_handler('JExceptionHandler');

        $this->_loadPlugins();
        session_start ();
    }

    /**
     * lecture de la configuration du framework
     */
    protected function _initConfig(){
        global $gJConfig,$gDefaultConfig;

        $gJConfig = parse_ini_file(JELIX_APP_CONFIG_PATH.$this->configFile,true);

        // traitement spécial pour la liste des réponses.
        if(isset($gJConfig['responses'])){
           $resplist = array_merge($gDefaultConfig['responses'],$gJConfig['responses']) ;
        }else{
           $resplist = $gDefaultConfig['responses'];
        }

        $gJConfig = array_merge($gDefaultConfig,$gJConfig);
        $gJConfig['responses'] = $resplist;
        $gJConfig = (object) $gJConfig;

        if(preg_match("/^(\w+).*$/", PHP_OS, $m)){
            $os=$m[1];
        }else{
            $os=PHP_OS;
        }
        $gJConfig->OS = $os;
        $gJConfig->isWindows = (strtolower($os) == 'win');
        if(trim( $gJConfig->defaultAction) == '')
             $gJConfig->defaultAction = 'default';

        $this->pluginPathList = $this->_loadPathList('plugins');
        $this->modulePathList = $this->_loadPathList('modules');
        $this->tplpluginPathList = $this->_loadPathList('tplplugins',true);

        if($gJConfig->checkTrustedModules){
            $gJConfig->trustedModules = explode(',',$gJConfig->trustedModules);
        }else{
            $gJConfig->trustedModules = array_keys($this->modulePathList);
        }
        $gJConfig->urlengine_specific_entrypoints = array_flip($gJConfig->urlengine_specific_entrypoints);

    }

    /**
     * compilation et mise en cache de liste de chemins
     */
    private function _loadPathList($dir, $tplp=false){
        global $gJConfig;

        $file = JELIX_APP_TEMP_PATH.$dir.'list.ini.php';
        $compil=false;
        if(!file_exists($file)){
            $compil=true;
        }else if($gJConfig->compilation['check_cache_filetime']){
            $t = filemtime($file);
            if(filemtime(JELIX_APP_CONFIG_PATH.$this->configFile)>$t){
                $compil=true;
            }else{

                $list = split(' *, *',$gJConfig->{$dir.'Path'});
                foreach($list as $p){
                    $path = str_replace(array('lib:','app:'), array(LIB_PATH,JELIX_APP_PATH), $p);
                    if(!file_exists($path)){
                        trigger_error($p.' path doesn\'t exist',E_USER_ERROR);
                        exit;
                    }
                    if(filemtime($path)>$t){
                        $compil=true;
                        break;
                    }
                }
            }
        }
        if($compil){
            $list = split(' *, *',$gJConfig->{$dir.'Path'});
            $result='';
            foreach($list as $path){
                $path = str_replace(array('lib:','app:'), array(LIB_PATH,JELIX_APP_PATH), $path);
                if ($handle = opendir($path)) {
                     while (false !== ($f = readdir($handle))) {
                        if ($f{0} != '.' && is_dir($path.$f)) {
                           if($tplp){
                              $result[$f][] = $path.$f.'/';
                           }else{
                              $result.=$f.'='.$path.$f."/\n";
                           }
                        }
                     }
                    closedir($handle);
                }
            }
            $f = new jFile ();
            if($tplp){
               $f->write ($file, serialize($result));
            }else{
               $f->write ($file, $result);
            }
        }
        if($tplp){
           return unserialize(file_get_contents($file));
        }else{
           return parse_ini_file($file);
        }
    }

    /**
     * instanciation des plugins
     */
    private function _loadPlugins(){

        foreach($GLOBALS['gJConfig']->plugins as $name=>$conf){
            if($conf && isset($this->pluginPathList[$name])){
                if($conf=='1')
                    $conf=$name.'.plugin.ini.php';
                if(file_exists(JELIX_APP_CONFIG_PATH.$conf)){
                   $conf = parse_ini_file(JELIX_APP_CONFIG_PATH.$conf);
                }else{
                    $conf = array();
                }
                include( $this->pluginPathList[$name]);
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
            trigger_error(jLocale::get('jelix~errors.response.missing',$result->toString()), E_USER_ERROR);
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
            trigger_error(jLocale::get('jelix~errors.default.response.type.unknow',array($type)),E_USER_ERROR);
            return null;
        }

        $respclass = $gJConfig->responses[$type];
        if(file_exists($path=JELIX_LIB_RESPONSE_PATH.$respclass.'.class.php')){
           require_once ($path);
        }elseif(file_exists($path=JELIX_APP_PATH.'responses/'.$respclass.'.class.php')){
           require_once ($path);
        }else{
           trigger_error(jLocale::get('jelix~errors.default.reponse.not.loaded',array($type)),E_USER_ERROR);
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
    * @param boolean  $required 	if the plugin is required or not. If true, will trigger a fatal_error if the plugin is not registered.
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


    /**
    * Creation d'un objet zone et appel de sa méthode processZone.
    * Utilise en interne _callZone.
    * @param string $name le nom de la zone à instancier.
    * @param array   $params un tableau a passer a la fonction processZone de l'objet zone.
    * @see CopixCoordination::_callZone
    */
    /*function processZone ($name, $params=array ()){
        return $this->_callZone($name, 'processZone', $params);
    }*/

    /**
    * Creation d'un objet zone et appel de sa méthode clearZone.
    * @param string $name le nom de la zone à instancier.
    * @param array   $params un tableau a passer a la fonction clearZone de l'objet zone.
    */
    /*function clearZone ($name, $params=array ()){
        return $this->_callZone($name, 'clearZone', $params);
    }*/

    /**
    * Creation d'un objet zone et appel de sa méthode processZone.
    * @param string $name le nom de la zone à instancier.
    * @param array   $params un tableau a passer a la fonction processZone de l'objet zone.
    */
    /*function & _callZone($name,$method, &$params){
        //Récupération des éléments critiques.
        $fileInfo = & new CopixModuleFileSelector ($name);
        jContext::push ($fileInfo->module);

        //Récupère le nom du fichier en fonction du module courant.
        $fileName = $fileInfo->getPath(COPIX_ZONES_DIR). strtolower($fileInfo->fileName) . '.zone.php';

        if (!is_readable ($fileName)){
            trigger_error (CopixI18N::get('copix:copix.error.load.zone',$fileInfo->fileName), E_USER_ERROR);
        }

        //inclusion du fichier.
        require_once($fileName);
        $objName = 'Zone'.$fileInfo->fileName;
        $objTraitement = & new $objName ();
        $toReturn = $objTraitement->$method ($params);

        jContext::pop ();
        return $toReturn;
    }*/

    /**
    * Include a static file.
    *
    * we're gonna parse the file for a | (pipe), if founded, we're gonna
    *   include the static file from the module path.
    *  Else, we'll include the file considering the project path
    * @param    string $idOfFile le nom formaté du fichier
    */
    /*function includeStatic ($idOfFile){
        //Récupération des éléments critiques.
        $fileInfo = new CopixModuleFileSelector($idOfFile);
        //makes the fileName.
        $fileName = $fileInfo->getPath(COPIX_STATIC_DIR). $fileInfo->fileName;
        //test & go.
        if (is_readable ($fileName)){
            ob_start ();
            readfile ($fileName);
            $toShow = ob_get_contents();
            ob_end_clean();
            return $toShow;
        }else{
            trigger_error (CopixI18N::get ('copix:copix.error.unfounded.static',$fileName), E_USER_ERROR);
        }
    }*/
}
?>