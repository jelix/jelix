<?php
/**
* @author      Laurent Jouanneau
* @copyright   2009-2014 Laurent Jouanneau
* @link        http://jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/
namespace Jelix\Installer;

use Jelix\Routing\UrlMapping\XmlEntryPoint;

/**
 * container for entry points properties
 */
class EntryPoint {

    /**
     * error code stored in a component: impossible to install
     * the module because dependencies are missing
     */
    const INSTALL_ERROR_MISSING_DEPENDENCIES = 1;

    /**
     * error code stored in a component: impossible to install
     * the module because of circular dependencies
     */
    const INSTALL_ERROR_CIRCULAR_DEPENDENCY = 2;

    /**
      * @var StdObj   configuration parameters. compiled content of config files
      *  result of the merge of entry point config, localconfig.ini.php,
      *  mainconfig.ini.php and defaultconfig.ini.php
      */
    protected $config;

    /**
     * @var string the filename of the configuration file dedicated to the entry point
     *       ex: <apppath>/app/config/index/config.ini.php
     */
    protected $configFile;

    /**
     * the mainconfig.ini.php file combined with defaultconfig.ini.php
     * @var \Jelix\IniFile\MultiIniModifier
     */
    protected $mainConfigIni;

    /**
     * the localconfig.ini.php file combined with $mainConfigIni
     * @var \Jelix\IniFile\MultiIniModifier
     */
    protected $localConfigIni;

    /**
      * entrypoint config of app/config/
      * @var \Jelix\IniFile\IniModifier
      */
    protected $epConfigIni;

    /**
    +     * entrypoint config of var/config/
    +     * @var \Jelix\IniFile\IniModifier
    +     */
    protected $localEpConfigIni;

    /**
     * the entry point config combined with $localConfigIni
     * @var \Jelix\IniFile\MultiIniModifier
     */
    protected $fullConfigIni;

    /** @var string entrypoint id of the entrypoint that have the same config */
    public $sameConfigAs = null;

    /**
     * @var boolean true if the script corresponding to the configuration
     *                is a script for CLI
     */
    public $isCliScript;

    /**
     * @var string the url path of the entry point
     */
    public $scriptName;

    /**
     * @var string the filename of the entry point
     */
    public $file;

    /**
     * @var string the type of entry point
     */
    public $type;

    /**
     * @var XmlEntryPoint
     */
    protected $urlMap;

    /**
     * @var \Jelix\Core\Infos\ModuleInfos[]
     */
    protected $modulesInfos = array();

    /**
     * key is module name
     * @var \Jelix\Installer\AbstractInstallLauncher[]
     */
    protected $moduleLaunchers = array();

    /**
     * @var \Jelix\Core\Infos\AppInfos
     */
    protected $appInfos;

    /**
     * @param \Jelix\IniFile\MultiIniModifier $mainConfig   the mainconfig.ini.php file combined with defaultconfig.ini.php
     * @param \Jelix\IniFile\MultiIniModifier $localConfig   the localconfig.ini.php file combined with $mainConfig
     * @param string $configFile the path of the configuration file, relative
     *                           to the app/config directory
     * @param string $file the filename of the entry point
     * @param string $type type of the entry point ('classic', 'cli', 'xmlrpc'....)
     */
    function __construct(\Jelix\IniFile\MultiIniModifier $mainConfig,
                         \Jelix\IniFile\MultiIniModifier $localConfig,
                         $configFile, $file, $type) {
        $this->type = $type;
        $this->isCliScript = ($type == 'cmdline');
        $this->configFile = $configFile;
        $this->scriptName =  ($this->isCliScript?$file:'/'.$file);
        $this->file = $file;
        $this->mainConfigIni = $mainConfig;
        $this->localConfigIni = $localConfig;

        $appConfigPath = \Jelix\Core\App::appConfigPath($configFile);
        if (!file_exists($appConfigPath)) {
            \jFile::createDir(dirname($appConfigPath));
            file_put_contents($appConfigPath, ';<'.'?php die(\'\');?'.'>');
        }
        $this->epConfigIni = new \Jelix\IniFile\IniModifier($appConfigPath);

        $varConfigPath = \Jelix\Core\App::configPath($configFile);
        if (!file_exists($varConfigPath)) {
            \jFile::createDir(dirname($varConfigPath));
            file_put_contents($varConfigPath, ';<'.'?php die(\'\');?'.'>');
        }
        $this->localEpConfigIni = new \Jelix\IniFile\IniModifier($varConfigPath);

        $this->fullConfigIni = new \Jelix\IniFile\MultiIniModifier($localConfig, $this->epConfigIni);
        $this->fullConfigIni = new \Jelix\IniFile\MultiIniModifier($this->fullConfigIni, $this->localEpConfigIni);

        $compiler = new \Jelix\Core\Config\Compiler($configFile,
                                                    $this->scriptName,
                                                    $this->isCliScript);
        $this->config = $compiler->read(true);
        $this->modulesInfos = $compiler->getModulesInfos();
    }

