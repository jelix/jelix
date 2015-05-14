<?php
/**
* @author       Laurent Jouanneau
* @contributor  Thibault Piront (nuKs), Christophe Thiriot, Philippe Schelté
* @copyright    2006-2015 Laurent Jouanneau
* @copyright    2007 Thibault Piront, 2008 Christophe Thiriot, 2008 Philippe Schelté
* @link         http://www.jelix.org
* @licence      GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

namespace Jelix\Core\Config;
use Jelix\Core\App as App;
use Jelix\IniFile\Manager as IniFileMgr;

/**
 * This class merges two ini files in a single array and store it in a temporary file
 * @static
 */
class Compiler {

    protected $commonConfig;

    protected $configFileName = '';

    protected $isCli = false;

    protected $pseudoScriptName = '';

    /**
     * @var StdClass
     */
    protected $config;

    /**
     * @var \Jelix\Core\Infos\ModuleInfos[]
     */
    protected $modulesInfos = array();

    /**
     * @param string $configFile  the name and path of the config file related to config dir of the app
     * @param string $pseudoScriptName the name of the entry point, relative to the base path,
     *              corresponding to the readed configuration. It should start with a leading /
     *              for non cli script.
     * @param boolean $isCli  indicate if the configuration to read is for a CLI script or no
     */
    function __construct ($configFile = '', $pseudoScriptName = '', $isCli = null){
        $this->isCli = ($isCli !== null? $isCli: \jServer::isCLI());
        $this->pseudoScriptName = $pseudoScriptName;
        $this->configFileName = $configFile;
    }

    protected function readConfigFiles($configFile, $additionalOptions) {

        $configPath = App::configPath();

        // this is the defaultconfig file of JELIX itself
        $config = IniFileMgr::read(__DIR__.'/defaultconfig.ini.php', true);

        // read the main configuration of the app
        $mcf = App::mainConfigFile();
        if ($mcf) {
            IniFileMgr::readAndMergeObject($mcf, $config);
        }
        $this->commonConfig = clone $config;

        // read the local configuration of the app
        if (file_exists($configPath.'localconfig.ini.php')) {
            IniFileMgr::readAndMergeObject($configPath.'localconfig.ini.php', $config);
        }

        // read the configuration specific to the entry point
        if ($configFile != '' && $configPath.$configFile != $mcf) {
            if (!file_exists($configPath.$configFile))
                throw new Exception("Configuration file is missing -- $configFile", 5);
            if ( false === IniFileMgr::readAndMergeObject($configPath.$configFile, $config))
                throw new Exception("Syntax error in the configuration file -- $configFile", 6);
        }
        if ($additionalOptions) {
            IniFileMgr::mergeIniObjectContents($config, $additionalOptions);
        }
        return $config;
    }

