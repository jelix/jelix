<?php

/**
 * @author     Laurent Jouanneau
 * @copyright  2015-2026 Laurent Jouanneau
 *
 * @see        https://jelix.org
 *
 * @licence    http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */

use Jelix\Core\Config;
use Jelix\Core\Config\FrameworkRuntimeConfig;
use Jelix\Core\Infos\FrameworkInfos;
use Jelix\Core\Services;

/**
 * Store some application parameter for the current application.
 *
 * @internal
 */
class jAppInstance
{
    public $tempBasePath = '';

    public $appPath = '';

    public $appSystemPath = '';

    public $varPath = '';

    public $logPath = '';

    public $configPath = '';

    public $wwwPath = '';

    public $varLibPath = '';

    /**
     * @var string
     *
     * @deprecated
     */
    public $scriptPath = '';

    public $env = 'www/';

    public $configAutoloader;

    protected $_version;

    /**
     * @var object object containing all configuration options of the application
     */
    public $config;

    public $coord;

    protected $_modulesDirPath = array();

    protected $_modulesPath = array();

    protected $_pluginsDirPath = array();

    protected $_localesDirPath = array();

    protected $_allModulesPath;

    protected $_allPluginsPath;

    protected $_modulesContext = array();

    public $applicationInitFile = '';

    /**
     * @var Services
     */
    protected $_services;

    /**
     * @var FrameworkInfos
     */
    protected $_framework;

    /**
     * @var FrameworkRuntimeConfig
     */
    protected $frameworkRuntimeConfig;

    /**
     * initialize the application paths.
     *
     * Warning: given paths should be ended by a directory separator.
     *
     * @param string $appPath    application directory
     * @param string $wwwPath    www directory
     * @param string $varPath    var directory
     * @param string $logPath    log directory
     * @param string $configPath config directory
     * @param string $scriptPath scripts directory (deprecated)
     */
    public function __construct(
        $appPath,
        $wwwPath = null,
        $varPath = null,
        $logPath = null,
        $configPath = null,
        $scriptPath = null
    ) {
        $this->setPaths($appPath, $wwwPath, $varPath, $logPath, $configPath, $scriptPath);
        $this->coord = null;
        $this->config = null;
        $this->configAutoloader = null;
        $this->_framework = null;
    }

    /**
     * initialize the application paths.
     *
     * Warning: given paths should be ended by a directory separator.
     *
     * @param string $appPath    application directory
     * @param string $wwwPath    www directory
     * @param string $varPath    var directory
     * @param string $logPath    log directory
     * @param string $configPath config directory
     * @param string $scriptPath scripts directory
     */
    public function setPaths(
        $appPath,
        $wwwPath = null,
        $varPath = null,
        $logPath = null,
        $configPath = null,
        $scriptPath = null
    ) {
        $this->appPath = $appPath;
        $this->appSystemPath = $appPath.'app/system/';
        $this->wwwPath = (is_null($wwwPath) ? $appPath.'www/' : $wwwPath);
        $this->varPath = (is_null($varPath) ? $appPath.'var/' : $varPath);
        $this->logPath = (is_null($logPath) ? $this->varPath.'log/' : $logPath);
        $this->configPath = (is_null($configPath) ? $this->varPath.'config/' : $configPath);
        $this->scriptPath = (is_null($scriptPath) ? $appPath.'scripts/' : $scriptPath);
        $this->applicationInitFile = $appPath.'application.init.php';
        $this->varLibPath = $this->varPath.'lib/';
        $this->_framework = null;
        $this->mainConfigFile = '';
    }

    public function __destruct()
    {
        $this->unregisterAutoload();
    }

    public function checkEnvironment()
    {
        if ($this->tempBasePath == '/') {
            // if it equals to '/', this is because realpath has returned false in the application.init.php
            // so this is because the path doesn't exist.
            throw new Exception('Application temp directory doesn\'t exist !', 3);
        }

        if (!is_writable($this->tempBasePath)) {
            throw new Exception('Application temp base directory is not writable -- ('.$this->tempBasePath.')', 4);
        }

        if (!is_writable($this->logPath)) {
            throw new Exception('Application log directory is not writable -- ('.$this->logPath.')', 4);
        }
    }

