<?php
/**
 * @package      jelix
 * @subpackage   core
 *
 * @author       Laurent Jouanneau
 * @contributor  Thibault Piront (nuKs), Christophe Thiriot, Philippe Schelté
 *
 * @copyright    2006-2022 Laurent Jouanneau
 * @copyright    2007 Thibault Piront, 2008 Christophe Thiriot, 2008 Philippe Schelté
 *
 * @see         http://www.jelix.org
 * @licence      GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

/**
 * jConfigCompiler merge two ini file in a single array and store it in a temporary file
 * This is a static class.
 *
 * @package  jelix
 * @subpackage core
 * @static
 */
class jConfigCompiler
{
    private function __construct()
    {
    }

    /**
     * read the given ini file, for the current entry point, or for the entrypoint given
     * in $pseudoScriptName. Merge it with the content of other config files
     * It also calculates some options.
     * If you are in a CLI script but you want to load a configuration file for a web entry point
     * or vice-versa, you need to indicate the $pseudoScriptName parameter with the name of the entry point.
     *
     * Merge of configuration files are made in this order:
     * - core/defaultconfig.ini.php
     * - app/system/mainconfig.ini.php
     * - app/system/$configFile
     * - var/config/localconfig.ini.php
     * - var/config/$configFile
     * - var/config/liveconfig.ini.php
     *
     * @param string $configFile       the config file name
     * @param bool   $allModuleInfo    may be true for the installer, which needs all informations
     *                                 else should be false, these extra informations are
     *                                 not needed to run the application
     * @param bool   $isCli            indicate if the configuration to read is for a CLI script or no
     * @param string $pseudoScriptName the name of the entry point, relative to the base path,
     *                                 corresponding to the readed configuration
     *
     * @throws Exception
     *
     * @return object an object which contains configuration values
     */
    public static function read($configFile, $allModuleInfo = false, $isCli = false, $pseudoScriptName = '')
    {
        $tempPath = jApp::tempBasePath();
        $appSystemPath = jApp::appSystemPath();
        $varConfigPath = jApp::varConfigPath();

        if ($tempPath == '/') {
            // if it equals to '/', this is because realpath has returned false in the application.init.php
            // so this is because the path doesn't exist.
            throw new Exception('Application temp directory doesn\'t exist !', 3);
        }

        if (!is_writable($tempPath)) {
            throw new Exception('Application temp base directory is not writable -- ('.$tempPath.')', 4);
        }

        if (!is_writable(jApp::logPath())) {
            throw new Exception('Application log directory is not writable -- ('.jApp::logPath().')', 4);
        }

        // this is the defaultconfig file of JELIX itself
        $config = jelix_read_ini(__DIR__.'/defaultconfig.ini.php');

        // read the main configuration of the app
        @jelix_read_ini(jApp::mainConfigFile(), $config);

        if (!file_exists($appSystemPath.$configFile) && !file_exists($varConfigPath.$configFile)) {
            throw new Exception("Configuration file of the entrypoint is missing -- {$configFile}", 5);
        }

        // read the static configuration specific to the entry point
        if ($configFile == 'mainconfig.ini.php') {
            throw new Exception('Entry point configuration file cannot be mainconfig.ini.php', 5);
        }

        // read the configuration of the entry point
        if (file_exists($appSystemPath.$configFile)) {
            if (@jelix_read_ini($appSystemPath.$configFile, $config, jConfig::sectionsToIgnoreForEp) === false) {
                throw new Exception("Syntax error in the configuration file -- {$configFile}", 6);
            }
        }

        // read the local configuration of the app
        if (file_exists($varConfigPath.'localconfig.ini.php')) {
            @jelix_read_ini($varConfigPath.'localconfig.ini.php', $config);
        }

        // read the local configuration of the entry point
        if (file_exists($varConfigPath.$configFile)) {
            if (@jelix_read_ini($varConfigPath.$configFile, $config, jConfig::sectionsToIgnoreForEp) === false) {
                throw new Exception("Syntax error in the configuration file -- {$configFile}", 6);
            }
        }

        if (file_exists($varConfigPath.'liveconfig.ini.php')) {
            @jelix_read_ini($varConfigPath.'liveconfig.ini.php', $config);
        }

        self::prepareConfig($config, $allModuleInfo, $isCli, $pseudoScriptName);

        return $config;
    }

    /**
     * Identical to read(), but also stores the result in a temporary file.
     *
     * @param string $configFile       the config file name
     * @param bool   $isCli
     * @param string $pseudoScriptName
     *
     * @throws Exception
     *
     * @return object an object which contains configuration values
     */
    public static function readAndCache($configFile, $isCli = null, $pseudoScriptName = '')
    {
        if ($isCli === null) {
            $isCli = jServer::isCLI();
        }

        $config = self::read($configFile, false, $isCli, $pseudoScriptName);
        jFile::createDir(jApp::tempPath(), $config->chmodDir);
        $filename = self::getCacheFilename($configFile);

        if (BYTECODE_CACHE_EXISTS) {
            if ($f = @fopen($filename, 'wb')) {
                fwrite($f, '<?php $config = '.var_export(get_object_vars($config), true).";\n?>");
                fclose($f);
                chmod($filename, $config->chmodFile);
            } else {
                throw new Exception('Error while writing configuration cache file -- '.$filename);
            }
        } else {
            \Jelix\IniFile\Util::write(get_object_vars($config), $filename.'.resultini.php', ";<?php die('');?>\n", $config->chmodFile);
        }

        return $config;
    }

