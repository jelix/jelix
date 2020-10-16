<?php
/**
 * @package  Jelix\Legacy
 *
 * @author   Laurent Jouanneau
 * @contributor
 *
 * @copyright 2014 Laurent Jouanneau
 *
 * @see     http://www.jelix.org
 * @licence  MIT
 */

/**
 * dummy class for compatibility.
 *
 * @see \Jelix\Core\App
 * @deprecated
 */
class jApp
{
    public static function initPaths(
        $appPath,
        $wwwPath = null,
        $varPath = null,
        $logPath = null,
        $configPath = null,
        $scriptPath = null
                                 ) {
        \Jelix\Core\App::initPaths($appPath, $wwwPath, $varPath, $logPath, $configPath, $scriptPath);
    }

    public static function version()
    {
        return \Jelix\Core\App::version();
    }

    public static function isInit()
    {
        return \Jelix\Core\App::isInit();
    }

    public static function appPath($file = '')
    {
        return \Jelix\Core\App::appPath($file);
    }

    public static function varPath($file = '')
    {
        return \Jelix\Core\App::varPath($file);
    }

    public static function logPath($file = '')
    {
        return \Jelix\Core\App::logPath($file);
    }

    public static function appConfigPath($file = '')
    {
        return \Jelix\Core\App::appSystemPath($file);
    }

    public static function appSystemPath($file = '')
    {
        return \Jelix\Core\App::appSystemPath($file);
    }

    public static function varConfigPath($file = '')
    {
        return \Jelix\Core\App::varConfigPath($file);
    }

    public static function wwwPath($file = '')
    {
        return \Jelix\Core\App::wwwPath($file);
    }

    public static function scriptsPath($file = '')
    {
        return \Jelix\Core\App::scriptsPath($file);
    }

    public static function tempPath($file = '')
    {
        return \Jelix\Core\App::tempPath($file);
    }

    public static function tempBasePath()
    {
        return \Jelix\Core\App::tempBasePath();
    }

    public static function setTempBasePath($path)
    {
        \Jelix\Core\App::setTempBasePath($path);
    }

    public static function setEnv($env)
    {
        \Jelix\Core\App::setEnv($env);
    }

    public static function urlJelixWWWPath()
    {
        return \Jelix\Core\App::urlJelixWWWPath();
    }

    public static function urlBasePath()
    {
        return \Jelix\Core\App::urlBasePath();
    }

    public static function config()
    {
        return \Jelix\Core\App::config();
    }

    public static function setConfig($config)
    {
        \Jelix\Core\App::setConfig($config);
    }

    public static function loadConfig($configFile, $enableErrorHandler = true)
    {
        \Jelix\Core\App::loadConfig($configFile, $enableErrorHandler);
    }

    public static function mainConfigFile()
    {
        return \Jelix\Core\App::mainConfigFile();
    }

    public static function router()
    {
        return \Jelix\Core\App::router();
    }

    /**
     * @param \Jelix\Routing\Router $router set new current router
     */
    public static function setRouter($router)
    {
        \Jelix\Core\App::setRouter($router);
    }

    public static function coord()
    {
        //trigger_error("App::coord() is deprecated, use App::router() instead", E_USER_DEPRECATED);
        return \Jelix\Core\App::router();
    }

    public static function setCoord($router)
    {
        //trigger_error("App::setCoord() is deprecated, use App::setRouter() instead", E_USER_DEPRECATED);
        \Jelix\Core\App::setRouter($router);
    }

    public static function saveContext()
    {
        \Jelix\Core\App::saveContext();
    }

    public static function restoreContext()
    {
        \Jelix\Core\App::restoreContext();
    }

    public static function declareModulesDir($basePath, $modules = null)
    {
        \Jelix\Core\App::declareModulesDir($basePath, $modules);
    }

    public static function getDeclaredModulesDir()
    {
        return \Jelix\Core\App::getDeclaredModulesDir();
    }

    public static function declareModule($modulePath)
    {
        \Jelix\Core\App::declareModule($modulePath);
    }

    public static function clearModulesPluginsPath()
    {
        \Jelix\Core\App::clearModulesPluginsPath();
    }

    public static function declarePluginsDir($basePath)
    {
        \Jelix\Core\App::declarePluginsDir($basePath);
    }

    public static function getAllModulesPath()
    {
        return \Jelix\Core\App::getAllModulesPath();
    }

    public static function getAllPluginsPath()
    {
        return \Jelix\Core\App::getAllPluginsPath();
    }

    public static function loadPlugin($name, $type, $suffix, $classname, $args = null)
    {
        return \Jelix\Core\App::loadPlugin($name, $type, $suffix, $classname, $args);
    }

    public static function includePlugin($name, $type, $suffix, $classname)
    {
        return \Jelix\Core\App::includePlugin($name, $type, $suffix, $classname);
    }

    public static function isModuleEnabled($moduleName, $includingExternal = false)
    {
        return \Jelix\Core\App::isModuleEnabled($moduleName, $includingExternal);
    }

    public static function getModulePath($module, $includingExternal = false)
    {
        return \Jelix\Core\App::getModulePath($module, $includingExternal);
    }

    public static function pushCurrentModule($module)
    {
        \Jelix\Core\App::pushCurrentModule($module);
    }

    public static function popCurrentModule()
    {
        return \Jelix\Core\App::popCurrentModule();
    }

    public static function getCurrentModule()
    {
        return \Jelix\Core\App::getCurrentModule();
    }
}
