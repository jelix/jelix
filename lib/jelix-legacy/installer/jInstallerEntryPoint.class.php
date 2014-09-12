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

    /** @var StdObj   configuration parameters. compiled content of config files */
    public $config;

    /** @var string the filename of the configuration file */
    public $configFile;

    /** @var \Jelix\IniFile\MultiModifier */
    public $configIni;

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
     * @var \Jelix\Core\Infos\ModuleInfos[]
     */
    protected $modulesInfos = array();

    /**
     * @param jIniFileModifier    $mainConfig   the mainconfig.ini.php file
     * @param string $configFile the path of the configuration file, relative
     *                           to the var/config directory
     * @param string $file the filename of the entry point
     * @param string $type type of the entry point ('classic', 'cli', 'xmlrpc'....)
     */
    function __construct($mainConfig, $configFile, $file, $type) {
        $this->type = $type;
        $this->isCliScript = ($type == 'cmdline');
        $this->configFile = $configFile;
        $this->scriptName =  ($this->isCliScript?$file:'/'.$file);
        $this->file = $file;
        $this->configIni = new jIniMultiFilesModifier($mainConfig, jApp::configPath($configFile));
        $compiler = new \Jelix\Core\Config\Compiler($configFile,
                                                    $this->scriptName,
                                                    $this->isCliScript);
        $this->config = $compiler->read(true);
        $this->modulesInfos = $compiler->getModulesInfos();
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
     * @return \Jelix\Installer\ModuleStatus informations about the installation status of a module
     */
    function getModuleStatus($moduleName) {
        return new \Jelix\Installer\ModuleStatus($moduleName, $this->config->modules);
    }

    /**
     * @return \Jelix\Core\Infos\ModuleInfos information about the identity of the module
     */
    function getModuleInfos($moduleName) {
        if (isset($this->modulesInfos[$moduleName])) {
            return $this->modulesInfos[$moduleName];
        }
        return null;
    }
}
