<?php
/**
 * @author       Laurent Jouanneau
 *
 * @copyright    2006-2023 Laurent Jouanneau
 *
 * @see         http://www.jelix.org
 * @licence      GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

namespace Jelix\Core\Config;

use Jelix\Core\App as App;
use Jelix\IniFile\Util as IniFileMgr;
use Jelix\Core\Server;

/**
 * This class loads all configuration files and generate a single configuration file
 * that is ready to be used by the application
 *
 * @internal
 */
class Compiler
{
    protected $commonConfig;

    protected $configFileName = '';


    protected $pseudoScriptName = '';

    /**
     * @var \StdClass
     */
    protected $config;

    /**
     * @var \Jelix\Core\Infos\ModuleInfos[]
     */
    protected $modulesInfos = array();

    /**
     *
     * If you are in a CLI script but you want to load a configuration file for a web
     * entry point or vice-versa, you need to indicate the $pseudoScriptName parameter
     * with the name of the entry point
     *
     * @param string $configFile       the name and path of the config file related to config dir of the app
     * @param string $pseudoScriptName the name of the entry point, relative to the base path,
     *                                 corresponding to the readed configuration. It should start with a leading /.
     */
    public function __construct($configFile = '', $pseudoScriptName = '')
    {
        $this->pseudoScriptName = $pseudoScriptName;
        $this->configFileName = $configFile;
    }

    /**
     * Read and merge all configuration files.
     *
     * Merge of configuration files are made in this order:
     * - core/defaultconfig.ini.php
     * - app/system/mainconfig.ini.php
     * - app/system/$configFile
     * - var/config/localconfig.ini.php
     * - var/config/$configFile
     * - var/config/liveconfig.ini.php
     *
     * @param string $configFile
     *
     * @throws Exception
     *
     * @return object the object containing content of all configuration files
     */
    protected function readConfigFiles($configFile)
    {
        $appSystemPath = App::appSystemPath();
        $varConfigPath = App::varConfigPath();

        // this is the defaultconfig file of JELIX itself
        $config = IniFileMgr::read(__DIR__.'/defaultconfig.ini.php', true);

        // read the main configuration of the app
        $mcf = App::mainConfigFile();
        if ($mcf) {
            IniFileMgr::readAndMergeObject($mcf, $config);
        }
        $this->commonConfig = clone $config;

        if (!file_exists($appSystemPath.$configFile) && !file_exists($varConfigPath.$configFile)) {
            throw new Exception("Configuration file of the entrypoint is missing -- {$configFile}", 5);
        }

        // read the static configuration specific to the entry point
        if ($configFile == 'mainconfig.ini.php') {
            throw new Exception('Entry point configuration file cannot be mainconfig.ini.php', 5);
        }

        // read the configuration of the entry point
        if (file_exists($appSystemPath.$configFile)) {
            if (IniFileMgr::readAndMergeObject($appSystemPath.$configFile, $config, 0, \Jelix\Core\Config\AppConfig::sectionsToIgnoreForEp) === false) {
                throw new Exception("Syntax error in the configuration file -- {$configFile}", 6);
            }
        }

        // read the local configuration of the app
        if (file_exists($varConfigPath.'localconfig.ini.php')) {
            IniFileMgr::readAndMergeObject($varConfigPath.'localconfig.ini.php', $config);
        }

        // read the local configuration of the entry point
        if (file_exists($varConfigPath.$configFile)) {
            if (IniFileMgr::readAndMergeObject($varConfigPath.$configFile, $config, 0, \Jelix\Core\Config\AppConfig::sectionsToIgnoreForEp) === false) {
                throw new Exception("Syntax error in the configuration file -- {$configFile}", 6);
            }
        }

        if (file_exists($varConfigPath.'liveconfig.ini.php')) {
            IniFileMgr::readAndMergeObject($varConfigPath.'liveconfig.ini.php', $config);
        }

        return $config;
    }

