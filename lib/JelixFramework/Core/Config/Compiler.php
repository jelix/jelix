<?php

/**
 * @author       Laurent Jouanneau
 *
 * @contributor  Thibault Piront (nuKs), Christophe Thiriot, Philippe Schelté
 *
 * @copyright    2006-2026 Laurent Jouanneau
 * @copyright    2007 Thibault Piront, 2008 Christophe Thiriot, 2008 Philippe Schelté
 *
 * @see          https://www.jelix.org
 *
 * @licence      GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

namespace Jelix\Core\Config;

use jApp as App;
use jAppInstance as AppInstance;
use Jelix\Core\Infos\AppInfos;
use Jelix\FileUtilities\Path;
use Jelix\IniFile\Util as IniFileMgr;
use jServer as Server;

/**
 * This class loads all configuration files and generate a single configuration file
 * that is ready to be used by the application.
 *
 * @internal
 */
class Compiler
{
    protected $commonConfig;

    protected $configFileName = '';

    protected $pseudoScriptName = '';

    protected AppInstance $appInstance;

    /**
     * @var \StdClass
     */
    protected $config;

    /**
     * If you are in a CLI script but you want to load a configuration file for a web
     * entry point or vice-versa, you need to indicate the $pseudoScriptName parameter
     * with the name of the entry point.
     *
     * @param string $configFile       the name and path of the config file related to config dir of the app
     * @param string $pseudoScriptName the name of the entry point, relative to the base path,
     *                                 corresponding to the readed configuration. It should start with a leading /.
     */
    public function __construct(AppInstance $appInstance, $configFile = '', $pseudoScriptName = '')
    {
        $this->pseudoScriptName = $pseudoScriptName;
        $this->configFileName = $configFile;
        $this->appInstance = $appInstance;
    }

    /**
     * Read and merge all configuration files.
     *
     * Merge of configuration files is made in this order:
     * - core/defaultconfig.ini.php
     * - app/system/mainconfig.ini.php
     * - app/system/$configFile
     * - var/config/localconfig.ini.php
     * - var/config/$configFile
     *
     * @param string $configFile
     *
     * @return object the object containing content of all configuration files
     *
     * @throws Exception
     */
    protected function readStaticConfigFiles($configFile)
    {
        $appSystemPath = $this->appInstance->appSystemPath;
        $varConfigPath = $this->appInstance->configPath;

        // this is the defaultconfig file of JELIX itself
        $config = \jFile::mergeIniFile(__DIR__.'/defaultconfig.ini.php');

        // read the main configuration of the app
        \jFile::mergeIniFile($this->appInstance->mainConfigFile(), $config);
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
            if (\jFile::mergeIniFile($appSystemPath.$configFile, $config, AppConfig::sectionsToIgnoreForEp) === false) {
                throw new Exception("Syntax error in the configuration file -- {$configFile}", 6);
            }
        }

        // read the local configuration of the app
        if (file_exists($varConfigPath.'localconfig.ini.php')) {
            \jFile::mergeIniFile($varConfigPath.'localconfig.ini.php', $config);
        }

        // read the local configuration of the entry point
        if (file_exists($varConfigPath.$configFile)) {
            if (\jFile::mergeIniFile($varConfigPath.$configFile, $config, AppConfig::sectionsToIgnoreForEp) === false) {
                throw new Exception("Syntax error in the configuration file -- {$configFile}", 6);
            }
        }

