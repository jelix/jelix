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
    function showMessage($message, $type='') {
        echo ($type != ''?$type.': ':'').$message."\n";
    }

    /**
     * called when the installation is finished
     * @param object $installer
     */
    function end($installer) {
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
 * main class for the installation
 */
class jInstaller {

    const STATUS_UNINSTALLED = 0;
    const STATUS_INSTALLED = 1;

    const ACCESS_FORBIDDEN = 0;
    const ACCESS_PRIVATE = 1;
    const ACCESS_PUBLIC = 2;
    
    const INSTALL_ERROR_MISSING_DEPENDENCIES = 1;
    const INSTALL_ERROR_CIRCULAR_DEPENDENCY = 2;

    public $appConfig = null;
    
    public $installConfig = null;
    
    protected $epConfig = array();

    protected $rConfig = null;
    protected $modules = array();

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


    function __construct ($reporter, $lang='') {

        $this->reporter = $reporter;
        $this->messages = new jInstallerMessageProvider($lang);

        if (!file_exists(JELIX_APP_CONFIG_PATH.'installer.ini.php'))
            file_put_contents(JELIX_APP_CONFIG_PATH.'installer.ini.php', ";<?php die(''); ?>
; for security reasons , don't remove or modify the first line
; don't modify this file if you don't know what you do. it is generated automatically by jInstaller
[modules]

");
        $xml = simplexml_load_file(JELIX_APP_PATH.'project.xml');
        foreach ($xml->entrypoints->entrypoint as $entrypoint) {
            $file = (string)$entrypoint['file'];
            $this->epConfig[$file] = new jIniMultiFilesModifier(JELIX_APP_CONFIG_PATH.'defaultconfig.ini.php', JELIX_APP_CONFIG_PATH.((string)$entrypoint['config']));
        }
        $this->rConfig = jConfigCompiler::read('defaultconfig.ini.php', true);
        $this->appConfig = new jIniFileModifier(JELIX_APP_CONFIG_PATH.'defaultconfig.ini.php');
        $this->installConfig = new jIniFileModifier(JELIX_APP_CONFIG_PATH.'installer.ini.php');
        $this->modules = array();
        foreach($this->rConfig->_allModulesPathList as $name=>$path) {
            $access = $this->rConfig->modules[$name.'.access'];
            $installed = $this->rConfig->modules[$name.'.installed'];
            $version = $this->rConfig->modules[$name.'.version'];
            $this->installConfig->setValue($name.'.installed', $installed, 'modules');
            $this->installConfig->setValue($name.'.version', $version, 'modules');
            $this->modules[$name] = new jInstallerComponentModule($name, $path, $installed, $access, $version, $this);
        }
        $this->installConfig->save();

        $GLOBALS['gJConfig'] = jConfig::load('defaultconfig.ini.php');
    }

    /**
     * get a module by its name
     * @return jInstallerModule
     */
    public function getModule($name) {
        if (isset($this->modules[$name]))
            return $this->modules[$name];
        else
            return null;
    }
    
    
    public function installApplication() {
        
        
    }
    
    

    /**
     * install given modules
     * @param array $list array of module names
     */
    public function installModules($list) {
        
        $this->startMessage ();
        
        $modules = array();
        // always install jelix
        array_unshift($list, 'jelix');
        foreach($list as $name) {
            if (!isset($this->modules[$name])) {
                $this->error('module.unknow', $name);
            }
            else
                $modules[] = $this->modules[$name];
        }

        $result = $this->checkDependencies($modules);

        if ($result) {
            $this->ok('install.dependencies.ok');

            // pre install
            foreach($this->_componentsToInstall as $item) {
                list($component, $toInstall) = $item;
                try {
                    if ($toInstall) {
                        $installer = $component->getInstaller();
                        $installer->preInstall();
                    }
                    else {
                        foreach($component->getUpgraders() as $upgrader) {
                            $upgrader->preInstall();
                        }
                    }
                } catch( jInstallerException $e) {
                    $result = false;
                    $this->error ($e->getLocaleKey(), $e->getLocaleParameters());
                } catch( Exception $e) {
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
                            $installer = $component->getInstaller();
                            $installer->install();
                            $this->installConfig->setValue($component->getName().'.installed', 1, 'modules');
                            $this->installConfig->setValue($component->getName().'.version', $component->getSourceVersion(), 'modules');
                            $this->ok('install.module.installed', $component->getName());
                            $installedModules[] = array($component, true);
                        }
                        else {
                            $lastversion='';
                            foreach($component->getUpgraders() as $upgrader) {
                                $upgrader->install();
                                // we set the version of the upgrade, so if an error occurs in
                                // the next upgrader, we won't have to re-run this current upgrader
                                // during a future update
                                $this->installConfig->setValue($component->getName().'.version', $upgrader->version, 'modules');
                                $this->ok('install.module.upgraded', array($component->getName(), $upgrader->version));
                                $lastversion = $upgrader->version;
                            }
                            // we set the version to the component version, because the version
                            // of the last upgrader could not correspond to the component version.
                            if ($lastversion != $component->getSourceVersion()) {
                                $this->installConfig->setValue($component->getName().'.version', $component->getSourceVersion(), 'modules');
                                $this->ok('install.module.upgraded', array($component->getName(), $component->getSourceVersion()));
                            }
                            $installedModules[] = array($component, false);
                        }
                    }
                } catch( jInstallerException $e) {
                    $result = false;
                    $this->error ($e->getLocaleKey(), $e->getLocaleParameters());
                } catch( Exception $e) {
                    $result = false;
                    $this->error ('install.module.error', $e->getMessage());
                }
            }
            
            // post install
            foreach($installedModules as $item) {
                try {
                    list($component, $toInstall) = $item;
                    if ($toInstall) {
                        $installer = $component->getInstaller();
                        $installer->postInstall();
                    }
                    else {
                        foreach($component->getUpgraders() as $upgrader) {
                            $upgrader->postInstall();
                        }
                    }
                } catch( jInstallerException $e) {
                    $result = false;
                    $this->error ($e->getLocaleKey(), $e->getLocaleParameters());
                } catch( Exception $e) {
                    $result = false;
                    $this->error ('install.module.error', $e->getMessage());
                }
            }
        }
        else
            $this->error('install.bad.dependencies');
        
        $this->installConfig->save();
        $this->endMessage();
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
    protected function checkDependencies ($list) {
        
        $this->_checkedComponents = array();
        $this->_componentsToInstall = array();
        $result = true;
        foreach($list as $component) {
            $this->_checkedCircularDependency = array();
            if (!isset($this->_checkedComponents[$component->getName()])) {
                try {
                    $component->init();

                    $this->_checkDependencies($component);

                    if (!$component->isInstalled()) {
                        $this->_componentsToInstall[] = array($component, true);
                    }
                    else if (!$component->isUpgraded()) {
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
    protected function _checkDependencies($component) {

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
            $comp = $this->getModule($name);
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
                    $this->_checkDependencies($comp);
                    if (!$comp->isInstalled()) {
                        $this->_componentsToInstall[] = array($comp, true);
                    }
                    else if(!$comp->isUpgraded()) {
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

    /**
     * install a package.
     * a package is a zip or gz archive. Top directories of this archive
     * should be a plugin or a module not an application. So in this directories
     * it should contains a module.xml, or a plugin.xml.
     * @param string $packageFileName  the file path of the package
     * @return array an array of jInstallerModule or jInstallerPlugin objects,
     * corresponding to 
     */
    function installPackage ($packageFileName) {
        // it should
        // * extract the package in the temp directory
        // * verify that modules/plugins are not already installed
        // in the application
        // * if ok, it should copy modules and plugins in the right directories
        // into the application
        // * create instance of jInstallerModule/jInstallerPlugin corresponding of
        // each module & plugins
        // call install()
        // if the install fails, the new directories of modules and plugins
        // should be deleted.
    }
    
    /**
     * get on object to modify the config file of an entry point 
     * @param string $filename relative path to the var/config directory
     * @return jIniMultiFilesModifier
     */
    function getEntryPointConfig($entrypoint) {
        
         throw new Exception('not implemented');
        
        //TODO
        // get the path of the config file corresponding to the entrypoint,
        // from the project.xml
        $filename = '';
        return new jIniMutliFilesModifier(JELIX_APP_CONFIG_PATH.'defaultconfig.ini.php',
                                          JELIX_APP_CONFIG_PATH.$filename);
    }

    function addEntryPoint($filename, $type, $configFilename) {
        throw new Exception('not implemented');
        // should modify the project.xml
        // if the config file doesn't exist, create it
        // if the entrypoint doesn't exist, create it, with the given type
    }


    function removeEntryPoint($filename) {
        throw new Exception('not implemented');
        // should modify the project.xml
        // if the config file is not used by another entrypoint, remove it
    }

    protected function startMessage () {
        $this->nbError = 0;
        $this->nbOk = 0;
        $this->nbWarning = 0;
        $this->nbNotice = 0;
        $this->reporter->start();
    }
    
    protected function endMessage() {
        $this->reporter->end($this);
    }

    protected function error($msg, $params=null, $fullString=false){
        if($this->reporter) {
            if (!$fullString)
                $msg = $this->messages->get($msg,$params);
            $this->reporter->showMessage ( $msg, 'error');
        }
        $this->nbError ++;
    }

    protected function ok($msg, $params=null, $fullString=false){
        if($this->reporter) {
            if (!$fullString)
                $msg = $this->messages->get($msg,$params);
            $this->reporter->showMessage ( $msg, '');
        }
        $this->nbOk ++;
    }
    /**
     * generate a warning
     * @param string $msg  the key of the message to display
     */
    protected function warning($msg, $params=null, $fullString=false){
        if($this->reporter) {
            if (!$fullString)
                $msg = $this->messages->get($msg,$params);
            $this->reporter->showMessage ( $msg, 'warning');
        }
        $this->nbWarning ++;
    }

    protected function notice($msg, $params=null, $fullString=false){
        if($this->reporter) {
            if (!$fullString)
                $msg = $this->messages->get($msg,$params);
            $this->reporter->showMessage ( $msg, 'notice');
        }
        $this->nbNotice ++;
    }

}