    /**
     * read the ini file given to the constructor. It Merges it with the content of
     * mainconfig.ini.php. It also calculates some options.
     *

     *
     * @param bool $installationMode must be true for the installer, which needs extra information
     *                                and need config values as they are into the files.
     *                                It must be false for runtime to harden some configuration values.
     *
     * @return \StdClass an object which contains configuration values
     *@throws Exception
     *
     */
    public function read($installationMode = false)
    {
        $tempPath = App::tempBasePath();

        if ($tempPath == '/') {
            // if it equals to '/', this is because realpath has returned false in the application.init.php
            // so this is because the path doesn't exist.
            throw new Exception('Application temp directory doesn\'t exist !', 3);
        }

        if (!is_writable($tempPath)) {
            throw new Exception('Application temp base directory is not writable -- ('.$tempPath.')', 4);
        }

        if (!is_writable(App::logPath())) {
            throw new Exception('Application log directory is not writable -- ('.App::logPath().')', 4);
        }
        $this->config = $this->readConfigFiles($this->configFileName);
        $this->prepareConfig($installationMode);

        return $this->config;
    }

    public function getModulesInfos()
    {
        return $this->modulesInfos;
    }

    /**
     * fill some config properties with calculated values.
     *
     * @param bool $installationMode must be true for the installer, which needs extra information
     *                               and need config values as they are into the files.
     *                               It must be false for runtime to harden some configuration values.
     */
    protected function prepareConfig($installationMode)
    {
        $this->checkMiscParameters($this->config);
        $this->getPaths($this->config->urlengine, $this->pseudoScriptName);
        $this->modulesInfos = $this->_loadModulesInfo($this->config, $installationMode);
        $this->_loadPluginsPathList($this->config);
        $this->checkCoordPluginsPath($this->config);
        $this->runConfigCompilerPlugins($this->config, $this->modulesInfos);
    }

    protected function checkMiscParameters($config)
    {
        $config->isWindows = (DIRECTORY_SEPARATOR === '\\');

        if ($config->domainName == '') {
            // as each compiled config is stored in a file based on the domain
            // name/port, we can store the guessed domain name into the configuration
            list($domain, $port) = Server::getDomainPortFromServer();
            if ($domain) {
                $config->domainName = $domain;
                $isHttps = Server::isHttpsFromServer();
                if ($config->forceHTTPPort == '' && !$isHttps && $port != '80') {
                    $config->forceHTTPPort = $port;
                } elseif ($config->forceHTTPSPort == '' && $isHttps && $port != '443') {
                    $config->forceHTTPSPort = $port;
                }
            }
        }

        if (!is_string($config->chmodFile)) {
            $config->chmodFile = (string) $config->chmodFile;
        }
        $config->chmodFile = octdec($config->chmodFile);
        if (!is_string($config->chmodDir)) {
            $config->chmodDir = (string) $config->chmodDir;
        }
        $config->chmodDir = octdec($config->chmodDir);
        if (!is_array($config->error_handling['sensitiveParameters'])) {
            $config->error_handling['sensitiveParameters'] = preg_split('/ *, */', $config->error_handling['sensitiveParameters']);
        }
    }

    protected function checkCoordPluginsPath($config)
    {
        $coordplugins = array();
        foreach ($config->coordplugins as $name => $conf) {
            if (strpos($name, '.') !== false) {
                // this is an option for a plugin for the router
                $coordplugins[$name] = $conf;

                continue;
            }
            if (!isset($config->_pluginsPathList_coord[$name])) {
                throw new Exception("Error in the main configuration. A plugin doesn't exist -- The coord plugin {$name} is unknown.", 7);
            }
            if ($conf) {
                $coordplugins[$name] = $this->getCoordPluginConfValue($name, $conf);
            }
        }
        $config->coordplugins = $coordplugins;
    }

