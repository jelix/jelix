<?php

/**
 * @author     Laurent Jouanneau
 * @contributor  Olivier Demah
 *
 * @copyright  2011-2015 Laurent Jouanneau, 2012 Olivier Demah
 *
 * @link       http://jelix.org
 * @licence    http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */

/**
 *
 */
class jApp
{
    protected static $_currentApp = null;

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
    public static function initPaths($appPath,
                                     $wwwPath = null,
                                     $varPath = null,
                                     $logPath = null,
                                     $configPath = null,
                                     $scriptPath = null
                                     ) {
        if (self::$_currentApp) {
            self::$_currentApp->setPaths($appPath, $wwwPath, $varPath, $logPath, $configPath, $scriptPath);
        } else {
            self::$_currentApp = new jAppInstance($appPath, $wwwPath, $varPath, $logPath, $configPath, $scriptPath);
        }
        self::$_mainConfigFile = null;
    }

    /**
     * return the version of the application containing into a VERSION file
     * It doesn't read the version from project.xml or composer.json.
     *
     * @return string
     */
    public static function version()
    {
        return self::$_currentApp->version();
    }

    /**
     * indicate if path have been set.
     *
     * @return bool true if it is ok
     */
    public static function isInit()
    {
        return (self::$_currentApp !== null);
    }

    public static function app()
    {
        return self::$_currentApp;
    }

    public static function appPath($file = '')
    {
        return self::$_currentApp->appPath.$file;
    }

    public static function varPath($file = '')
    {
        return self::$_currentApp->varPath.$file;
    }

    public static function logPath($file = '')
    {
        return self::$_currentApp->logPath.$file;
    }

    public static function configPath($file = '')
    {
        return self::$_currentApp->configPath.$file;
    }

    public static function wwwPath($file = '')
    {
        return self::$_currentApp->wwwPath.$file;
    }

    public static function scriptsPath($file = '')
    {
        return self::$_currentApp->scriptPath.$file;
    }

    public static function tempPath($file = '')
    {
        return (self::$_currentApp->tempBasePath).(self::$_currentApp->env).$file;
    }

    public static function tempBasePath()
    {
        return self::$_currentApp->tempBasePath;
    }

    public static function setTempBasePath($path)
    {
        self::$_currentApp->tempBasePath = $path;
    }

    public static function setEnv($env)
    {
        if (substr($env, -1) != '/') {
            $env .= '/';
        }
        self::$_currentApp->env = $env;
    }

    public static function urlBasePath()
    {
        if (!self::$_currentApp->config || !isset(self::$_currentApp->config->urlengine['basePath'])) {
            return;
        }

        return self::$_currentApp->config->urlengine['basePath'];
    }

    /**
     * @return object object containing all configuration options of the application
     */
    public static function config()
    {
        return self::$_currentApp->config;
    }

    public static function setConfig($config)
    {
        self::$_currentApp->setConfig($config);
    }

    /**
     * Load the configuration from the given file.
     *
     * Call it after initPaths
     *
     * @param string|object $configFile         name of the ini file to configure the framework or a configuration object
     * @param bool          $enableErrorHandler enable the error handler of jelix.
     *                                          keep it to true, unless you have something to debug
     *                                          and really have to use the default handler or an other handler
     */
    public static function loadConfig($configFile, $enableErrorHandler = true)
    {
        if ($enableErrorHandler) {
            jBasicErrorHandler::register();
        }
        if (is_object($configFile)) {
            self::setConfig($configFile);
        } else {
            self::setConfig(jConfig::load($configFile));
        }
        self::$_currentApp->config->enableErrorHandler = $enableErrorHandler;
    }

    protected static $_mainConfigFile = null;

    /**
     * Main config file path.
     */
    public static function mainConfigFile()
    {
        if (self::$_mainConfigFile) {
            return self::$_mainConfigFile;
        }

        $configFileName = self::$_currentApp->configPath.'mainconfig.ini.php';
        if (!file_exists($configFileName)) {
            // support of legacy configuration file
            // TODO: support of defaultconfig.ini.php should be dropped in version > 1.6
            $configFileName = self::$_currentApp->configPath.'defaultconfig.ini.php';
            trigger_error('the config file defaultconfig.ini.php is deprecated and will be removed in the next major release', E_USER_DEPRECATED);
        }
        self::$_mainConfigFile = $configFileName;

        return $configFileName;
    }

