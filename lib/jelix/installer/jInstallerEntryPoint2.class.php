<?php
/**
 * @package     jelix
 * @subpackage  installer
 * @author      Laurent Jouanneau
 * @copyright   2009-2017 Laurent Jouanneau
 * @link        http://jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */
use Jelix\Routing\UrlMapping\XmlEntryPoint;
use Jelix\IniFile\IniModifier;


/**
 * container for entry points properties
 */
class jInstallerEntryPoint2
{

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
     * all original configuration files combined
     * @var \Jelix\IniFile\IniModifierArray
     */
    protected $configIni;

    /**
     * all local configuration files combined with original configuration file
     * @var \Jelix\IniFile\IniModifierArray
     */
    protected $localConfigIni;

    /**
     * the live configuration file combined with all other configuration files
     * @var \Jelix\IniFile\IniModifierArray
     */
    protected $liveConfigIni;


    /**
     * @var boolean true if the script corresponding to the configuration
     *                is a script for CLI
     */
    protected $_isCliScript;

    /**
     * @var string the url path of the entry point
     */
    protected $scriptName;

    /**
     * @var string the filename of the entry point
     */
    protected $file;

    /**
     * @var string the type of entry point
     */
    protected $type;

    /**
     * @var XmlEntryPoint
     */
    protected $urlMap;

    /**
     * @var jInstallerGlobalSetup
     */
    protected $globalSetup;

    /**
     * @var jInstallerModule2
     */
    protected $moduleInstaller;

    /**
     * @param jInstallerGlobalSetup $globalSetup
     * @param string $configFile the path of the configuration file, relative
     *                           to the app/config directory
     * @param string $file the filename of the entry point
     * @param string $type type of the entry point ('classic', 'cli', 'xmlrpc'....)
     */
    function __construct(jInstallerGlobalSetup $globalSetup,
                         $configFile, $file, $type)
    {
        $this->type = $type;
        $this->_isCliScript = ($type == 'cmdline');
        $this->configFile = $configFile;
        $this->scriptName = ($this->_isCliScript ? $file : '/' . $file);
        $this->file = $file;
        $this->globalSetup = $globalSetup;

        $appConfigPath = jApp::appConfigPath($configFile);
        if (!file_exists($appConfigPath)) {
            jFile::createDir(dirname($appConfigPath));
            file_put_contents($appConfigPath, ';<' . '?php die(\'\');?' . '>');
        }

        $this->configIni = clone $globalSetup->getConfigIni();
        $this->configIni['entrypoint'] = new IniModifier($appConfigPath);

        $varConfigPath = jApp::varConfigPath($configFile);
        $localEpConfigIni = new IniModifier($varConfigPath, ';<' . '?php die(\'\');?' . '>');
        $this->localConfigIni = clone $this->configIni;
        $this->localConfigIni['local'] = $globalSetup->getLocalConfigIni()['local'];
        $this->localConfigIni['localentrypoint'] = $localEpConfigIni;

        $this->liveConfigIni = clone $this->localConfigIni;
        $this->liveConfigIni['live'] = $globalSetup->getLiveConfigIni()['live'];

        $this->config = jConfigCompiler::read($configFile, true,
            $this->_isCliScript,
            $this->scriptName);

        $this->urlMap = $globalSetup->getUrlModifier()
            ->addEntryPoint($this->getEpId(), $type);
    }

    /**
     * @param jIInstallerComponent2 $installer
     * @access private
     */
    public function _setCurrentModuleInstaller(jIInstallerComponent2 $installer)
    {
        $this->moduleInstaller = $installer;
    }

    protected $legacyInstallerEntryPoint = null;