    /**
     * return the path of file where to store the cache of the configuration.
     *
     * @param string $configFile the name of the configuration file of the entry
     *                           point into var/config/
     *
     * @return string the full path of the cache
     *
     * @since 1.6.26
     */
    public static function getCacheFilename($configFile)
    {
        $filename = jApp::tempPath().str_replace('/', '~', $configFile);
        list($domain, $port) = jServer::getDomainPortFromServer();
        if ($domain) {
            $filename .= '.'.$domain.'-'.$port;
        }
        if (BYTECODE_CACHE_EXISTS) {
            $filename .= '.conf.php';
        } else {
            $filename .= '.resultini.php';
        }

        return $filename;
    }

    /**
     * fill some config properties with calculated values.
     *
     * @param object $config           the config object
     * @param bool   $allModuleInfo    may be true for the installer, which needs all informations
     *                                 else should be false, these extra informations are
     *                                 not needed to run the application
     * @param bool   $isCli            indicate if the configuration to read is for a CLI script or no
     * @param string $pseudoScriptName the name of the entry point, relative to the base path,
     *                                 corresponding to the readed configuration
     */
    protected static function prepareConfig($config, $allModuleInfo, $isCli, $pseudoScriptName)
    {
        self::checkMiscParameters($config);
        self::getPaths($config->urlengine, $pseudoScriptName, $isCli);
        self::_loadModuleInfo($config, $allModuleInfo);
        self::_loadPluginsPathList($config);
        self::checkCoordPluginsPath($config);
        self::runConfigCompilerPlugins($config);
    }

    protected static function checkMiscParameters($config)
    {
        $config->isWindows = (DIRECTORY_SEPARATOR === '\\');

        if ($config->domainName == '') {
            // as each compiled config is stored in a file based on the domain
            // name/port, we can store the guessed domain name into the configuration
            list($domain, $port) = jServer::getDomainPortFromServer();
            if ($domain) {
                $config->domainName = $domain;
                $isHttps = jServer::isHttpsFromServer();
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

    protected static function checkCoordPluginsPath($config)
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
                $coordplugins[$name] = self::getCoordPluginConfValue($name, $conf);
            }
        }
        $config->coordplugins = $coordplugins;
    }

    protected static function getCoordPluginConfValue($name, $conf)
    {
        if ($conf != '1' && strlen($conf) > 1) {
            // the configuration value is a filename
            $confFile = jApp::appSystemPath($conf);
            if (!file_exists($confFile)) {
                $confFile = jApp::varConfigPath($conf);
                if (!file_exists($confFile)) {
                    throw new Exception("Error in the configuration. A plugin configuration file doesn't exist -- Configuration file for the coord plugin {$name} doesn't exist: '{$confFile}'", 8);
                }
            }
            // let's get relative path to the app
            $conf = \Jelix\FileUtilities\Path::shortestPath(jApp::appPath(), $confFile);
        }

        return $conf;
    }

    protected static function runConfigCompilerPlugins($config)
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
            $classname = $pluginName.'ConfigCompilerPlugin';
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

        foreach ($config->_modulesPathList as $moduleName => $modulePath) {
            $moduleXml = simplexml_load_file($modulePath.'module.xml');
            foreach ($plugins as $plugin) {
                $plugin->onModule($config, $moduleName, $modulePath, $moduleXml);
            }
        }