    protected $mainConfigFile;

    /**
     * Main config file path.
     */
    public function mainConfigFile()
    {
        if ($this->mainConfigFile) {
            return $this->mainConfigFile;
        }

        $configFileName = $this->appPath.'app/system/mainconfig.ini.php';
        if (!file_exists($configFileName)) {
            if (jServer::isCLI() && strpos($_SERVER['SCRIPT_FILENAME'], 'installer.php') !== false) {
                throw new Exception("Don't find the app/system/mainconfig.ini.php file. You must change your installer.php script. See migration documentation.");
            }

            throw new Exception("Don't find the app/system/mainconfig.ini.php file. Low-level upgrade is needed. Launch the installer.");
        }
        $this->mainConfigFile = $configFileName;

        return $configFileName;
    }

    protected function unregisterAutoload()
    {
        if ($this->configAutoloader) {
            spl_autoload_unregister(array($this->configAutoloader, 'loadClass'));
            $this->configAutoloader = null;
        }
    }

    protected function registerAutoload()
    {
        if ($this->config) {
            date_default_timezone_set($this->config->timeZone);
            $this->configAutoloader = new Config\Autoloader($this->config);
            spl_autoload_register(array($this->configAutoloader, 'loadClass'));
            foreach ($this->config->_autoload_autoloader as $autoloader) {
                require_once $autoloader;
            }
        }
    }

    public function __clone()
    {
        if ($this->config) {
            $this->config = clone $this->config;
        }
        if ($this->coord) {
            $this->coord = clone $this->coord;
        }

        if ($this->frameworkRuntimeConfig) {
            $this->frameworkRuntimeConfig = clone $this->frameworkRuntimeConfig;
        }
    }

    public function setConfig($config, ?FrameworkRuntimeConfig $runtimeConfig = null)
    {
        $this->unregisterAutoload();
        $this->config = $config;
        if ($runtimeConfig) {
            $this->frameworkRuntimeConfig = $runtimeConfig;
        }
        $this->registerAutoload();
        $this->reloadServices();
    }

    public function onRestoringAsContext()
    {
        $this->registerAutoload();
    }

    public function getFrameworkConfig(): FrameworkRuntimeConfig
    {
        return $this->frameworkRuntimeConfig;
    }

    /**
     * Sometimes, the application.init.php may not be into the appPath, this method allows to indicate
     * the path to the application.init.php.
     *
     * @param string $path the full path to the application.init.php
     */
    public function setApplicationInitFile($path)
    {
        $this->applicationInitFile = $path;
    }

    /**
     * return the version of the application containing into a VERSION file
     * stored at the root of the application or from `jApp::config()->appVersion` (project.xml).
     *
     * It doesn't read the version from composer.json.
     *
     * @return string
     */
    public function version()
    {
        if ($this->_version === null) {
            if (file_exists($this->appPath.'VERSION')) {
                $this->_version = trim(str_replace(
                    array('SERIAL', "\n"),
                    array('0', ''),
                    file_get_contents($this->appPath.'VERSION')
                ));
            } elseif ($this->config && $this->config->appVersion) {
                $this->_version = $this->config->appVersion;
            } else {
                $this->_version = '0';
            }
        }

        return $this->_version;
    }

    /**
     * Declare a list of modules.
     *
     * This method must be called before loading the configuration with `jApp::loadConfig()`
     *
     * @param array|string  $basePath the directory path containing modules that can be used
     * @param null|string[] $modules  list of module name to declare, from the directory. By default: all sub-directories (null).
     *                                parameter used only if $basePath is a string
     */
    public function declareModulesDir($basePath, $modules = null)
    {
        $this->_allModulesPath = null;
        $this->_allPluginsPath = null;
        if (is_array($basePath)) {
            foreach ($basePath as $path) {
                $p = realpath($path);
                if ($p == '') {
                    throw new Exception('Given modules dir '.$path.'does not exists');
                }
                $this->_modulesDirPath[rtrim($p, '/')] = null;
            }
        } else {
            $p = realpath($basePath);
            if ($p == '') {
                throw new Exception('Given modules dir '.$basePath.'does not exists');
            }
            $this->_modulesDirPath[rtrim($p, '/')] = $modules;
        }
    }