    /**
     * read the ini file given to the constructor. It Merges it with the content of
     * mainconfig.ini.php. It also calculates some options.
     * If you are in a CLI script but you want to load a configuration file for a web
     * entry point or vice-versa, you need to indicate the $pseudoScriptName parameter
     * with the name of the entry point
     * @param boolean $allModuleInfo may be true for the installer, which needs all informations
     *                               else should be false, these extra informations are
     *                               not needed to run the application
     * @param array  $additionalOptions  some options to add to the configuration
     *
     * @return StdClass an object which contains configuration values
     */
    public function read($allModuleInfo = false, $additionalOptions= null){

        $tempPath = App::tempBasePath();

        if ($tempPath=='/') {
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
        $this->config = $this->readConfigFiles($this->configFileName, $additionalOptions);
        $this->prepareConfig($allModuleInfo);
        return $this->config;
    }

    /**
     * Identical to read(), but also stores the result in a temporary file
     * @return object an object which contains configuration values
     */
    public function readAndCache() {

        $config = $this->read(false);
        $tempPath = App::tempPath();
        \jFile::createDir($tempPath, $config->chmodDir);
        $filename = $tempPath.str_replace('/', '~', $this->configFileName);

        if (BYTECODE_CACHE_EXISTS) {
            $filename .= '.conf.php';
            if ($f = @fopen($filename, 'wb')) {
                fwrite($f, '<?php $config = '.var_export(get_object_vars($config),true).";\n?>");
                fclose($f);
                chmod($filename, $config->chmodFile);
            }
            else {
                throw new Exception('Error while writing configuration cache file -- '.$filename);
            }
        }
        else {
            IniFileMgr::write(get_object_vars($config), $filename.'.resultini.php', ";<?php die('');?>\n", '', $config->chmodFile);
        }
        return $config;
    }

    public function getModulesInfos() {
        return $this->modulesInfos;
    }

    /**
     * fill some config properties with calculated values
     * @param boolean $allModuleInfo may be true for the installer, which needs all informations
     *                               else should be false, these extra informations are
     *                               not needed to run the application
     */
    protected function prepareConfig($allModuleInfo){

        $this->checkMiscParameters($this->config);
        $this->getPaths($this->config->urlengine, $this->pseudoScriptName, $this->isCli);
        $this->modulesInfos = $this->_loadModulesInfo($this->config, $allModuleInfo);
        $this->_loadPluginsPathList($this->config);
        $this->checkCoordPluginsPath($this->config);
        $this->runConfigCompilerPlugins($this->config, $this->modulesInfos);
    }

    protected function checkMiscParameters($config) {
        $config->isWindows = (DIRECTORY_SEPARATOR === '\\');
        if (trim( $config->startAction) == '') {
            $config->startAction = ':';
        }

        if ($config->domainName == "" && isset($_SERVER['SERVER_NAME'])) {
            $config->domainName = $_SERVER['SERVER_NAME'];
        }

        if ($config->urlengine['engine'] == 'simple') {
            trigger_error("The 'simple' url engine is deprecated. use 'basic_significant' or 'significant' url engine", E_USER_NOTICE);
        }

        $config->chmodFile = octdec($config->chmodFile);
        $config->chmodDir = octdec($config->chmodDir);
    }

    protected function checkCoordPluginsPath($config) {
        $coordplugins = array();
        foreach ($config->coordplugins as $name=>$conf) {
            if (strpos($name, '.') !== false) {
                $coordplugins[$name] = $conf;
                continue;
            }
            if (!isset($config->_pluginsPathList_coord[$name])) {
                throw new Exception("Error in the main configuration. A plugin doesn't exist -- The coord plugin $name is unknown.", 7);
            }
            if ($conf) {
                if ($conf != '1' && !file_exists(App::configPath($conf))) {
                    throw new Exception("Error in the main configuration. A plugin configuration file doesn't exist -- Configuration file for the coord plugin $name doesn't exist: '$conf'", 8);
                }
                $coordplugins[$name] = $conf;
            }
        }
        $config->coordplugins = $coordplugins;
    }

    /**
     * @param StdClass $config
     * @param \Jelix\Core\Infos\ModuleInfos[] $modules
     */
    protected function runConfigCompilerPlugins($config, $modules) {
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

            require_once($file);
            $classname = '\\'.$pluginName.'ConfigCompilerPlugin';
            $plugins[] = new $classname();
        }
        if (!count($plugins)) {
            return;
        }

        // sort plugins by priority
        usort($plugins, function($a, $b){
                            return $a->getPriority() < $b->getPriority();
                        });

        // run plugins
        foreach($plugins as $plugin)
            $plugin->atStart($config);

        foreach ($modules as $moduleName=>$module) {
            foreach ($plugins as $plugin) {
                $plugin->onModule($config, $module);
            }
        }

        foreach($plugins as $plugin) {
            $plugin->atEnd($config);
        }
    }

