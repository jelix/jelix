<?php
/**
* @package     jelix
* @subpackage  installer
* @author      Laurent Jouanneau
* @copyright   2009-2010 Laurent Jouanneau
* @link        http://jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

/**
 * container for entry points properties
 */
class jInstallerEntryPoint {

    /**
      * @var StdObj   configuration parameters. compiled content of config files
      *  result of the merge of entry point config, localconfig.ini.php,
      *  mainconfig.ini.php and defaultconfig.ini.php
      */
    protected $config;

    /**
     * @var string the filename of the configuration file dedicated to the entry point
     *       ex: <apppath>/var/config/index/config.ini.php
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
     * the entry point config combined with $localConfigIni
     * @var \Jelix\IniFile\MultiIniModifier
     */
    protected $configIni;

    /**
      * entrypoint config
      * @var \Jelix\IniFile\IniModifier
      */
    protected $epConfigIni;

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
     * @param \Jelix\IniFile\MultiIniModifier $mainConfig   the mainconfig.ini.php file combined with defaultconfig.ini.php
     * @param \Jelix\IniFile\MultiIniModifier $localConfig   the localconfig.ini.php file combined with $mainConfig
     * @param string $configFile the path of the configuration file, relative
     *                           to the var/config directory
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
        $this->epConfigIni = new \Jelix\IniFile\IniModifier(jApp::configPath($configFile));
        $this->configIni = new \Jelix\IniFile\MultiIniModifier($localConfig, $this->epConfigIni);
        $this->config = jConfigCompiler::read($configFile, true,
                                              $this->isCliScript,
                                              $this->scriptName);
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
     * @return jInstallerModuleInfos informations about a specific module used
     * by the entry point
     */
    function getModule($moduleName) {
        return new jInstallerModuleInfos($moduleName, $this->config->modules);
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
     * the entry point config combined with $localConfigIni
     * @return \Jelix\IniFile\MultiIniModifier
     * @since 1.7
     */
    function getConfigIni() {
        return $this->configIni;
    }

    /*
     * the entry point config alone
     * @return \Jelix\IniFile\IniModifier
     * @since 1.6.8
     */
    function getEpConfigIni() {
        return $this->epConfigIni;
    }

    /**
     * @return string the config file name of the entry point
     */
    function getConfigFile() {
        return $this->configFile;
    }

    /**
     * @return stdObj the config content of the entry point, as seen when
     * calling jApp::config()
     */
    function getConfigObj() {
        return $this->config;
    }

    function setConfigObj($config) {
        $this->config = $config;
    }
}