    public static function coord()
    {
        return self::$_currentApp->coord;
    }

    public static function setCoord($coord)
    {
        self::$_currentApp->coord = $coord;
    }

    protected static $contextBackup = array();

    /**
     * save all path and others variables relatives to the application, so you can
     * temporary change the context to an other application.
     */
    public static function saveContext()
    {
        self::$contextBackup[] = self::$_currentApp;
        self::$_currentApp = clone self::$_currentApp;
    }

    /**
     * restore the previous context of the application.
     */
    public static function restoreContext()
    {
        if (!count(self::$contextBackup)) {
            return;
        }
        self::$_currentApp = null;
        self::$_currentApp = array_pop(self::$contextBackup);
        self::$_currentApp->onRestoringAsContext();
        self::$_mainConfigFile = null;
    }

    /**
     * Declare a list of modules.
     *
     * @param string|array $basePath the directory path containing modules that can be used
     * @param null|string[]  list of module name to declare, from the directory. By default: all sub-directories (null).
     *                               parameter used only if $basePath is a string
     */
    public static function declareModulesDir($basePath, $modules = null)
    {
        self::$_currentApp->declareModulesDir($basePath, $modules);
    }

    public static function getDeclaredModulesDir()
    {
        return self::$_currentApp->getDeclaredModulesDir();
    }

    /**
     * declare a module.
     *
     * @param string $path the path of the module directory
     */
    public static function declareModule($modulePath)
    {
        self::$_currentApp->declareModule($modulePath);
    }

    public static function clearModulesPluginsPath()
    {
        self::$_currentApp->clearModulesPluginsPath();
    }

    /**
     * Declare a directory containing some plugins. Note that it does not
     * need to declare 'plugins/' inside modules, as there are declared automatically
     * when you declare modules.
     *
     * @param string|string[] $basePath the directory path containing plugins that can be used
     */
    public static function declarePluginsDir($basePath)
    {
        self::$_currentApp->declarePluginsDir($basePath);
    }

    /**
     * returns all modules path, even those are not used by the application.
     *
     * @return string[] keys are module name, values are paths
     */
    public static function getAllModulesPath()
    {
        return self::$_currentApp->getAllModulesPath();
    }

    /**
     * return all paths of directories containing plugins, even those which are
     * in disabled modules.
     *
     * @return string[]
     */
    public static function getAllPluginsPath()
    {
        return self::$_currentApp->getAllPluginsPath();
    }

    /**
     * load a plugin from a plugin directory (any type of plugins).
     *
     * @param string $name      the name of the plugin
     * @param string $type      the type of the plugin
     * @param string $suffix    the suffix of the filename
     * @param string $classname the name of the class to instancy
     * @param mixed  $args      the argument for the constructor of the class. null = no argument.
     *
     * @return null|object null if the plugin doesn't exists
     */
    public static function loadPlugin($name, $type, $suffix, $classname, $args = null)
    {
        return self::$_currentApp->loadPlugin($name, $type, $suffix, $classname, $args);
    }

    /**
     * Says if the given module $name is enabled.
     *
     * @param string $moduleName
     * @param bool   $includingExternal true if we want to know if the module
     *                                  is also an external module, e.g. in an other entry point
     *
     * @return bool true : module is ok
     */
    public static function isModuleEnabled($moduleName, $includingExternal = false)
    {
        return self::$_currentApp->isModuleEnabled($moduleName, $includingExternal);
    }

    /**
     * return the real path of an enabled module.
     *
     * @param string $module            a module name
     * @param bool   $includingExternal true if we want the path of a module
     *                                  enabled in an other entry point.
     *
     * @return string the corresponding path
     */
    public static function getModulePath($module, $includingExternal = false)
    {
        return self::$_currentApp->getModulePath($module, $includingExternal);
    }

    /**
     * set the context to the given module.
     *
     * @param string $module the module name
     */
    public static function pushCurrentModule($module)
    {
        self::$_currentApp->pushCurrentModule($module);
    }

    /**
     * cancel the current context and set the context to the previous module.
     *
     * @return string the obsolet module name
     */
    public static function popCurrentModule()
    {
        return self::$_currentApp->popCurrentModule();
    }

    /**
     * get the module name of the current context.
     *
     * @return string name of the current module
     */
    public static function getCurrentModule()
    {
        return self::$_currentApp->getCurrentModule();
    }
}