    /**
     * Find all activated modules and check their status
     * @param object $config  the config object
     * @param boolean $allModuleInfo may be true for the installer, which needs all informations
     *                               else should be false, these extra informations are
     *                               not needed to run the application
     * @return \Jelix\Core\Infos\ModuleInfos[]
     */
    protected function _loadModulesInfo($config, $allModuleInfo) {

        $installerFile = App::configPath('installer.ini.php');

        if ($config->disableInstallers) {
            $installation = array ();
        }
        else if (!file_exists($installerFile)) {
            if ($allModuleInfo) {
                $installation = array ();
            } else {
                throw new Exception("The application is not installed -- installer.ini.php doesn't exist!\n", 9);
            }
        }
        else {
            $installation = parse_ini_file($installerFile,true);
        }
        $section = $config->urlengine['urlScriptId'];

        if (!isset($installation[$section])) {
            $installation[$section] = array();
        }

        if ($config->compilation['checkCacheFiletime']) {
            $config->_allBasePath = App::getDeclaredModulesDir();
        } else {
            $config->_allBasePath = array();
        }

        $modules = array();
        $list = App::getAllModulesPath();
        foreach($list as $k=>$path){
            $module = $this->_readModuleInfo($config, $allModuleInfo, $path, $installation, $section);
            if ($module !== null) {
                $modules[$module->name] = $module;
            }
        }
        return $modules;
    }

    /**
     * @return \Jelix\Core\Infos\ModuleInfos
     */
    protected function _readModuleInfo ($config, $allModuleInfo, $path, &$installation, $section) {

        $moduleInfo = new \Jelix\Core\Infos\ModuleInfos($path);
        if (!$moduleInfo->exists()) {
            return null;
        }
        $f = $moduleInfo->name;
        if ($config->disableInstallers) {
            $installation[$section][$f.'.installed'] = 1;
        }
        else if (!isset($installation[$section][$f.'.installed'])) {
            $installation[$section][$f.'.installed'] = 0;
        }

        if ($f == 'jelix') {
            $config->modules['jelix.access'] = 2; // the jelix module should always be public
        }
        else {
            if ($config->enableAllModules) {
                if ($config->disableInstallers
                    || $installation[$section][$f.'.installed']
                    || $allModuleInfo) {
                    $config->modules[$f.'.access'] = 2;
                } else {
                    $config->modules[$f.'.access'] = 0;
                }
            }
            else if (!isset($config->modules[$f.'.access'])) {
                // no given access in defaultconfig and ep config
                $config->modules[$f.'.access'] = 0;
            }
            else if ($config->modules[$f.'.access'] == 0) {
                // we want to activate the module if it is not activated
                // for the entry point, but is declared activated
                // in the default config file. In this case, it means
                // that it is activated for an other entry point,
                // and then we want the possibility to retrieve its
                // urls, at least
                if (isset($this->commonConfig->modules[$f.'.access'])
                    && $this->commonConfig->modules[$f.'.access'] > 0) {
                    $config->modules[$f.'.access'] = 3;
                }
            }
            else if (!$installation[$section][$f.'.installed']) {
                // module is not installed.
                // outside installation mode, we force the access to 0
                // so the module is unusable until it is installed
                if (!$allModuleInfo) {
                    $config->modules[$f.'.access'] = 0;
                }
            }
        }

        if (!$config->modules[$f.'.access']) {
            return null;
        }

        if (!isset($installation[$section][$f.'.dbprofile'])) {
            $config->modules[$f.'.dbprofile'] = 'default';
        } else {
            $config->modules[$f.'.dbprofile'] = $installation[$section][$f.'.dbprofile'];
        }

        if ($allModuleInfo) {
            if (!isset($installation[$section][$f.'.version'])) {
                $installation[$section][$f.'.version'] = '';
            }

            if (!isset($installation[$section][$f.'.dataversion'])) {
                $installation[$section][$f.'.dataversion'] = '';
            }

            if (!isset($installation['__modules_data'][$f.'.contexts'])) {
                $installation['__modules_data'][$f.'.contexts'] = '';
            }

            $config->modules[$f.'.version'] = $installation[$section][$f.'.version'];
            $config->modules[$f.'.dataversion'] = $installation[$section][$f.'.dataversion'];
            $config->modules[$f.'.installed'] = $installation[$section][$f.'.installed'];

            $config->_allModulesPathList[$f] = $path;
        }

        if ($config->modules[$f.'.access'] == 3) {
            $config->_externalModulesPathList[$f] = $path;
        } elseif ($config->modules[$f.'.access']) {
            $config->_modulesPathList[$f] = $path;
        }
        return $moduleInfo;
    }

