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
namespace Jelix\Core;

/**
 *
 * @method public static function setConfig($config)
 * @method public static function declareModulesDir($basePath, $modules = null)
 * @method public static function getDeclaredModulesDir()
 * @method public static function declareModule($modulePath)
 * @method public static function clearModulesPluginsPath()
 * @method public static function declarePluginsDir($basePath)
 * @method public static function getAllModulesPath()
 * @method public static function getAllPluginsPath()
 * @method public static function loadPlugin($name, $type, $suffix, $classname, $args = null)
 * @method public static function includePlugin($name, $type, $suffix, $classname)
 * @method public static function isModuleEnabled($moduleName, $includingExternal = false)
 * @method public static function getModulePath($module, $includingExternal = false)
 * @method public static function pushCurrentModule($module)
 * @method public static function popCurrentModule()
 * @method public static function getCurrentModule()
 */

class App {

    /**
     * @var AppInstance
     */
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
     * @param string $configPath var config directory
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
            self::$_currentApp = new AppInstance($appPath, $wwwPath, $varPath, $logPath, $configPath, $scriptPath);
        }
        self::$_mainConfigFile = null;
    }

    /**
     * return the version of the application containing into a VERSION file
     * It doesn't read the version from project.xml or jelix-app.json.
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

    public static function appConfigPath($file = '')
    {
        return self::$_currentApp->appPath.'app/config/'.$file;
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
        trigger_error("jApp::varConfigPath() is deprecated. use jApp::varConfigPath() instead", E_USER_DEPRECATED);
        return self::$_currentApp->configPath.$file;
    }

    public static function varConfigPath($file = '')
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
        if (self::$_currentApp == null) {
            throw new \Exception("App not initialized");
        }
        if (substr($env, -1) != '/') {
            $env .= '/';
        }
        self::$_currentApp->env = $env;
    }

    public static function urlBasePath()
    {
        if (!self::$_currentApp->config || !isset(self::$_currentApp->config->urlengine['basePath'])) {
            return "";
        }
        return self::$_currentApp->config->urlengine['basePath'];
    }

    public static function urlJelixWWWPath()
    {
        if (!self::$_currentApp->config || !isset(self::$_currentApp->config->urlengine['jelixWWWPath'])) {
            return "";
        }

        return self::$_currentApp->config->urlengine['jelixWWWPath'];
    }


    /**
     * @return object object containing all configuration options of the application
     */
    public static function config()
    {
        return self::$_currentApp->config;
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
            \jBasicErrorHandler::register();
        }
        if (is_object($configFile)) {
            self::setConfig($configFile);
        } else {
            self::setConfig(Config::load($configFile));
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

        $configFileName = self::$_currentApp->appPath.'app/config/mainconfig.ini.php';
        if (!file_exists($configFileName)) {
            if (jServer::isCLI() && strpos($_SERVER['SCRIPT_FILENAME'], 'installer.php') !== false) {
                throw new \Exception("Don't find the app/config/mainconfig.ini.php file. You must change your installer.php script. See migration documentation.");
            }
            throw new \Exception("Don't find the app/config/mainconfig.ini.php file. Low-level upgrade is needed. Launch the installer.");
        }
        self::$_mainConfigFile = $configFileName;

        return $configFileName;
    }

    /**
     * @return \Jelix\Routing\Router current router
     */
    public static function router() {
        return self::$_currentApp->router;
    }

    /**
     * @param \Jelix\Routing\Router $router set new current router
     */
    public static function setRouter($router) {
        self::$_currentApp->router = $router;
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
     * allows to call some methods on the current instance as static methods
     * on App
     */
    public static function __callStatic($name, $arguments)
    {
        if (self::$_currentApp == null) {
            throw new \Exception("App not initialized");
        }
        return call_user_func_array(array(self::$_currentApp, $name), $arguments);
    }
}