    /**
     * @return string[] list of path where to find modules
     */
    public function getDeclaredModulesDir()
    {
        return array_keys($this->_modulesDirPath);
    }

    /**
     * @return array list of path (keys) where to find modules, and modules names (values)
     */
    public function getDeclaredModulesDirAndModules()
    {
        return $this->_modulesDirPath;
    }

    /**
     * declare a module.
     *
     * This method must be called before loading the configuration with `jApp::loadConfig()`
     *
     * @param mixed $modulePath
     */
    public function declareModule($modulePath)
    {
        $this->_allModulesPath = null;
        $this->_allPluginsPath = null;
        if (!is_array($modulePath)) {
            $modulePath = array($modulePath);
        }
        foreach ($modulePath as $path) {
            $p = realpath($path);
            if ($p == '') {
                throw new Exception('Given module dir '.$path.'does not exists');
            }
            $this->_modulesPath[] = rtrim($p, '/');
        }
    }

    public function getDeclaredModulePaths()
    {
        return $this->_modulesPath;
    }

    public function clearModulesPluginsPath()
    {
        $this->_modulesPath = array();
        $this->_modulesDirPath = array();
        $this->_pluginsDirPath = array();
        $this->_allModulesPath = null;
        $this->_allPluginsPath = null;
    }

    /**
     * Declare a directory containing some plugins.
     *
     * Note that it does not need to declare 'plugins/' inside modules, as they are declared automatically
     * when you declare modules.
     *
     * This method must be called before loading the configuration with `jApp::loadConfig()`
     *
     * @param string|string[] $basePath the directory path containing plugins that can be used
     */
    public function declarePluginsDir($basePath)
    {
        $this->_allPluginsPath = null;
        if (!is_array($basePath)) {
            $basePath = array($basePath);
        }
        foreach ($basePath as $path) {
            $p = realpath($path);
            if ($p == '') {
                throw new Exception('Given plugin dir '.$path.'does not exists');
            }
            $this->_pluginsDirPath[] = $p;
        }
    }

    /**
     * returns the list of enabled module.
     *
     * Must be called after the call of `jApp::loadConfig()`.
     *
     * @return array
     */
    public function getEnabledModulesPaths()
    {
        return $this->frameworkRuntimeConfig->getEnabledModulePathsList();
    }

    /**
     * returns all module paths, even those are not used by the application.
     *
     * @return string[] keys are module name, values are paths
     */
    public function getAllModulesPath()
    {
        if ($this->_allModulesPath === null) {
            $frameworkInfo = FrameworkInfos::load();
            $config = jApp::config();
            $compiler = new Config\FrameworkConfigCompiler(
                $this,
                $frameworkInfo,
                jApp::varConfigPath('installer.ini.php'),
            );

            $frameworkConfig = $compiler->compile(true);
            $this->_allModulesPath = $frameworkConfig->getAllModulePathsList();
        }

        return $this->_allModulesPath;
    }

    public function getDeclaredPluginPaths()
    {
        return $this->_pluginsDirPath;
    }

    /**
     * return all paths of directories containing plugins, even those which are
     * in enabled modules.
     *
     * @return string[]
     *
     * @deprecated internal use only, it will disappear in the next version
     */
    public function getAllPluginsPath()
    {
        if ($this->_allPluginsPath === null) {
            $frameworkInfo = FrameworkInfos::load();
            $config = jApp::config();
            $compiler = new Config\FrameworkConfigCompiler(
                $this,
                $frameworkInfo,
                jApp::varConfigPath('installer.ini.php'),
            );

            $frameworkConfig = $compiler->compile(true);
            $this->_allPluginsPath = $frameworkConfig->getEnabledPluginsDirectoryPathsList();
        }

        return $this->_allPluginsPath;
    }