    protected function getCoordPluginConfValue($name, $conf)
    {
        if ($conf != '1' && strlen($conf) > 1) {
            // the configuration value is a filename
            $confFile = App::varConfigPath($conf);
            if (!file_exists($confFile)) {
                $confFile = App::appSystemPath($conf);
                if (!file_exists($confFile)) {
                    throw new Exception("Error in the configuration. A plugin configuration file doesn't exist -- Configuration file for the coord plugin {$name} doesn't exist: '{$confFile}'", 8);
                }
            }
            // let's get relative path to the app
            $conf = \Jelix\FileUtilities\Path::shortestPath(App::appPath(), $confFile);
        }

        return $conf;
    }

    /**
     * @param \StdClass                        $config
     * @param \Jelix\Core\Infos\ModuleInfos[] $modules
     */
    protected function runConfigCompilerPlugins($config, $modules)
    {
        if (!isset($config->_pluginsPathList_configcompiler)) {
            return;
        }

        // load plugins
        $plugins = array();
        foreach ($config->_pluginsPathList_configcompiler as $pluginName => $path) {
            $file = $path.$pluginName.'.configcompiler.php';
            if (!file_exists($file)) {
                continue;
            }

            require_once $file;
            $classname = '\\'.$pluginName.'ConfigCompilerPlugin';
            $plugins[] = new $classname();
        }
        if (!count($plugins)) {
            return;
        }

        // sort plugins by priority
        usort($plugins, function ($a, $b) {
            if ($a->getPriority() == $b->getPriority()) {
                return 0;
            }

            return ($a->getPriority() < $b->getPriority()) ? -1 : 1;
        });

        // run plugins
        foreach ($plugins as $plugin) {
            $plugin->atStart($config);
        }

        foreach ($modules as $moduleName => $module) {
            foreach ($plugins as $plugin) {
                $plugin->onModule($config, $module);
            }
        }

        foreach ($plugins as $plugin) {
            $plugin->atEnd($config);
        }
    }

    /**
     * Find all activated modules and check their status.
     *
     * @param object $config        the config object
     * @param bool   $allModuleInfo may be true for the installer, which needs all informations
     *                              else should be false, these extra informations are
     *                              not needed to run the application
     *
     * @throws Exception
     *
     * @return \Jelix\Core\Infos\ModuleInfos[]
     */
    protected function _loadModulesInfo($config, $installationMode)
    {
        $installerFile = App::varConfigPath('installer.ini.php');

        if (file_exists($installerFile)) {
            $installation = parse_ini_file($installerFile, true, INI_SCANNER_TYPED);
        } else {
            if ($installationMode) {
                $installation = array();
            } else {
                throw new Exception("The application is not installed -- installer.ini.php doesn't exist!\n", 9);
            }
        }

        if (!isset($installation['modules'])) {
            $installation['modules'] = array();
        }

        // _allBasePath is used for:
        // - check time of directories to check if the config cache should be rebuilt
        // FIXME WARMUP: to remove?
        if ($config->compilation['checkCacheFiletime']) {
            $config->_allBasePath = App::getDeclaredModulesDir();
        } else {
            $config->_allBasePath = array();
        }

        $modules = array();
        $list = App::getAllModulesPath();
        $config->modules = [];
        foreach ($list as $k => $path) {
            $module = $this->_readModuleInfo($config, $installationMode, $path, $installation);
            if ($module !== null) {
                $modules[$module->name] = $module;
            }
        }

        return $modules;
    }