    public function getLegacyInstallerEntryPoint()
    {
        if ($this->legacyInstallerEntryPoint === null) {
            $this->legacyInstallerEntryPoint = new jInstallerEntryPoint($this, $this->globalSetup);
        }
        return $this->legacyInstallerEntryPoint;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getScriptName()
    {
        return $this->scriptName;
    }

    public function getFileName()
    {
        return $this->file;
    }

    public function isCliScript()
    {
        return $this->_isCliScript;
    }

    public function getUrlMap()
    {
        return $this->urlMap;
    }

    /**
     * @return string the entry point id
     */
    function getEpId()
    {
        return $this->config->urlengine['urlScriptId'];
    }

    /**
     * @return array the list of modules and their path, as stored in the
     * compiled configuration file
     */
    function getModulesList()
    {
        return $this->config->_allModulesPathList;
    }

    /**
     * @return jInstallerModuleInfos informations about a specific module used
     * by the entry point
     */
    function getModuleInfos($moduleName)
    {
        return new jInstallerModuleInfos($moduleName, $this->config->modules);
    }

    /**
     * the full original configuration of the entry point
     *
     * combination of
     *  - "default" => defaultconfig.ini.php
     *  - "main" => mainconfig.ini.php
     *  - "entrypoint" => app/config/$entrypointConfigFile
     *
     * @return \Jelix\IniFile\IniModifierArray
     */
    function getConfigIni()
    {
        return $this->configIni;
    }

    /*
     * the local entry point config (in var/config) combined with the original configuration
     *
     * combination of
     *  - "default" => defaultconfig.ini.php
     *  - "main" => mainconfig.ini.php
     *  - "entrypoint" => app/config/$entrypointConfigFile
     *  - "local" => localconfig.ini.php
     *  - "localentrypoint" => var/config/$entrypointConfigFile
     *
     * @return \Jelix\IniFile\IniModifierArray
     */
    function getLocalConfigIni()
    {
        return $this->localConfigIni;
    }

    /*
     * the live config combined with other configuration files
     *
     * combination of
     *  - "default" => defaultconfig.ini.php
     *  - "main" => mainconfig.ini.php
     *  - "entrypoint" => app/config/$entrypointConfigFile
     *  - "local" => localconfig.ini.php
     *  - "localentrypoint" => var/config/$entrypointConfigFile
     *  - "live" => var/config/liveconfig.ini.php
     * @return \Jelix\IniFile\IniModifierArray
     */
    function getLiveConfigIni()
    {
        return $this->liveConfigIni;
    }

    /**
     * @return string the config file name of the entry point
     */
    function getConfigFile()
    {
        return $this->configFile;
    }

    /**
     * @return stdObj the config content of the entry point, as seen when
     * calling jApp::config()
     */
    function getConfigObj()
    {
        return $this->config;
    }

    function setConfigObj($config)
    {
        $this->config = $config;
    }

    /**
     * Declare web assets into the entry point config
     * @param string $name the name of webassets
     * @param array $values should be an array with one or more of these keys 'css' (array), 'js'  (array), 'require' (string)
     * @param string $collection the name of the webassets collection
     * @param bool $force
     */
    public function declareWebAssets($name, array $values, $collection, $force)
    {
        $this->globalSetup->declareWebAssetsInConfig($this->configIni['entrypoint'], $name, $values, $collection, $force);
    }

    /**
     *
     */
    public function firstConfExec()
    {
        $config = $this->getConfigFile();
        return $this->moduleInstaller->firstExec('cf:' . $config);
    }

    /**
     * import a sql script into the current profile.
     *
     * The name of the script should be store in install/$name.databasetype.sql
     * in the directory of the component. (replace databasetype by mysql, pgsql etc.)
     * You can however provide a script compatible with all databases, but then
     * you should indicate the full name of the script, with a .sql extension.
     *
     * @param string $name the name of the script
     * @param string $module the module from which we should take the sql file.
     * @param boolean $inTransaction indicate if queries should be executed inside a transaction
     * @throws Exception
     */
    public function execSQLScript($name, $module, $inTransaction = true)
    {
        $conf = $this->getConfigObj()->_modulesPathList;
        if (!isset($conf[$module])) {
            throw new Exception('execSQLScript : invalid module name');
        }
        $path = $conf[$module];
        $this->moduleInstaller->_execSQLScript($name, $path, $inTransaction);
    }
}