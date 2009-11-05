<?php
/**
* @package     jelix
* @subpackage  installer
* @author      Laurent Jouanneau
* @contributor 
* @copyright   2008-2009 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

require_once(JELIX_LIB_PATH.'installer/jIInstallReporter.iface.php');
require_once(JELIX_LIB_PATH.'installer/jIInstallerComponent.iface.php');
require_once(JELIX_LIB_PATH.'installer/jInstallerBase.class.php');
require_once(JELIX_LIB_PATH.'core/jConfigCompiler.class.php');
require(JELIX_LIB_PATH.'installer/jInstallerMessageProvider.class.php');


/**
 * simple text reporter
 */
class textInstallReporter implements jIInstallReporter {
    
    function start() {
        echo "Installation start..\n";
    }

    /**
     * displays a message
     * @param string $message the message to display
     * @param string $type the type of the message : 'error', 'notice', 'warning', ''
     */
    function message($message, $type='') {
        echo ($type != ''?'['.$type.'] ':'').$message."\n";
    }

    /**
     * called when the installation is finished
     * @param array $results an array which contains, for each type of message,
     * the number of messages
     */
    function end($results) {
        echo "Installation ended.\n";
    }
}




/**
 * Installer Exception
 *
 * It handles installer messages.
 * @package  jelix
 * @subpackage installer
 */
class jInstallerException extends Exception {

    /**
     * the locale key
     * @var string
     */
    protected $localeKey = '';

    /**
     * parameters for the locale key
     */
    protected $localeParams = null;

    /**
     * @param string $localekey a locale key
     * @param array $localeParams parameters for the message (for sprintf)
     */
    public function __construct($localekey, $localeParams = null) {

        $this->localeKey = $localekey;
        $this->localeParams = $localeParams;
        parent::__construct($localekey, 0);
    }

    /**
     * getter for the locale parameters
     * @return string
     */
    public function getLocaleParameters(){
        return $this->localeParams;
    }

    /**
     * getter for the locale key
     * @return string
     */
    public function getLocaleKey(){
        return $this->localeKey;
    }

}

/**
 * container for entry points properties
 */
class jInstallerEntryPointProperties {
    /** @var StdObj   configuration parameters */
    public $config;
    
    public $configFile;
    
    public $isCliScript;
    
    public $scriptName;
    
    public $file;
    
}


/**
 * main class for the installation
 *
 * It load all entry points configurations. Each configurations has its own
 * activated modules. jInstaller then construct a tree dependencies for these
 * activated modules, and launch their installation and the installation
 * of their dependencies.
 * An installation can be an initial installation, or just an upgrade
 * if the module is already installed.
 * @internal The object which drives the installation of a component
 * (module, plugin...) is an object which inherits from jInstallerComponentBase.
 * This object calls load a file from the directory of the component. this
 * file should contain a class which should inherits from jInstallerModule
 * or jInstallerPlugin. this class should implements processes to install
 * the component.
 */
class jInstaller {

    const STATUS_UNINSTALLED = 0;
    const STATUS_INSTALLED = 1;

    const ACCESS_FORBIDDEN = 0;
    const ACCESS_PRIVATE = 1;
    const ACCESS_PUBLIC = 2;
    
    const INSTALL_ERROR_MISSING_DEPENDENCIES = 1;
    const INSTALL_ERROR_CIRCULAR_DEPENDENCY = 2;
    
    /**
     *  @var jIniFileModifier it represents the installer.ini.php file.
     */
    public $installerIni = null;
    
    /**
     * parameters for each entry point.
     * @var array of jInstallerEntryPointProperties
     */
    protected $epProperties = array();

    /**
     * list of entry point identifiant (provided by the configuration compiler).
     * identifiant of the entry point is the path+filename of the entry point
     * without the php extension
     * @var array   key=entry point name, value=url id
     */
    protected $epId = array();

    /**
     * list of modules for each entry point
     * @var array first key: entry point id, second key: module name, value = jInstallerComponentModule
     */
    protected $modules = array();
    