    /**
     * @param mixed $config
     * @param mixed $installationMode
     * @param mixed $path
     * @param mixed $installation
     *
     * @return \Jelix\Core\Infos\ModuleInfos
     */
    protected function _readModuleInfo($config, $installationMode, $path, &$installation)
    {
        $moduleInfo = \Jelix\Core\Infos\ModuleInfos::load($path);
        if (!$moduleInfo->exists()) {
            return null;
        }

        $declaredModules = App::getFrameworkInfo()->getModules();

        $f = $moduleInfo->name;
        if (!isset($installation['modules'][$f.'.installed'])) {
            $installation['modules'][$f.'.installed'] = 0;
        }

        if ($f == 'jelix') {
            $config->modules['jelix.enabled'] = true; // the jelix module should always be public
            $moduleStatus = $declaredModules[$f] ?? null;
        } else if (isset($declaredModules[$f])) {
            $moduleStatus = $declaredModules[$f];
            $config->modules[$f.'.enabled'] = $moduleStatus->isEnabled;
            if (!$installation['modules'][$f.'.installed']) {
                // module is not installed.
                // outside installation context, we force the access to 0
                // so the module is unusable until it is installed
                if (!$installationMode) {
                    $config->modules[$f.'.enabled'] = false;
                }
            }
        }
        else {
            $config->modules[$f.'.enabled'] = false;
        }

        if (!$config->modules[$f.'.enabled']) {
            return null;
        }

        if (!isset($installation['modules'][$f.'.dbprofile'])) {
            $config->modules[$f.'.dbprofile'] = 'default';
        } else {
            $config->modules[$f.'.dbprofile'] = $installation['modules'][$f.'.dbprofile'];
        }

        if ($installationMode) {
            if (!isset($installation['modules'][$f.'.version'])) {
                $installation['modules'][$f.'.version'] = '';
            }

            if (!isset($installation['modules'][$f.'.dataversion'])) {
                $installation['modules'][$f.'.dataversion'] = '';
            }

            if (!isset($installation['__modules_data'][$f.'.contexts'])) {
                $installation['__modules_data'][$f.'.contexts'] = '';
            }

            $config->modules[$f.'.version'] = (string) $installation['modules'][$f.'.version'];
            $config->modules[$f.'.dataversion'] = $installation['modules'][$f.'.dataversion'];
            $config->modules[$f.'.installed'] = $installation['modules'][$f.'.installed'];

            if ($moduleStatus) {
                if ($moduleStatus->parameters) {
                    $config->modules[$f.'.installparam'] = $moduleStatus->parameters;
                }
                if ($moduleStatus->skipInstaller) {
                    $config->modules[$f.'.skipinstaller'] = 'skip';
                }
            }

            $config->_allModulesPathList[$f] = $path;
        }

        $config->_modulesPathList[$f] = $path;

        return $moduleInfo;
    }

    /**
     * Analyse plugin paths.
     *
     * @param object $config the config container
     */
    protected function _loadPluginsPathList($config)
    {
        $list = App::getAllPluginsPath();
        foreach ($list as $k => $p) {
            if ($handle = opendir($p)) {
                while (($f = readdir($handle)) !== false) {
                    if ($f[0] != '.' && is_dir($p.$f)) {
                        if ($subdir = opendir($p.$f)) {
                            if ($k != 0 && $config->compilation['checkCacheFiletime']) {
                                $config->_allBasePath[] = $p.$f.'/';
                            }
                            while (($subf = readdir($subdir)) !== false) {
                                if ($subf[0] != '.' && is_dir($p.$f.'/'.$subf)) {
                                    if ($f == 'tpl') {
                                        $prop = '_tplpluginsPathList_'.$subf;
                                        if (!isset($config->{$prop})) {
                                            $config->{$prop} = array();
                                        }
                                        array_unshift($config->{$prop}, $p.$f.'/'.$subf.'/');
                                    } else {
                                        $prop = '_pluginsPathList_'.$f;
                                        $config->{$prop}[$subf] = $p.$f.'/'.$subf.'/';
                                    }
                                }
                            }
                            closedir($subdir);
                        }
                    }
                }
                closedir($handle);
            }
        }
    }

