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
require_once(JELIX_LIB_PATH.'installer/jInstallerBase.class.php');
require_once(JELIX_LIB_PATH.'core/jConfigCompiler.class.php');


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
     * @param integer $code error code (can be provided by the localized message)
     * @param string $lang
     * @param string $charset
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
class jInstaller extends jInstallerBase {

    const STATUS_UNINSTALLED = 0;
    const STATUS_INSTALLED = 1;

    const ACCESS_FORBIDDEN = 0;
    const ACCESS_PRIVATE = 1;
    const ACCESS_PUBLIC = 2;
    
    const INSTALL_ERROR_MISSING_DEPENDENCIES = 1;
    const INSTALL_ERROR_CIRCULAR_DEPENDENCY = 2;

    public $appConfig = null;
    
    public $installConfig = null;

    protected $rConfig = null;
    protected $modules = array();

    function __construct ($reporter, $lang='') {
        parent::__construct($reporter, $lang);

        if (!file_exists(JELIX_APP_CONFIG_PATH.'installer.ini.php'))
            file_put_contents(JELIX_APP_CONFIG_PATH.'installer.ini.php', ";<?php die(''); ?>
; for security reasons , don't remove or modify the first line
; don't modify this file if you don't know what you do. it is generated automatically by jInstaller
[modules]

");

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
            $this->modules[$name] = new jInstallerModule($name, $path, $installed, $access, $version, $this);
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

    /**
     * install given modules
     * @param array $list array of module names
     */
    public function installModules($list) {
        
        $this->startMessage ();
        
        $modules = array();
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
            $this->_installingComponents = array();
            // call the install() method of each object.
            foreach($modules as $component) {
                try {
                    $this->_installComponent($component);
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


    protected $_checkedComponents = array();
    protected $_checkedCircularDependency = array();

   /**
     * check dependencies of given modules and plugins
     *
     * @param array $list  list of jInstallerModule/jInstallerPlugin objects
     * @throw jException if the install has failed
     */
    protected function checkDependencies ($list) {
        
        $this->_checkedComponents = array();
        $result = true;
        foreach($list as $component) {
            $this->_checkedCircularDependency = array();
            if (!isset($this->_checkedComponents[$component->getName()])) {
                try {
                    $component->init();
                    $this->_checkDependencies($component);
                } catch( jInstallerException $e) {
                    $result = false;
                    $this->error ($e->getLocaleKey(), $e->getLocaleParameters());
                } catch( Exception $e) {
                    $result = false;
                    $this->error ($e->getMessage(), null, true);
                }
            }
        }
        return $result;
    }

    /**
     * check dependencies of a module
     * @param jInstallerBase $component
     */
    protected function _checkDependencies($component) {

        if (isset($this->_checkedCircularDependency[$component->getName()])) {
            $component->inError = self::INSTALL_ERROR_CIRCULAR_DEPENDENCY;
            throw new jInstallerException ('module.circular.dependency',$component->getName());
        }
        $this->ok('install.module.check.dependency', $component->getName());

        $this->_checkedCircularDependency[$component->getName()] = true;

        if (!$component->checkJelixVersion(JELIX_VERSION)) {
            $args = $component->getJelixVersion();
            array_unshift($args, $component->getName());
            throw new jInstallerException ('module.bad.jelix.version', $args);
        }

        $compNeeded = '';
        foreach($component->dependencies as $compInfo) {
            $name = (string)$compInfo['name'];
            $comp = $this->getModule($name);
            if (!$comp)
                $compNeeded.=$name.', ';
            else {
                if (!isset($this->_checkedComponents[$comp->getName()]))
                    $comp->init();

                if (!$comp->checkVersion($compInfo['minversion'], $compInfo['maxversion']))
                    throw new jInstallerException ('module.bad.dependency.version',array($component->getName(), $comp->getName(), $compInfo['minversion'], $compInfo['maxversion']));

                if (!isset($this->_checkedComponents[$comp->getName()])) 
                    $this->_checkDependencies($comp);
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
     * install a module or a plugin
     * should be called after a dependencies check.
     * @param jInstallerBase $component
     */
    protected function _installComponent($component) {

        if ($component->isInstalled()) {
            $this->ok('install.module.already.installed', $component->getName());
            return;
        }

        $compNeeded = '';
        foreach ($component->dependencies as $compInfo) {
            $comp = $this->getModule((string)$compInfo['name']);
            $this->_installComponent($comp);
        }

        try {
            $component->install();
            $this->installConfig->setValue($component->getName().'.installed', 1, 'modules');
            $this->installConfig->setValue($component->getName().'.version', $component->getSourceVersion(), 'modules');
            $this->ok('install.module.installed', $component->getName());
        } catch(Exception $e) {
            throw $e;
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
     * import a sql script into the given profile.
     *
     * The name of the script should be store in install/sql/$name.databasetype.sql
     * in the directory of the component. (replace databasetype by mysql, pgsql etc.)
     * 
     * @param string $name the name of the script, without suffixes
     */
    static public function execSQLScript($name, $profile='') {
        $tools = jDb::getTools($profile);
        $p = jDb::getProfile ($profile);
        $driver = $p['driver'];
        if($driver == 'pdo'){
            preg_match('/^(\w+)\:.*$/',$p['dsn'], $m);
            $driver = $m[1];
        }
        $tools->execSQLScript($this->path.'install/sql/'.$name.'.'.$driver.'.sql');
    }

    /**
     * @param string $sourcePath
     * @param string $targetPath
     */
    static function copyDirectoryContent($sourcePath, $targetPath) {
        jFile::createDir($targetPath);
        $dir = new DirectoryIterator($sourcePath);
        foreach ($dir as $dirContent) {
            if ($dirContent->isFile()) {
                copy($dirContent->getPathName(), $targetPath.substr($dirContent->getPathName(), strlen($dirContent->getPath())));
            } else {
                if (!$dirContent->isDot() && $dirContent->isDir()) {
                    $newTarget = $targetPath.substr($dirContent->getPathName(), strlen($dirContent->getPath()));
                    $this->copyDirectoryContent($dirContent->getPathName(),$newTarget );
                }
            }
        }
    }

/*$path = JELIX_APP_PATH.'project.xml';

        $projectXml = new DOMDocument();

        if(!$projectXml->load($path)){
            throw new jException('jelix~install.invalid.xml.file',array($path));
        }
        
        $root = $projectXml->documentElement;

        $modules = array();
        
        if ($root->namespaceURI == 'http://jelix.org/ns/project/1.0') {
            $xml = simplexml_import_dom($projectXml);
            $entrypoints = $xml->entrypoints[0]->entry;
            foreach ($entrypoints as $entrypoint) {
                $config = jConfigCompiler::read($entrypoint['config'], true);
                foreach($config->_allModulesPathList as $name=>$path) {
                    if (isset($modules[$name])) {
                        continue;
                    }
                    $access = $config->modules[$name.'.access'];
                    $installed = $config->modules[$name.'.installed'];
                    $version = $config->modules[$name.'.version'];
                    $modules[$name] = new jInstallerModule($name, $path, $installed, $access, $version);
                }
            }
        }*/

}