        return $config;
    }

    /**
     * Read the ini file given to the constructor. It Merges it with the content of
     * mainconfig.ini.php and other configuration files. It also calculates some options.
     *
     * @return \StdClass an object which contains configuration values
     *
     * @throws Exception
     */
    public function read(FrameworkRuntimeConfig $fmkConfig)
    {
        $this->readStaticConfiguration($fmkConfig);

        return $this->readLiveConfiguration($this->config, false);
    }

    /**
     * Read the ini file given to the constructor, in CLI mode.
     *
     * It Merges it with the content of mainconfig.ini.php and other
     * configuration files. It also calculates some options.
     *
     * @return \StdClass an object which contains configuration values
     *
     * @throws Exception
     */
    public function readForCli(FrameworkRuntimeConfig $fmkConfig)
    {
        $this->readStaticConfiguration($fmkConfig);

        return $this->readLiveConfiguration($this->config, true);
    }

    /**
     * Read the liveconfig.ini php file and merge it with the given configuration object.
     *
     * @param mixed $isCli
     *
     * @return \StdClass
     */
    public function readLiveConfiguration(object $config, $isCli)
    {
        $varConfigPath = $this->appInstance->configPath;
        if (file_exists($varConfigPath.'liveconfig.ini.php')) {
            IniFileMgr::readAndMergeObject($varConfigPath.'liveconfig.ini.php', $config);
        }
        $this->prepareLiveConfig($config, $isCli);

        return $config;
    }

    /**
     * Read all configuration files except the liveconfig.ini.php, and merge them.
     *
     * It also calculates some options.
     *
     * Used to create a cache file, to be merged with liveconfig at runtime.
     *
     * @return \StdClass
     *
     * @throws Exception
     */
    public function readStaticConfiguration(FrameworkRuntimeConfig $fmkConfig)
    {
        $this->config = $this->readStaticConfigFiles($this->configFileName);
        $this->prepareConfig($fmkConfig);

        return $this->config;
    }

    /**
     * fill some static config properties with calculated values.
     */
    protected function prepareConfig(FrameworkRuntimeConfig $fmkConfig)
    {
        $this->checkMiscParameters($this->config);
        $this->checkCoordPluginsPath($this->config, $fmkConfig);
        $this->runConfigCompilerPlugins($this->config, $fmkConfig);
    }

    /**
     * fill some config properties with calculated values available only in a web context.
     *
     * @param object $config
     * @param mixed  $isCli
     *
     * @throws Exception
     */
    protected function prepareLiveConfig($config, $isCli)
    {
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

        $this->getPaths($config->urlengine, $this->pseudoScriptName, $isCli);
    }

    protected function checkMiscParameters($config)
    {
        $config->isWindows = (DIRECTORY_SEPARATOR === '\\');

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

        if ($config->appVersion == '') {
            $config->appVersion = AppInfos::load()->version;
        }
    }

    protected function checkCoordPluginsPath($config, FrameworkRuntimeConfig $fmkConfig)
    {
        $coordplugins = array();
        foreach ($config->coordplugins as $name => $conf) {
            if (strpos($name, '.') !== false) {
                // this is an option for a plugin for the router
                $coordplugins[$name] = $conf;

                continue;
            }
            if (!$fmkConfig->getPluginPathByNameAndType($name, 'coord')) {
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
            $confFile = $this->appInstance->configPath.$conf;
            if (!file_exists($confFile)) {
                $confFile = $this->appInstance->appSystemPath.$conf;
                if (!file_exists($confFile)) {
                    throw new Exception("Error in the configuration. A plugin configuration file doesn't exist -- Configuration file for the coord plugin {$name} doesn't exist: '{$confFile}'", 8);
                }
            }
            // let's get relative path to the app
            $conf = Path::shortestPath($this->appInstance->appPath, $confFile);
        }

        return $conf;
    }

    /**
     * @param \StdClass $config
     */
    protected function runConfigCompilerPlugins($config, FrameworkRuntimeConfig $fmkConfig)
    {
        $pluginsPaths = $fmkConfig->getPluginPathsListByType('configcompiler');

        if (!$pluginsPaths) {
            return;
        }

        // Some plugins needs the list of $fmkConfig->getEnabledModulePathsList()
        // but we then have to break the interface CompilerPluginInterface. We will break it
        // in Jelix 2. Until then, we will pass the list as the old _modulesPathList property
        // @deprecated
        $config->_modulesPathList = $fmkConfig->getEnabledModulePathsList();

        // load plugins
        $plugins = array();
        foreach ($pluginsPaths as $pluginName => $path) {
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

        // deprecated loop
        foreach ($fmkConfig->getEnabledModulePathsList() as $moduleName => $modulePath) {
            $moduleXml = \simplexml_load_file($modulePath.'/module.xml');
            foreach ($plugins as $plugin) {
                $plugin->onModule($config, $moduleName, $modulePath, $moduleXml);
            }
        }

        foreach ($plugins as $plugin) {
            $plugin->atEnd($config);
        }
    }

    /**
     * calculate miscellaneous paths, depending on the server configuration and other information
     * in the given array: script path, script name, documentRoot, etc.
     *
     * @param array  $urlconf          urlengine configuration. scriptNameServerVariable, basePath,
     *                                 and jelixWWWPath should be present
     * @param string $pseudoScriptName
     * @param bool   $isCli
     *
     * @throws Exception
     */
    public function getPaths(&$urlconf, $pseudoScriptName = '', $isCli = false)
    {
        // Retrieve the script path+name.
        // For cli, it will be the path from the directory where we execute the script (given to the php exec).
        // For web, it is the path from the root of the url.

        if ($pseudoScriptName) {
            $urlconf['urlScript'] = $pseudoScriptName;
        } else {
            if ($urlconf['scriptNameServerVariable'] == '') {
                if ($isCli) {
                    $urlconf['scriptNameServerVariable'] = 'SCRIPT_NAME';
                } else {
                    $urlconf['scriptNameServerVariable'] = Server::findServerName('.php');
                }
            }
            $urlconf['urlScript'] = $_SERVER[$urlconf['scriptNameServerVariable']];
        }

        // now we separate the path and the name of the script, and then the basePath
        if ($isCli) {
            $lastslash = strrpos($urlconf['urlScript'], DIRECTORY_SEPARATOR);
            if ($lastslash === false) {
                $urlconf['urlScriptPath'] = ($pseudoScriptName ? $this->appInstance->appPath.'/scripts/' : getcwd().'/');
                $urlconf['urlScriptName'] = $urlconf['urlScript'];
            } else {
                $urlconf['urlScriptPath'] = getcwd().'/'.substr($urlconf['urlScript'], 0, $lastslash).'/';
                $urlconf['urlScriptName'] = substr($urlconf['urlScript'], $lastslash + 1);
            }

            $snp = $urlconf['urlScriptName'];
            $urlconf['urlScript'] = $urlconf['urlScriptPath'].$snp;

            if ($urlconf['basePath'] == '') {
                // we should have a basePath when generating url from a command line
                // script. We cannot guess the url base path so we use a default value
                $urlconf['basePath'] = '/';
            }
        } else {
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
                    // we have to change urlScriptPath. it may contains the base path of the backend server
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

            if ($urlconf['jqueryPath'][0] != '/') {
                $urlconf['jqueryPath'] = $basepath.rtrim($urlconf['jqueryPath'], '/').'/';
            }
            $urlconf['jqueryPath'] = rtrim($urlconf['jqueryPath'], '/').'/';

            $snp = substr($urlconf['urlScript'], strlen($localBasePath));

            if (isset($_SERVER['DOCUMENT_ROOT'])) {
                $urlconf['documentRoot'] = $_SERVER['DOCUMENT_ROOT'];
            } else {
                $urlconf['documentRoot'] = $this->appInstance->wwwPath;
            }

            if ($localBasePath != '/') {
                // if wwwPath ends with the base path, we remove the base path from the wwwPath to have
                // the document root
                $posBP = strpos($this->appInstance->wwwPath, $localBasePath);
                if ($posBP !== false) {
                    $lenWP = strlen($this->appInstance->wwwPath) - strlen($localBasePath);
                    if ($posBP == $lenWP) {
                        $urlconf['documentRoot'] = substr($this->appInstance->wwwPath, 0, $lenWP);
                    }
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
