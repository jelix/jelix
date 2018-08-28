<?php
/**
* @package      jelix
* @subpackage   core
* @author       Laurent Jouanneau
* @contributor  Thibault Piront (nuKs), Christophe Thiriot, Philippe Schelté
* @copyright    2006-2012 Laurent Jouanneau
* @copyright    2007 Thibault Piront, 2008 Christophe Thiriot, 2008 Philippe Schelté
* @link         http://www.jelix.org
* @licence      GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

/**
 * jConfigCompiler merge two ini file in a single array and store it in a temporary file
 * This is a static class
 * @package  jelix
 * @subpackage core
 * @static
 */
class jConfigCompiler {

    private function __construct (){ }

    /**
     * read the given ini file, for the current entry point, or for the entrypoint given
     * in $pseudoScriptName. Merge it with the content of other config files
     * It also calculates some options.
     * If you are in a CLI script but you want to load a configuration file for a web entry point
     * or vice-versa, you need to indicate the $pseudoScriptName parameter with the name of the entry point
     *
     * Merge of configuration files are made in this order:
     * - core/defaultconfig.ini.php
     * - app/config/mainconfig.ini.php
     * - app/config/$configFile
     * - var/config/localconfig.ini.php
     * - var/config/$configFile
     * - var/config/liveconfig.ini.php
     *
     * @param string $configFile the config file name
     * @param boolean $allModuleInfo may be true for the installer, which needs all informations
     *                               else should be false, these extra informations are
     *                               not needed to run the application
     * @param boolean $isCli indicate if the configuration to read is for a CLI script or no
     * @param string $pseudoScriptName the name of the entry point, relative to the base path,
     *              corresponding to the readed configuration
     * @return object an object which contains configuration values
     * @throws Exception
     */
    static public function read($configFile, $allModuleInfo = false, $isCli = false, $pseudoScriptName=''){
        $tempPath = jApp::tempBasePath();
        $appConfigPath = jApp::appConfigPath();
        $varConfigPath = jApp::varConfigPath();

        if($tempPath=='/'){
            // if it equals to '/', this is because realpath has returned false in the application.init.php
            // so this is because the path doesn't exist.
            throw new Exception('Application temp directory doesn\'t exist !', 3);
        }

        if(!is_writable($tempPath)){
            throw new Exception('Application temp base directory is not writable -- ('.$tempPath.')', 4);
        }

        if(!is_writable(jApp::logPath())) {
            throw new Exception('Application log directory is not writable -- ('.jApp::logPath().')', 4);
        }

        // this is the defaultconfig file of JELIX itself
        $config = jelix_read_ini(__DIR__.'/defaultconfig.ini.php');

        // read the main configuration of the app
        @jelix_read_ini(jApp::mainConfigFile(), $config);

        if(!file_exists($appConfigPath.$configFile) && !file_exists($varConfigPath.$configFile)) {
            throw new Exception("Configuration file of the entrypoint is missing -- $configFile", 5);
        }

        // read the static configuration specific to the entry point
        if ($configFile == 'mainconfig.ini.php') {
            throw new Exception("Entry point configuration file cannot be mainconfig.ini.php", 5);
        }

        // read the configuration of the entry point
        if (file_exists($appConfigPath.$configFile)) {
            if( false === @jelix_read_ini($appConfigPath.$configFile, $config, jConfig::sectionsToIgnoreForEp)) {
                throw new Exception("Syntax error in the configuration file -- $configFile", 6);
            }
        }

        // read the local configuration of the app
        if (file_exists($varConfigPath.'localconfig.ini.php')) {
            @jelix_read_ini($varConfigPath.'localconfig.ini.php', $config);
        }

        // read the local configuration of the entry point
        if (file_exists($varConfigPath.$configFile)) {
            if( false === @jelix_read_ini($varConfigPath.$configFile, $config, jConfig::sectionsToIgnoreForEp)) {
                throw new Exception("Syntax error in the configuration file -- $configFile", 6);
            }
        }

        if (file_exists($varConfigPath.'liveconfig.ini.php')) {
            @jelix_read_ini($varConfigPath.'liveconfig.ini.php', $config);
        }

        self::prepareConfig($config, $allModuleInfo, $isCli, $pseudoScriptName);
        return $config;
    }