    public function setUrlMap(XmlEntryPoint $urlEp) {
        $this->urlMap = $urlEp;
    }

    public function getUrlMap() {
        return $this->urlMap;
    }

    /**
     * @param \Jelix\Core\Infos\AppInfos $app
     */
    public function setAppInfos(\Jelix\Core\Infos\AppInfos $app) {
        $this->appInfos = $app;
    }

    /**
     * @return \Jelix\Core\Infos\AppInfos
     */
    public function getAppInfos() {
        return $this->appInfos;
    }

    /**
     * @return string the entry point id
     */
    function getEpId() {
        return $this->config->urlengine['urlScriptId'];
    }

    /**
     * @return array the list of modules and their path, as stored in the
     * compiled configuration file
     */
    function getModulesList() {
        return $this->config->_allModulesPathList;
    }

    /**
     * @deprecated
     * @see getModuleStatus()
     */
    function getModule($moduleName) {
        trigger_error("EntryPoint::getModule() is deprecated", E_WARNING);
        return $this->getModuleStatus($moduleName);
    }

    /**
     * the mainconfig.ini.php file combined with defaultconfig.ini.php
     * @return \Jelix\IniFile\MultiIniModifier
     * @since 1.7
     */
    function getMainConfigIni() {
        return $this->mainConfigIni;
    }

    /**
     * the localconfig.ini.php file combined with $mainConfigIni
     * @return \Jelix\IniFile\MultiIniModifier
     * @since 1.7
     */
    function getLocalConfigIni() {
        return $this->localConfigIni;
    }

    /**
     * the entry point config (static and local) combined with $localConfigIni
     * @return \Jelix\IniFile\MultiIniModifier
     * @since 1.7
     */
    function getConfigIni() {
        return $this->fullConfigIni;
    }

    /*
     * he static entry point config alone (in app/config)
     * @return \Jelix\IniFile\IniModifier
     * @since 1.6.8
     */
    function getEpConfigIni() {
        return $this->epConfigIni;
    }

    /*
     * the local entry point config alone (in var/config)
     * @return \Jelix\IniFile\IniModifier
     * @since 1.7
     */
    function getLocalEpConfigIni() {
        return $this->localEpConfigIni;
    }

    /**
     * @return string the config file name of the entry point
     */
    function getConfigFile() {
        return $this->configFile;
    }

    /**
     * @return stdObj the config content of the entry point, as seen when
     * calling App::config()
     */
    function getConfigObj() {
        return $this->config;
    }

    function setConfigObj($config) {
        $this->config = $config;
    }

    /**
     * @param callable $launcherGetter
     * @internal
     */
    function createInstallLaunchers(callable $launcherGetter) {
        $this->moduleLaunchers = array();
        $epId = $this->getEpId();

        // now let's read all modules properties
        foreach ($this->config->_allModulesPathList as $name=>$path) {
            $moduleStatus = new \Jelix\Installer\ModuleStatus($name, $this->config->modules);
            $moduleInfos = $this->modulesInfos[$name];

            $launcher = $this->moduleLaunchers[$name] = $launcherGetter($moduleStatus, $moduleInfos);
            $launcher->addModuleStatus($epId, $moduleStatus);
        }
    }

    /**
     * @return AbstractInstallLauncher[]
     */
    function getLaunchers() {
        return $this->moduleLaunchers;
    }

    /**
     * @return \Jelix\Installer\AbstractInstallLauncher
     */
    function getLauncher($moduleName) {
        if(!isset($this->moduleLaunchers[$moduleName])) {
            return null;
        }
        return $this->moduleLaunchers[$moduleName];
    }
}