    /**
     * Analyse plugin paths
     * @param object $config the config container
     */
    protected function _loadPluginsPathList($config) {
        $list = App::getAllPluginsPath();
        foreach ($list as $k=>$p) {
            if ($handle = opendir($p)) {
                while (false !== ($f = readdir($handle))) {
                    if ($f[0] != '.' && is_dir($p.$f)) {
                        if ($subdir = opendir($p.$f)) {
                            if ($k!=0 && $config->compilation['checkCacheFiletime']) {
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
     * @param array $urlconf  urlengine configuration. scriptNameServerVariable, basePath,
     * jelixWWWPath and jqueryPath should be present
     */
    protected function getPaths(&$urlconf, $pseudoScriptName ='', $isCli = false) {
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
                $urlconf['urlScriptPath'] = ($pseudoScriptName? App::appPath('/scripts/'): getcwd().'/');
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

            if($urlconf['jelixWWWPath'][0] != '/')
                $urlconf['jelixWWWPath'] = $basepath.$urlconf['jelixWWWPath'];
            if($urlconf['jqueryPath'][0] != '/')
                $urlconf['jqueryPath'] = $basepath.$urlconf['jqueryPath'];
            $snp = substr($urlconf['urlScript'],strlen($localBasePath));

            if ($localBasePath == '/') {
                $urlconf['documentRoot'] = App::wwwPath();
            }
            else if(strpos(App::wwwPath(), $localBasePath) === false) {
                if (isset($_SERVER['DOCUMENT_ROOT'])) {
                    $urlconf['documentRoot'] = $_SERVER['DOCUMENT_ROOT'];
                }
                else {
                    $urlconf['documentRoot'] = App::wwwPath();
                }
            }
            else {
                $urlconf['documentRoot'] = substr(App::wwwPath(), 0, - (strlen($localBasePath)));
            }
        }

        $pos = strrpos($snp, '.php');
        if ($pos !== false) {
            $snp = substr($snp,0,$pos);
        }
        $urlconf['urlScriptId'] = $snp;
        $urlconf['urlScriptIdenc'] = rawurlencode($snp);
    }

    static public function findServerName($ext = '.php', $isCli = false) {
        $varname = '';
        $extlen = strlen($ext);

        if (strrpos($_SERVER['SCRIPT_NAME'], $ext) === (strlen($_SERVER['SCRIPT_NAME']) - $extlen)
           || $isCli) {
            return 'SCRIPT_NAME';
        }
        else if (isset($_SERVER['REDIRECT_URL'])
                  && strrpos( $_SERVER['REDIRECT_URL'], $ext) === (strlen( $_SERVER['REDIRECT_URL']) -$extlen)) {
            return 'REDIRECT_URL';
        }
        else if (isset($_SERVER['ORIG_SCRIPT_NAME'])
                  && strrpos( $_SERVER['ORIG_SCRIPT_NAME'], $ext) === (strlen( $_SERVER['ORIG_SCRIPT_NAME']) - $extlen)) {
            return 'ORIG_SCRIPT_NAME';
        }
        throw new Exception('Error in main configuration on URL engine parameters -- In config file the parameter urlengine:scriptNameServerVariable is empty and Jelix doesn\'t find
            the variable in $_SERVER which contains the script name. You must see phpinfo and setup this parameter in your config file.', 11);
    }
}