    /**
     * Identical to read(), but also stores the result in a temporary file
     * @param string $configFile the config file name
     * @param boolean $isCli
     * @param string $pseudoScriptName
     * @return object an object which contains configuration values
     * @throws Exception
     */
    static public function readAndCache($configFile, $isCli = null, $pseudoScriptName = '') {

        if ($isCli === null)
            $isCli = jServer::isCLI();

        $config = self::read($configFile, false, $isCli, $pseudoScriptName);
        $tempPath = jApp::tempPath();
        jFile::createDir($tempPath, $config->chmodDir);
        $filename = $tempPath.str_replace('/','~',$configFile);

        if(BYTECODE_CACHE_EXISTS){
            $filename .= '.conf.php';
            if ($f = @fopen($filename, 'wb')) {
                fwrite($f, '<?php $config = '.var_export(get_object_vars($config),true).";\n?>");
                fclose($f);
                chmod($filename, $config->chmodFile);
            } else {
                throw new Exception('Error while writing configuration cache file -- '.$filename);
            }
        }else{
            \Jelix\IniFile\Util::write(get_object_vars($config), $filename.'.resultini.php', ";<?php die('');?>\n", $config->chmodFile);
        }
        return $config;
    }

    /**
     * fill some config properties with calculated values
     * @param object $config  the config object
     * @param boolean $allModuleInfo may be true for the installer, which needs all informations
     *                               else should be false, these extra informations are
     *                               not needed to run the application
     * @param boolean $isCli  indicate if the configuration to read is for a CLI script or no
     * @param string $pseudoScriptName the name of the entry point, relative to the base path,
     *              corresponding to the readed configuration
     */
    static protected function prepareConfig($config, $allModuleInfo, $isCli, $pseudoScriptName){
        self::checkMiscParameters($config);
        self::getPaths($config->urlengine, $pseudoScriptName, $isCli);
        self::_loadModuleInfo($config, $allModuleInfo);
        self::_loadPluginsPathList($config);
        self::checkCoordPluginsPath($config);
        self::runConfigCompilerPlugins($config);
    }

    static protected function checkMiscParameters($config) {
        $config->isWindows = (DIRECTORY_SEPARATOR === '\\');

        if ($config->domainName == "" && isset($_SERVER['SERVER_NAME'])) {
            $config->domainName = $_SERVER['SERVER_NAME'];
        }

        $config->chmodFile = octdec($config->chmodFile);
        $config->chmodDir = octdec($config->chmodDir);
        if (!is_array($config->error_handling['sensitiveParameters'])) {
            $config->error_handling['sensitiveParameters'] = preg_split('/ *, */', $config->error_handling['sensitiveParameters']);
        }

    }

    static protected function checkCoordPluginsPath($config) {
        $coordplugins = array();
        foreach ($config->coordplugins as $name=>$conf) {
            if (strpos($name, '.') !== false)  {
                // this is an option for a plugin for the router
                $coordplugins[$name] = $conf;
                continue;
            }
            if (!isset($config->_pluginsPathList_coord[$name])) {
                throw new Exception("Error in the main configuration. A plugin doesn't exist -- The coord plugin $name is unknown.", 7);
            }
            if ($conf) {
                $coordplugins[$name] = self::getCoordPluginConfValue($name, $conf);
            }
        }
        $config->coordplugins = $coordplugins;
    }

    static protected function getCoordPluginConfValue($name, $conf) {
        if ($conf != '1' && strlen($conf) > 1) {
            // the configuration value is a filename
            $confFile = jApp::appConfigPath($conf);
            if (!file_exists($confFile)) {
                $confFile = jApp::varConfigPath($conf);
                if (!file_exists($confFile)) {
                    throw new Exception("Error in the configuration. A plugin configuration file doesn't exist -- Configuration file for the coord plugin $name doesn't exist: '$confFile'", 8);
                }
            }
            // let's get relative path to the app
            $conf = \Jelix\FileUtilities\Path::shortestPath(jApp::appPath(), $confFile);
        }
        return $conf;
    }

    static protected function runConfigCompilerPlugins($config) {
        if (!isset($config->_pluginsPathList_configcompiler)) {
            return;
        }

        // load plugins
        $plugins = array();
        foreach($config->_pluginsPathList_configcompiler as $pluginName => $path) {
            $file = $path.$pluginName.'.configcompiler.php';
            if (!file_exists($file) ){
                continue;
            }

            require_once($file);
            $classname = $pluginName.'ConfigCompilerPlugin';
            $plugins[] = new $classname();
        }
        if (!count($plugins)) {
            return;
        }

        // sort plugins by priority
        usort($plugins, function($a, $b){ return $a->getPriority() < $b->getPriority();});

        // run plugins
        foreach($plugins as $plugin) {
            $plugin->atStart($config);
        }

        foreach($config->_modulesPathList as $moduleName=>$modulePath) {
            $moduleXml = simplexml_load_file($modulePath.'module.xml');
            foreach($plugins as $plugin) {
                $plugin->onModule($config, $moduleName, $modulePath, $moduleXml);
            }
        }

        foreach($plugins as $plugin) {
            $plugin->atEnd($config);
        }
    }