        foreach ($plugins as $plugin) {
            $plugin->atEnd($config);
        }
    }

    /**
     * Analyse and check the "lib:" and "app:" path.
     *
     * @param object $config        the config object
     * @param bool   $allModuleInfo may be true for the installer, which needs all informations
     *                              else should be false, these extra informations are
     *                              not needed to run the application
     *
     * @throws Exception
     */
    protected static function _loadModuleInfo($config, $allModuleInfo)
    {
        $installerFile = jApp::varConfigPath('installer.ini.php');

        if ($config->disableInstallers) {
            $installation = array();
        } elseif (file_exists($installerFile)) {
            $installation = parse_ini_file($installerFile, true, INI_SCANNER_TYPED);
        } else {
            if ($allModuleInfo) {
                $installation = array();
            } else {
                throw new Exception("The application is not installed -- installer.ini.php doesn't exist!\n", 9);
            }
        }

        if (!isset($installation['modules'])) {
            $installation['modules'] = array();
        }

        jApp::declareModulesFromConfig($config);

        if ($config->compilation['checkCacheFiletime']) {
            $config->_allBasePath = jApp::getDeclaredModulesDir();
        } else {
            $config->_allBasePath = array();
        }

        $list = jApp::getAllModulesPath();

        foreach ($list as $f => $path) {
            if ($config->disableInstallers) {
                $installation['modules'][$f.'.installed'] = 1;
            } elseif (!isset($installation['modules'][$f.'.installed'])) {
                $installation['modules'][$f.'.installed'] = 0;
            }

            if ($f == 'jelix') {
                $config->modules['jelix.enabled'] = true; // the jelix module should always be public
            } else {
                if ($config->enableAllModules) {
                    if ($config->disableInstallers
                        || $installation['modules'][$f.'.installed']
                        || $allModuleInfo) {
                        $config->modules[$f.'.enabled'] = true;
                    } else {
                        $config->modules[$f.'.enabled'] = false;
                    }
                } elseif (!isset($config->modules[$f.'.enabled'])) {
                    // no given enabling status in mainconfig and ep config
                    $config->modules[$f.'.enabled'] = false;
                } elseif (!$installation['modules'][$f.'.installed']) {
                    // module is not installed.
                    // outside installation mode, we force the disabling
                    // so we are sure the module is unusable until it is installed
                    if (!$allModuleInfo) {
                        $config->modules[$f.'.enabled'] = false;
                    }
                }
            }

            if (!isset($installation['modules'][$f.'.dbprofile'])) {
                $config->modules[$f.'.dbprofile'] = 'default';
            } else {
                $config->modules[$f.'.dbprofile'] = $installation['modules'][$f.'.dbprofile'];
            }

            if ($allModuleInfo) {
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

                $config->_allModulesPathList[$f] = $path;
            }

            if ($config->modules[$f.'.enabled']) {
                $config->_modulesPathList[$f] = $path;
            }
        }
    }

    /**
     * Analyse plugin paths.
     *
     * @param object $config the config container
     */
    protected static function _loadPluginsPathList($config)
    {
        $list = jApp::getAllPluginsPath();
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
     * calculate miscellaneous path, depending of the server configuration and other information
     * in the given array : script path, script name, documentRoot ..
     *
     * @param array  $urlconf          urlengine configuration. scriptNameServerVariable, basePath,
     *                                 jelixWWWPath and jqueryPath should be present
     * @param string $pseudoScriptName
     * @param bool   $isCli
     *
     * @throws Exception
     */
    public static function getPaths(&$urlconf, $pseudoScriptName = '', $isCli = false)
    {
        // retrieve the script path+name.
        // for cli, it will be the path from the directory were we execute the script (given to the php exec).
        // for web, it is the path from the root of the url

        if ($pseudoScriptName) {
            $urlconf['urlScript'] = $pseudoScriptName;
        } else {
            if ($urlconf['scriptNameServerVariable'] == '') {
                $urlconf['scriptNameServerVariable'] = self::findServerName('.php', $isCli);
            }
            $urlconf['urlScript'] = $_SERVER[$urlconf['scriptNameServerVariable']];
        }

        // now we separate the path and the name of the script, and then the basePath
        if ($isCli) {
            $lastslash = strrpos($urlconf['urlScript'], DIRECTORY_SEPARATOR);
            if ($lastslash === false) {
                $urlconf['urlScriptPath'] = ($pseudoScriptName ? jApp::appPath('/scripts/') : getcwd().'/');
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
                $urlconf['documentRoot'] = jApp::wwwPath();
            }

            if ($localBasePath != '/') {
                // if wwwPath ends with the base path, we remove the base path from the wwwPath to have
                // the document root
                $posBP = strpos(jApp::wwwPath(), $localBasePath);
                if ($posBP !== false) {
                    $lenWP = strlen(jApp::wwwPath()) - strlen($localBasePath);
                    if ($posBP == $lenWP) {
                        $urlconf['documentRoot'] = substr(jApp::wwwPath(), 0, $lenWP);
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

    public static function findServerName($ext = '.php', $isCli = false)
    {
        $extlen = strlen($ext);

        if (strrpos($_SERVER['SCRIPT_NAME'], $ext) === (strlen($_SERVER['SCRIPT_NAME']) - $extlen)
           || $isCli) {
            return 'SCRIPT_NAME';
        }
        if (isset($_SERVER['REDIRECT_URL'])
                  && strrpos($_SERVER['REDIRECT_URL'], $ext) === (strlen($_SERVER['REDIRECT_URL']) - $extlen)) {
            return 'REDIRECT_URL';
        }
        if (isset($_SERVER['ORIG_SCRIPT_NAME'])
                  && strrpos($_SERVER['ORIG_SCRIPT_NAME'], $ext) === (strlen($_SERVER['ORIG_SCRIPT_NAME']) - $extlen)) {
            return 'ORIG_SCRIPT_NAME';
        }

        throw new Exception('Error in main configuration on URL engine parameters -- In config file the parameter urlengine:scriptNameServerVariable is empty and Jelix doesn\'t find
            the variable in $_SERVER which contains the script name. You must see phpinfo and setup this parameter in your config file.', 11);
    }
}