    /**
     * calculate miscellaneous path, depending on the server configuration and other information
     * in the given array : script path, script name, documentRoot ..
     *
     * @param array  $urlconf          urlengine configuration. scriptNameServerVariable, basePath,
     *                                 and jelixWWWPath should be present
     * @param string $pseudoScriptName
     *
     * @throws Exception
     */
    protected function getPaths(&$urlconf, $pseudoScriptName = '')
    {
        // retrieve the script path+name.
        // for cli, it will be the path from the directory were we execute the script (given to the php exec).
        // for web, it is the path from the root of the url

        if ($pseudoScriptName) {
            $urlconf['urlScript'] = $pseudoScriptName;
        } else {
            if ($urlconf['scriptNameServerVariable'] == '') {
                $urlconf['scriptNameServerVariable'] = Server::findServerName('.php');
            }
            $urlconf['urlScript'] = $_SERVER[$urlconf['scriptNameServerVariable']];
        }

        // now we separate the path and the name of the script, and then the basePath
        $lastslash = strrpos($urlconf['urlScript'], '/');
        $urlconf['urlScriptPath'] = substr($urlconf['urlScript'], 0, $lastslash).'/';
        $urlconf['urlScriptName'] = substr($urlconf['urlScript'], $lastslash + 1);

        $basepath = $urlconf['basePath'];
        if ($basepath == '') {
            // for beginners or simple site, we "guess" the base path
            $basepath = $localBasePath = $urlconf['urlScriptPath'];
        } else {
            if ($basepath != '/') {
                if ($basepath[0] != '/') {
                    $basepath = '/'.$basepath;
                }
                if (substr($basepath, -1) != '/') {
                    $basepath .= '/';
                }
            }

            if ($pseudoScriptName) {
                // with pseudoScriptName, we aren't in a true context, we could be in a cli context
                // (the installer), and we want the path like when we are in a web context.
                // $pseudoScriptName is supposed to be relative to the basePath
                $urlconf['urlScriptPath'] = substr($basepath, 0, -1).$urlconf['urlScriptPath'];
                $urlconf['urlScript'] = $urlconf['urlScriptPath'].$urlconf['urlScriptName'];
            }
            $localBasePath = $basepath;
            if ($urlconf['backendBasePath']) {
                $localBasePath = $urlconf['backendBasePath'];
                // we have to change urlScriptPath. it may contain the base path of the backend server
                // we should replace this base path by the basePath of the frontend server
                if (strpos($urlconf['urlScriptPath'], $urlconf['backendBasePath']) === 0) {
                    $urlconf['urlScriptPath'] = $basepath.substr($urlconf['urlScriptPath'], strlen($urlconf['backendBasePath']));
                } else {
                    $urlconf['urlScriptPath'] = $basepath.substr($urlconf['urlScriptPath'], 1);
                }
            } elseif (strpos($urlconf['urlScriptPath'], $basepath) !== 0) {
                throw new Exception('Error in main configuration on basePath -- basePath ('.$basepath.') in config file doesn\'t correspond to current base path. You should setup it to '.$urlconf['urlScriptPath']);
            }
        }
        $urlconf['basePath'] = $basepath;

        if ($urlconf['jelixWWWPath'][0] != '/') {
            $urlconf['jelixWWWPath'] = $basepath.$urlconf['jelixWWWPath'];
        }
        $urlconf['jelixWWWPath'] = rtrim($urlconf['jelixWWWPath'], '/').'/';

        $snp = substr($urlconf['urlScript'], strlen($localBasePath));

        if (isset($_SERVER['DOCUMENT_ROOT'])) {
            $urlconf['documentRoot'] = $_SERVER['DOCUMENT_ROOT'];
        } else {
            $urlconf['documentRoot'] = App::wwwPath();
        }

        if ($localBasePath != '/') {
            // if wwwPath ends with the base path, we remove the base path from the wwwPath to have
            // the document root
            $posBP = strpos(App::wwwPath(), $localBasePath);
            if ($posBP !== false) {
                $lenWP = strlen(App::wwwPath()) - strlen($localBasePath);
                if ($posBP == $lenWP) {
                    $urlconf['documentRoot'] = substr(App::wwwPath(), 0, $lenWP);
                }
            }
        }

        $pos = strrpos($snp, '.php');
        if ($pos !== false) {
            $snp = substr($snp, 0, $pos);
        }

        $urlconf['urlScriptId'] = $snp;
        $urlconf['urlScriptIdenc'] = rawurlencode($snp);

        // fix compatibility with previous name of notFoundAct
        if (isset($urlconf['notfoundAct'])) {
            $urlconf['notFoundAct'] = $urlconf['notfoundAct'];
        }
    }
}