    /**
     * list of all modules of the application
     * @var array key=path of the module, value = jInstallerComponentModule
     */
    protected $allModules = array();

    /**
     * the object responsible of the results output
     * @var jIInstallReporter
     */
    public $reporter;

    /**
     * @var JInstallerMessageProvider
     */
    public $messages;

    public $nbError = 0;
    public $nbOk = 0;
    public $nbWarning = 0;
    public $nbNotice = 0;


    /**
     * initialize the installation
     *
     * it reads configurations files of all entry points, and prepare object for
     * each module, needed to install/upgrade modules.
     */
    function __construct ($reporter, $lang='') {

        set_error_handler('jInstallerErrorHandler');
        //set_exception_handler('jExceptionHandler');

        $this->reporter = $reporter;
        $this->messages = new jInstallerMessageProvider($lang);

        if (!file_exists(JELIX_APP_CONFIG_PATH.'installer.ini.php'))
            file_put_contents(JELIX_APP_CONFIG_PATH.'installer.ini.php', ";<?php die(''); ?>
; for security reasons , don't remove or modify the first line
; don't modify this file if you don't know what you do. it is generated automatically by jInstaller

");
        $this->modules = array();
        $this->allModules = array();
        $this->installerIni = new jIniFileModifier(JELIX_APP_CONFIG_PATH.'installer.ini.php');

        $xml = simplexml_load_file(JELIX_APP_PATH.'project.xml');

        $configFileList = array();

        // 
        foreach ($xml->entrypoints->entry as $entrypoint) {

            $file = (string)$entrypoint['file'];
            $configFile = (string)$entrypoint['config'];
            $isCliScript = (isset($entrypoint['cli'])?(string)$entrypoint['cli'] == 'true':false);

            // ignore entry point which have the same config file of an other one
            if (isset($configFileList[$configFile]))
                continue;

            $configFileList[$configFile] = true;

            $c = new jInstallerEntryPointProperties();
            $c->isCliScript = $isCliScript;
            $c->configFile = $configFile;
            $c->scriptName =  ($isCliScript?$file:'/'.$file);
            $c->file = $file;

            // we don't load yet a jIniMultiFilesModifier because installer
            // could modify defaultconfig file, so other installer should have
            // a jIniMultiFilesModifier loaded with the good version of the
            // defaultconfig file. However, we load a static version of
            // the configuration here because we need it.
            $c->config = jConfigCompiler::read($configFile, true,
                                               $isCliScript, $c->scriptName);
            
            $id = $c->config->urlengine['urlScriptId'];
            $this->epId[$file] = $id;
            $this->epProperties[$id] = $c;
            $this->modules[$id] = array();

            foreach ($c->config->_allModulesPathList as $name=>$path) {
                $installed = $c->config->modules[$name.'.installed'];
                $version = $c->config->modules[$name.'.version'];
                $this->installerIni->setValue($name.'.installed', $installed, $id);
                $this->installerIni->setValue($name.'.version', $version, $id);
                
                if (!isset($this->allModules[$path])) {
                    $this->allModules[$path] = new jInstallerComponentModule($name, $path, $this);
                }
                $m = $this->allModules[$path];
                $m->setEntryPointData ($id, $c->config->modules[$name.'.access'],
                                       $c->config->modules[$name.'.dbprofile'],
                                       $installed, $version);
                $this->modules[$id][$name] = $m;
            }
        }

        $this->installerIni->save();
    }

    /**
     * install and upgrade if needed, all modules for each
     * entry point. Only modules which have an access property > 0
     * are installed.
     * @return boolean
     */
    public function installApplication() {

        $this->startMessage();
        $result = true;

        foreach(array_keys($this->epProperties) as $epId) {
            $modules = array();
            foreach($this->modules[$epId] as $name => $module) {
                if ($module->getAccessLevel($epId) == 0)
                    continue;
                $modules[$name] = $module;
            }
            $result = $result & $this->_installModules($modules, $epId);
        }

        $this->installerIni->save();
        $this->endMessage();
        return $result;
    }

    /**
     * install given modules even if they don't have an access property > 0
     * @param array $list array of module names
     * @param string $entrypoint  the entrypoint name as it appears in project.xml
     * @return boolean true if the installation is ok
     */
    public function installModules($list, $entrypoint = 'index.php') {
        
        $this->startMessage();
        
        if (!isset($this->epId[$entrypoint])) {
            throw new Exception("unknow entry point");
        }
        
        
        $epId = $this->epId[$entrypoint];
        $allModules = &$this->modules[$epId];
        
        $modules = array();
        // always install jelix
        array_unshift($list, 'jelix');
        foreach($list as $name) {
            if (!isset($allModules[$name])) {
                $this->error('module.unknow', $name);
            }
            else
                $modules[] = $allModules[$name];
        }

        $result = $this->_installModules($modules, $epId);
        $this->installerIni->save();
        $this->endMessage();
        return $result;
    }
    
    /**
     * @param array $modules list of jInstallerComponentModule
     * @param string $epId  the entrypoint id
     * @return boolean true if the installation is ok
     */
    protected function _installModules(&$modules, $epId) {

        $this->ok('install.entrypoint.start', $epId);
        
        $ep = $this->epProperties[$epId];
        $GLOBALS['gJConfig'] = $ep->config;
        
        // load an update
        $epConfig = new jIniMultiFilesModifier(JELIX_APP_CONFIG_PATH.'defaultconfig.ini.php',
                                             JELIX_APP_CONFIG_PATH.$ep->configFile);

        $result = $this->checkDependencies($modules, $epId);

        if ($result) {
            $this->ok('install.dependencies.ok');

            // pre install
            foreach($this->_componentsToInstall as $item) {
                list($component, $toInstall) = $item;
                try {
                    if ($toInstall) {
                        $installer = $component->getInstaller($epConfig, $epId);
                        $installer->preInstall();
                    }
                    else {
                        foreach($component->getUpgraders($epConfig, $epId) as $upgrader) {
                            $upgrader->preInstall();
                        }
                    }
                } catch (jInstallerException $e) {
                    $result = false;
                    $this->error ($e->getLocaleKey(), $e->getLocaleParameters());
                } catch (Exception $e) {
                    $result = false;
                    $this->error ('install.module.error', $e->getMessage());
                }
            }
            
            $installedModules = array();
            // install
            if ($result) {
                try {
                    foreach($this->_componentsToInstall as $item) {
                        list($component, $toInstall) = $item;
                        if ($toInstall) {
                            $installer = $component->getInstaller($epConfig, $epId);
                            $installer->install();
                            $this->installerIni->setValue($component->getName().'.installed',
                                                           1, $epId);
                            $this->installerIni->setValue($component->getName().'.version',
                                                           $component->getSourceVersion(), $epId);
                            $this->ok('install.module.installed', $component->getName());
                            $installedModules[] = array($component, true);
                        }
                        else {
                            $lastversion='';
                            foreach($component->getUpgraders($epConfig, $epId) as $upgrader) {
                                $upgrader->install();
                                // we set the version of the upgrade, so if an error occurs in
                                // the next upgrader, we won't have to re-run this current upgrader
                                // during a future update
                                $this->installerIni->setValue($component->getName().'.version',
                                                              $upgrader->version, $epId);
                                $this->ok('install.module.upgraded',
                                          array($component->getName(), $upgrader->version));
                                $lastversion = $upgrader->version;
                            }
                            // we set the version to the component version, because the version
                            // of the last upgrader could not correspond to the component version.
                            if ($lastversion != $component->getSourceVersion()) {
                                $this->installerIni->setValue($component->getName().'.version',
                                                              $component->getSourceVersion(), $epId);
                                $this->ok('install.module.upgraded',
                                          array($component->getName(), $component->getSourceVersion()));
                            }
                            $installedModules[] = array($component, false);
                        }
                        if ($epConfig->isModified()) {
                            $epConfig->save();
                            // we re-load configuration file for each module because
                            // previous module installer could have modify it.
                            $GLOBALS['gJConfig'] = $ep->config =
                                jConfigCompiler::read($ep->configFile, true,
                                                      $ep->isCliScript,
                                                      $ep->scriptName);
                        }
                    }
                } catch (jInstallerException $e) {
                    $result = false;
                    $this->error ($e->getLocaleKey(), $e->getLocaleParameters());
                } catch (Exception $e) {
                    $result = false;
                    $this->error ('install.module.error', array($component->getName(), $e->getMessage()));
                }
            }
            
            // post install
            foreach($installedModules as $item) {
                try {
                    list($component, $toInstall) = $item;
                    if ($toInstall) {
                        $installer = $component->getInstaller($epConfig, $epId);
                        $installer->postInstall();
                    }
                    else {
                        foreach($component->getUpgraders($epConfig, $epId) as $upgrader) {
                            $upgrader->postInstall();
                        }
                    }
                    if ($epConfig->isModified()) {
                        $epConfig->save();
                        // we re-load configuration file for each module because
                        // previous module installer could have modify it.
                        $GLOBALS['gJConfig'] = $ep->config =
                            jConfigCompiler::read($ep->configFile, true,
                                                  $ep->isCliScript,
                                                  $ep->scriptName);
                    }
                } catch (jInstallerException $e) {
                    $result = false;
                    $this->error ($e->getLocaleKey(), $e->getLocaleParameters());
                } catch (Exception $e) {
                    $result = false;
                    $this->error ('install.module.error', $e->getMessage());
                }
            }
        }
        else
            $this->error('install.bad.dependencies');

        $this->ok('install.entrypoint.end', $epId);

        return $result;
    }


    protected $_componentsToInstall = array();
    protected $_checkedComponents = array();
    protected $_checkedCircularDependency = array();

    /**
     * check dependencies of given modules and plugins
     *
     * @param array $list  list of jInstallerComponentModule/jInstallerComponentPlugin objects
     * @throw jException if the install has failed
     */
    protected function checkDependencies ($list, $epId) {
        
        $this->_checkedComponents = array();
        $this->_componentsToInstall = array();
        $result = true;
        foreach($list as $component) {
            $this->_checkedCircularDependency = array();
            if (!isset($this->_checkedComponents[$component->getName()])) {
                try {
                    $component->init();

                    $this->_checkDependencies($component, $epId);

                    if (!$component->isInstalled($epId)) {
                        $this->_componentsToInstall[] = array($component, true);
                    }
                    else if (!$component->isUpgraded($epId)) {
                        $this->_componentsToInstall[] =array($component, false);
                    }
                } catch (jInstallerException $e) {
                    $result = false;
                    $this->error ($e->getLocaleKey(), $e->getLocaleParameters());
                } catch (Exception $e) {
                    $result = false;
                    $this->error ($e->getMessage(), null, true);
                }
            }
        }
        return $result;
    }

    /**
     * check dependencies of a module
     * @param jInstallerComponentBase $component
     */
    protected function _checkDependencies($component, $epId) {

        if (isset($this->_checkedCircularDependency[$component->getName()])) {
            $component->inError = self::INSTALL_ERROR_CIRCULAR_DEPENDENCY;
            throw new jInstallerException ('module.circular.dependency',$component->getName());
        }

        $this->ok('install.module.check.dependency', $component->getName());

        $this->_checkedCircularDependency[$component->getName()] = true;

        $compNeeded = '';
        foreach ($component->dependencies as $compInfo) {
            // TODO : supports others type of components
            if ($compInfo['type'] != 'module')
                continue;
            $name = $compInfo['name'];
            $comp = $this->modules[$epId][$name];
            if (!$comp)
                $compNeeded .= $name.', ';
            else {
                if (!isset($this->_checkedComponents[$comp->getName()])) {
                    $comp->init();
                }

                if (!$comp->checkVersion($compInfo['minversion'], $compInfo['maxversion'])) {
                    if ($name == 'jelix') {
                        $args = $component->getJelixVersion();
                        array_unshift($args, $component->getName());
                        throw new jInstallerException ('module.bad.jelix.version', $args);
                    }
                    else
                        throw new jInstallerException ('module.bad.dependency.version',array($component->getName(), $comp->getName(), $compInfo['minversion'], $compInfo['maxversion']));
                }

                if (!isset($this->_checkedComponents[$comp->getName()])) {
                    $this->_checkDependencies($comp, $epId);
                    if (!$comp->isInstalled($epId)) {
                        $this->_componentsToInstall[] = array($comp, true);
                    }
                    else if(!$comp->isUpgraded($epId)) {
                        $this->_componentsToInstall[] = array($comp, false);
                    }
                }
            }
        }

        $this->_checkedComponents[$component->getName()] = true;
        unset($this->_checkedCircularDependency[$component->getName()]);

        if ($compNeeded) {
            $component->inError = self::INSTALL_ERROR_MISSING_DEPENDENCIES;
            throw new jInstallerException ('module.needed', array($component->getName(), $compNeeded));
        }
    }
    
    protected function startMessage () {
        $this->nbError = 0;
        $this->nbOk = 0;
        $this->nbWarning = 0;
        $this->nbNotice = 0;
        $this->reporter->start();
    }
    
    protected function endMessage() {
        $this->reporter->end(array('error'=>$this->nbError, 'warning'=>$this->nbWarning, 'ok'=>$this->nbOk,'notice'=>$this->nbNotice));
    }

    protected function error($msg, $params=null, $fullString=false){
        if($this->reporter) {
            if (!$fullString)
                $msg = $this->messages->get($msg,$params);
            $this->reporter->message($msg, 'error');
        }
        $this->nbError ++;
    }

    protected function ok($msg, $params=null, $fullString=false){
        if($this->reporter) {
            if (!$fullString)
                $msg = $this->messages->get($msg,$params);
            $this->reporter->message($msg, '');
        }
        $this->nbOk ++;
    }

    protected function warning($msg, $params=null, $fullString=false){
        if($this->reporter) {
            if (!$fullString)
                $msg = $this->messages->get($msg,$params);
            $this->reporter->message($msg, 'warning');
        }
        $this->nbWarning ++;
    }

    protected function notice($msg, $params=null, $fullString=false){
        if($this->reporter) {
            if (!$fullString)
                $msg = $this->messages->get($msg,$params);
            $this->reporter->message($msg, 'notice');
        }
        $this->nbNotice ++;
    }

}




function jInstallerErrorHandler($errno, $errmsg, $filename, $linenum, $errcontext){

    if (error_reporting() == 0)
        return;

    $codeString = array(
        E_ERROR         => 'error',
        E_RECOVERABLE_ERROR => 'error',
        E_WARNING       => 'warning',
        E_NOTICE        => 'notice',
        E_DEPRECATED    => 'deprecated',
        E_USER_ERROR    => 'error',
        E_USER_WARNING  => 'warning',
        E_USER_NOTICE   => 'notice',
        E_USER_DEPRECATED => 'deprecated',
        E_STRICT        => 'strict'
    );

    if (isset ($codeString[$errno])){
        $codestr = $codeString[$errno];
    }else{
        $codestr = 'error';
    }

    $trace = debug_backtrace();
    array_shift($trace);

    echo '['.$codestr.'] '.$errmsg."\n";
    echo '  '.$filename. ' (line: '.$linenum.")\n";

    echo "\ttrace:";
    foreach($trace as $k=>$t){
        echo "\n\t$k\t".(isset($t['class'])?$t['class'].$t['type']:'').$t['function']."()\t";
        echo (isset($t['file'])?$t['file']:'[php]').' : '.(isset($t['line'])?$t['line']:'');
    }
    echo "\n";

}