    /**
     * load a plugin from a plugin directory (any type of plugins).
     *
     * @param string $name         the name of the plugin
     * @param string $type         the type of the plugin
     * @param string $suffix       the suffix of the filename
     * @param string $classname    the name of the class to instancy
     * @param mixed  $constructArg the single argument for the constructor of the class. null = no argument.
     *
     * @return null|object null if the plugin doesn't exists
     */
    public function loadPlugin($name, $type, $suffix, $classname, $constructArg = null)
    {
        if (!$this->includePlugin($name, $type, $suffix, $classname)) {
            return null;
        }
        if (!is_null($constructArg)) {
            return new $classname($constructArg);
        }

        return new $classname();
    }

    /**
     * include the file of a plugin from a plugin directory (any type of plugins).
     *
     * @param string $name      the name of the plugin
     * @param string $type      the type of the plugin
     * @param string $suffix    the suffix of the filename
     * @param string $classname the name of the class to instancy
     *
     * @return bool true if the plugin exists
     */
    public function includePlugin($name, $type, $suffix, $classname)
    {
        if (!class_exists($classname, false)) {
            $path = $this->frameworkRuntimeConfig->getPluginPathByNameAndType($name, $type);
            if ($path === false || !file_exists($path.$name.$suffix)) {
                return false;
            }

            require_once $path.$name.$suffix;
        }

        return true;
    }

    /**
     * Says if the given module $name is enabled.
     *
     * @param string $moduleName
     * @param bool   $includingExternal deprecated
     *
     * @return bool true : module is ok
     */
    public function isModuleEnabled($moduleName, $includingExternal = false)
    {
        if (!$this->frameworkRuntimeConfig) {
            throw new Exception('Configuration is not loaded');
        }

        return $this->frameworkRuntimeConfig->isModuleEnabled($moduleName);
    }

    /**
     * return the real path of an enabled module.
     *
     * @param string $module            a module name
     * @param bool   $includingExternal deprecated
     *
     * @return string the corresponding path
     */
    public function getModulePath($module, $includingExternal = false)
    {
        if (!$this->frameworkRuntimeConfig) {
            throw new Exception('Configuration is not loaded');
        }

        return $this->frameworkRuntimeConfig->getModulePath($module);
    }

    /**
     * set the context to the given module.
     *
     * @param string $module the module name
     */
    public function pushCurrentModule($module)
    {
        array_push($this->_modulesContext, $module);
    }

    /**
     * cancel the current context and set the context to the previous module.
     *
     * @return string the module name of the canceled context
     */
    public function popCurrentModule()
    {
        return array_pop($this->_modulesContext);
    }

    /**
     * get the module name of the current context.
     *
     * @return string name of the current module
     */
    public function getCurrentModule()
    {
        return end($this->_modulesContext);
    }

    /**
     * @return Services
     *
     * @experimental
     */
    public function services()
    {
        return $this->_services;
    }

    /**
     * @experimental
     */
    public function reloadServices()
    {
        $this->_services = new Services();
    }

    /**
     * Declare a directory containing some locales.
     *
     * Content of this directory should be organized like this:
     * `<localecode>/<module>/locales/<localefile>.UTF-8.properties`
     *
     * This method must be called before loading the configuration with `jApp::loadConfig()`
     *
     * @param array|string $dirPath the directory path containing locales that can be used
     */
    public function declareLocalesDir($dirPath)
    {
        if (!is_array($dirPath)) {
            $dirPath = array($dirPath);
        }
        foreach ($dirPath as $path) {
            $p = realpath($path);
            if ($p == '') {
                throw new Exception('Given locales dir '.$path.'does not exists');
            }
            $this->_localesDirPath[] = rtrim($p, '/');
        }
    }

    public function getDeclaredLocalesDir()
    {
        return $this->_localesDirPath;
    }
}