    /**
     * Analyse and check the "lib:" and "app:" path.
     * @param object $config the config object
     * @param boolean $allModuleInfo may be true for the installer, which needs all informations
     *                               else should be false, these extra informations are
     *                               not needed to run the application
     * @throws Exception
     */
    static protected function _loadModuleInfo($config, $allModuleInfo) {

        $installerFile = jApp::varConfigPath('installer.ini.php');

        if ($config->disableInstallers) {
            $installation = array ();
        }
        else if (file_exists($installerFile)) {
            $installation = parse_ini_file($installerFile, true);
        }
        else {
            if ($allModuleInfo)
                $installation = array ();
            else {
                throw new Exception("The application is not installed -- installer.ini.php doesn't exist!\n", 9);
            }
        }

        if (!isset($installation['modules'])) {
            $installation['modules'] = array();
        }

        if ($config->compilation['checkCacheFiletime']) {
            $config->_allBasePath = jApp::getDeclaredModulesDir();
        } else {
            $config->_allBasePath = array();
        }
            
        $list = jApp::getAllModulesPath();

        foreach($list as $f => $path) {

            if ($config->disableInstallers) {
                $installation['modules'][$f.'.installed'] = 1;
            } else if (!isset($installation['modules'][$f.'.installed'])) {
                $installation['modules'][$f.'.installed'] = 0;
            }

            if ($f == 'jelix') {
                $config->modules['jelix.enabled'] = true; // the jelix module should always be public
            }
            else {
                if ($config->enableAllModules) {
                    if ($config->disableInstallers
                        || $installation['modules'][$f.'.installed']
                        || $allModuleInfo) {
                        $config->modules[$f.'.enabled'] = true;
                    } else {
                        $config->modules[$f.'.enabled'] = false;
                    }
                }
                else if (!isset($config->modules[$f.'.enabled'])) {
                    // no given enabling status in mainconfig and ep config
                    $config->modules[$f.'.enabled'] = false;
                }
                else if (!$installation['modules'][$f.'.installed']) {
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

                $config->modules[$f.'.version'] = $installation['modules'][$f.'.version'];
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
     * Analyse plugin paths
     * @param object $config the config container
     */
    static protected function _loadPluginsPathList($config) {
        $list = jApp::getAllPluginsPath();
        foreach ($list as $k=>$p) {
            if ($handle = opendir($p)) {
                while (false !== ($f = readdir($handle))) {
                    if ($f[0] != '.' && is_dir($p.$f)) {
                        if ($subdir = opendir($p.$f)) {
                            if($k!=0 && $config->compilation['checkCacheFiletime']) {
                               $config->_allBasePath[] = $p.$f.'/';
                            }
                            while (false !== ($subf = readdir($subdir))) {
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
     * calculate miscelaneous path, depending of the server configuration and other informations
     * in the given array : script path, script name, documentRoot ..
     * @param array $urlconf urlengine configuration. scriptNameServerVariable, basePath,
     * jelixWWWPath and jqueryPath should be present
     * @param string $pseudoScriptName
     * @param bool $isCli
     * @throws Exception
     */
    static public function getPaths(&$urlconf, $pseudoScriptName ='', $isCli = false) {
        // retrieve the script path+name.
        // for cli, it will be the path from the directory were we execute the script (given to the php exec).
        // for web, it is the path from the root of the url

        if ($pseudoScriptName) {
            $urlconf['urlScript'] = $pseudoScriptName;
        }
        else {
            if($urlconf['scriptNameServerVariable'] == '') {
                $urlconf['scriptNameServerVariable'] = self::findServerName('.php', $isCli);
            }
            $urlconf['urlScript'] = $_SERVER[$urlconf['scriptNameServerVariable']];
        }

        // now we separate the path and the name of the script, and then the basePath
        if ($isCli) {
            $lastslash = strrpos ($urlconf['urlScript'], DIRECTORY_SEPARATOR);
            if ($lastslash === false) {
                $urlconf['urlScriptPath'] = ($pseudoScriptName? jApp::appPath('/scripts/'): getcwd().'/');
                $urlconf['urlScriptName'] = $urlconf['urlScript'];
            }
            else {
                $urlconf['urlScriptPath'] = getcwd().'/'.substr ($urlconf['urlScript'], 0, $lastslash ).'/';
                $urlconf['urlScriptName'] = substr ($urlconf['urlScript'], $lastslash+1);
            }
            $basepath = $urlconf['urlScriptPath'];
            $snp = $urlconf['urlScriptName'];
            $urlconf['urlScript'] = $basepath.$snp;
        }
        else {
            $lastslash = strrpos ($urlconf['urlScript'], '/');
            $urlconf['urlScriptPath'] = substr ($urlconf['urlScript'], 0, $lastslash ).'/';
            $urlconf['urlScriptName'] = substr ($urlconf['urlScript'], $lastslash+1);

            $basepath = $urlconf['basePath'];
            if ($basepath == '') {
                // for beginners or simple site, we "guess" the base path
                $basepath = $localBasePath = $urlconf['urlScriptPath'];
            }
            else {
                if ($basepath != '/') {
                    if($basepath[0] != '/') $basepath='/'.$basepath;
                    if(substr($basepath,-1) != '/') $basepath.='/';
                }

                if ($pseudoScriptName) {
                    // with pseudoScriptName, we aren't in a true context, we could be in a cli context
                    // (the installer), and we want the path like when we are in a web context.
                    // $pseudoScriptName is supposed to be relative to the basePath
                    $urlconf['urlScriptPath'] = substr($basepath,0,-1).$urlconf['urlScriptPath'];
                    $urlconf['urlScript'] = $urlconf['urlScriptPath'].$urlconf['urlScriptName'];
                }
                $localBasePath = $basepath;
                if ($urlconf['backendBasePath']) {
                    $localBasePath = $urlconf['backendBasePath'];
                    // we have to change urlScriptPath. it may contains the base path of the backend server
                    // we should replace this base path by the basePath of the frontend server
                    if (strpos($urlconf['urlScriptPath'], $urlconf['backendBasePath']) === 0) {
                        $urlconf['urlScriptPath'] = $basepath.substr( $urlconf['urlScriptPath'], strlen($urlconf['backendBasePath']));
                    }
                    else  {
                        $urlconf['urlScriptPath'] = $basepath.substr($urlconf['urlScriptPath'], 1);
                    }

                }elseif(strpos($urlconf['urlScriptPath'], $basepath) !== 0) {
                    throw new Exception('Error in main configuration on basePath -- basePath ('.$basepath.') in config file doesn\'t correspond to current base path. You should setup it to '.$urlconf['urlScriptPath']);
                }
            }
            $urlconf['basePath'] = $basepath;

            if ($urlconf['jelixWWWPath'][0] != '/') {
                $urlconf['jelixWWWPath'] = $basepath.$urlconf['jelixWWWPath'];
            }
            if ($urlconf['jqueryPath'][0] != '/') {
                $urlconf['jqueryPath'] = $basepath.$urlconf['jqueryPath'];
            }
            $snp = substr($urlconf['urlScript'], strlen($localBasePath));

            if ($localBasePath == '/')
                $urlconf['documentRoot'] = jApp::wwwPath();
            else if(strpos(jApp::wwwPath(), $localBasePath) === false) {
                if (isset($_SERVER['DOCUMENT_ROOT']))
                    $urlconf['documentRoot'] = $_SERVER['DOCUMENT_ROOT'];
                else
                    $urlconf['documentRoot'] = jApp::wwwPath();
            }
            else
                $urlconf['documentRoot'] = substr(jApp::wwwPath(), 0, - (strlen($localBasePath)));
        }

        $pos = strrpos($snp, '.php');
        if ($pos !== false) {
            $snp = substr($snp,0,$pos);
        }

        $urlconf['urlScriptId'] = $snp;
        $urlconf['urlScriptIdenc'] = rawurlencode($snp);
    }

    static public function findServerName($ext = '.php', $isCli = false) {
        $extlen = strlen($ext);

        if(strrpos($_SERVER['SCRIPT_NAME'], $ext) === (strlen($_SERVER['SCRIPT_NAME']) - $extlen)
           || $isCli) {
            return 'SCRIPT_NAME';
        }else if (isset($_SERVER['REDIRECT_URL'])
                  && strrpos( $_SERVER['REDIRECT_URL'], $ext) === (strlen( $_SERVER['REDIRECT_URL']) -$extlen)) {
            return 'REDIRECT_URL';
        }else if (isset($_SERVER['ORIG_SCRIPT_NAME'])
                  && strrpos( $_SERVER['ORIG_SCRIPT_NAME'], $ext) === (strlen( $_SERVER['ORIG_SCRIPT_NAME']) - $extlen)) {
            return 'ORIG_SCRIPT_NAME';
        }
        throw new Exception('Error in main configuration on URL engine parameters -- In config file the parameter urlengine:scriptNameServerVariable is empty and Jelix doesn\'t find
            the variable in $_SERVER which contains the script name. You must see phpinfo and setup this parameter in your config file.', 11);
    }


}
