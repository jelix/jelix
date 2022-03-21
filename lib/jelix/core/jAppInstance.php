<?php

/**
 * @author     Laurent Jouanneau
 * @copyright  2015-2022 Laurent Jouanneau
 *
 * @see       http://jelix.org
 * @licence    http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */
class jAppInstance
{
    public $tempBasePath = '';

    public $appPath = '';

    public $varPath = '';

    public $logPath = '';

    public $configPath = '';

    public $wwwPath = '';

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

    protected $_allModulesPath;

    protected $_allPluginsPath;

    protected $_modulesContext = array();

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
    }

    public function setPaths(
        $appPath,
        $wwwPath = null,
        $varPath = null,
        $logPath = null,
        $configPath = null,
        $scriptPath = null
    ) {
        $this->appPath = $appPath;
        $this->wwwPath = (is_null($wwwPath) ? $appPath.'www/' : $wwwPath);
        $this->varPath = (is_null($varPath) ? $appPath.'var/' : $varPath);
        $this->logPath = (is_null($logPath) ? $this->varPath.'log/' : $logPath);
        $this->configPath = (is_null($configPath) ? $this->varPath.'config/' : $configPath);
        $this->scriptPath = (is_null($scriptPath) ? $appPath.'scripts/' : $scriptPath);
    }

    public function __destruct()
    {
        $this->unregisterAutoload();
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
            $this->configAutoloader = new jConfigAutoloader($this->config);
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
    }

    public function setConfig($config)
    {
        $this->unregisterAutoload();
        $this->config = $config;
        $this->registerAutoload();
    }

    public function onRestoringAsContext()
    {
        $this->registerAutoload();
    }

    /**
     * return the version of the application containing into a VERSION file
     * stored at the root of the application.
     *
     * It doesn't read the version from project.xml or composer.json.
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
     * @param array|string $basePath the directory path containing modules that can be used
     * @param null|string[] $modules list of module name to declare, from the directory. By default: all sub-directories (null).
     *                               parameter used only if $basePath is a string
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

    public function getDeclaredModulesDir()
    {
        return array_keys($this->_modulesDirPath);
    }

    /**
     * declare a module.
     *
     * This method must be called before loading the configuration with `jApp::loadConfig()`
     *
     * @param array|string $path       the path of the module directory
     * @param mixed        $modulePath
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

    /**
     * Read all modules path declared into the configuration
     *
     * Method reserved to the configuration compiler.
     *
     * @param object $config
     */
    public function declareModulesFromConfig($config)
    {
        // -- read all *.path into [modules]
        if (property_exists($config, 'modules')) {
            foreach ($config->modules as $key => $path) {
                if (!preg_match('/^([a-zA-Z_0-9]+)\\.path$/', $key, $m) || $path == '') {
                    continue;
                }
                $p = jFile::parseJelixPath($path);
                if (!file_exists($p)) {
                    throw new Exception('Error in the configuration file -- The path, ' . $path . ', given in the configuration, doesn\'t exist', 10);
                }
                if (!is_dir($p)) {
                    throw new Exception('Error in the configuration file -- The path, ' . $path . ', given in the configuration, is not a directory', 10);
                }
                $this->_modulesPath[] = rtrim($p, '/');
            }
        }
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
        return $this->config->_modulesPathList;
    }

    /**
     * returns all modules path, even those are not used by the application.
     *
     * @return string[] keys are module name, values are paths
     */
    public function getAllModulesPath()
    {
        if ($this->_allModulesPath === null) {
            $this->_allModulesPath = array();

            if ($this->config) {
                $this->declareModulesFromConfig($this->config);
            }

            foreach ($this->_modulesPath as $modulePath) {
                $this->_allModulesPath[basename($modulePath)] = $modulePath.DIRECTORY_SEPARATOR;
            }

            foreach ($this->_modulesDirPath as $basePath => $names) {
                $path = $basePath.DIRECTORY_SEPARATOR;
                if (is_array($names)) {
                    foreach ($names as $name) {
                        $this->_allModulesPath[$name] = $path.$name.DIRECTORY_SEPARATOR;
                    }
                } elseif ($names == '*' || $names === null) {
                    if ($handle = opendir($path)) {
                        while (($name = readdir($handle)) !== false) {
                            if ($name[0] != '.' && is_dir($path.$name)) {
                                $this->_allModulesPath[$name] = $path.$name.DIRECTORY_SEPARATOR;
                            }
                        }
                        closedir($handle);
                    }
                }
            }
        }

        return $this->_allModulesPath;
    }

    /**
     * return all paths of directories containing plugins, even those which are
     * in disabled modules.
     *
     * @return string[]
     */
    public function getAllPluginsPath()
    {
        if ($this->_allPluginsPath === null) {
            $this->_allPluginsPath = array_map(function ($path) {
                return rtrim($path, '/\\').DIRECTORY_SEPARATOR;
            }, $this->_pluginsDirPath);

            foreach ($this->getAllModulesPath() as $name => $path) {
                $p = $path.'plugins'.DIRECTORY_SEPARATOR;
                if (file_exists($p) &&
                    is_dir($p) &&
                    !in_array($p, $this->_allPluginsPath)) {
                    $this->_allPluginsPath[] = $p;
                }
            }

            $bundled = realpath(__DIR__.'/../plugins/').DIRECTORY_SEPARATOR;
            if (file_exists($bundled) && !in_array($bundled, $this->_allPluginsPath)) {
                array_unshift($this->_allPluginsPath, $bundled);
            }
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
            $optname = '_pluginsPathList_'.$type;
            if (!isset($this->config->{$optname})) {
                return false;
            }
            $opt = &$this->config->{$optname};
            if (!isset($opt[$name])
                || !file_exists($opt[$name].$name.$suffix)) {
                return false;
            }
            require_once $opt[$name].$name.$suffix;
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
        if (!$this->config) {
            throw new Exception('Configuration is not loaded');
        }

        return isset($this->config->_modulesPathList[$moduleName]);
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
        if (!$this->config) {
            throw new Exception('Configuration is not loaded');
        }

        if (!isset($this->config->_modulesPathList[$module])) {
            throw new Exception('getModulePath : invalid module name');
        }

        return $this->config->_modulesPathList[$module];
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
     * @return string the obsolet module name
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
}
