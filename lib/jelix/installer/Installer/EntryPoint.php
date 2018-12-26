<?php
/**
 * @author      Laurent Jouanneau
 * @copyright   2009-2018 Laurent Jouanneau
 * @link        http://jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */
namespace Jelix\Installer;


use Jelix\IniFile\IniModifier;
use \Jelix\IniFile\IniModifierReadOnly;

/**
 * container for entry points properties, for installers
 *
 * @since 1.7
 */
class EntryPoint
{
    /**
     * @var \StdClass   configuration parameters. compiled content of config files
     *  result of the merge of entry point config, localconfig.ini.php,
     *  mainconfig.ini.php and defaultconfig.ini.php
     */
    protected $config;

    /**
     * @var string the filename of the configuration file dedicated to the entry point
     *       ex: <apppath>/app/config/index/config.ini.php
     */
    protected $configFileName;

    /**
     * application entry point configuration
     * * @var \Jelix\IniFile\IniModifier
     */
    protected $appEpConfigIni;

    /**
     * local entry point configuration
     * @var \Jelix\IniFile\IniModifier
     */
    protected $localEpConfigIni;

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
     * @var GlobalSetup
     */
    protected $globalSetup;

    /**
     * @var \jInstallerEntryPoint
     */
    public $legacyInstallerEntryPoint = null;

    /**
     * @param GlobalSetup $globalSetup
     * @param string $configFile the path of the configuration file, relative
     *                           to the app/config directory
     * @param string $file the filename of the entry point
     * @param string $type type of the entry point ('classic', 'cli', 'xmlrpc'....)
     */
    function __construct(GlobalSetup $globalSetup,
                         $configFile, $file, $type)
    {
        $this->type = $type;
        $this->_isCliScript = ($type == 'cmdline');
        $this->configFileName = $configFile;
        $this->scriptName = ($this->_isCliScript ? $file : '/' . $file);
        $this->file = $file;
        $this->globalSetup = $globalSetup;

        $appConfigPath = \jApp::appConfigPath($configFile);
        if (!file_exists($appConfigPath)) {
            \jFile::createDir(dirname($appConfigPath));
            file_put_contents($appConfigPath, ';<' . '?php die(\'\');?' . '>');
        }
        $varConfigPath = \jApp::varConfigPath($configFile);

        $this->appEpConfigIni = new IniModifier($appConfigPath);
        $this->localEpConfigIni = new IniModifier($varConfigPath, ';<' . '?php die(\'\');?' . '>');

        $this->config = \jConfigCompiler::read($configFile, true,
            $this->_isCliScript,
            $this->scriptName);

    }

    /**
     * @return string the type of entry point
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return string the url path of the entry point
     */
    public function getScriptName()
    {
        return $this->scriptName;
    }

    /**
     * @return string the filename of the entry point
     */
    public function getFileName()
    {
        return $this->file;
    }

    /**
     * @return bool
     */
    public function isCliScript()
    {
        return $this->_isCliScript;
    }


    /**
     * @return string the entry point id
     */
    function getEpId()
    {
        return $this->config->urlengine['urlScriptId'];
    }

    /**
     * @return array[string] the list of all available modules (installed or not)
     * and their path, as stored in the compiled configuration file
     */
    function getModulesList()
    {
        return $this->config->_allModulesPathList;
    }


    /**
     * @return \Jelix\IniFile\IniModifierArray list of ini content of the
     *     configuration, and local configuration in the context of local installation
     */
    public function getConfigIni() {

        if ($this->globalSetup->isReadWriteConfigMode()) {
            $appCf = $this->appEpConfigIni;
        }
        else {
            $appCf = new IniModifierReadOnly($this->appEpConfigIni);
        }

        if ($this->globalSetup->forLocalConfiguration()) {
            $ini = $this->globalSetup->getAppConfigIni(true);
            $ini['entrypoint'] = $appCf;
            $ini['local'] = $this->globalSetup->getLocalConfigIni();
            if ($this->globalSetup->isReadWriteConfigMode()) {
                $ini['localentrypoint'] = $this->localEpConfigIni;
            }
            else {
                $ini['localentrypoint'] = new IniModifierReadOnly($this->localEpConfigIni);
            }
            return $ini;
        }
        $ini = $this->globalSetup->getAppConfigIni();
        $ini['entrypoint'] = $appCf;
        return $ini;
    }

    /**
     * @return IniModifier|IniModifierReadOnly ini content of the main configuration
     *   of the entry point, or its local configuration
     */
    public function getSingleConfigIni() {
        if ($this->globalSetup->forLocalConfiguration()) {
            $ini = $this->localEpConfigIni;
        }
        else {
            $ini = $this->appEpConfigIni;
        }

        if ($this->globalSetup->isReadWriteConfigMode()) {
            return $ini;
        }
        else {
            return new IniModifierReadOnly($ini);
        }
    }

    /**
     * @return string the config file name of the entry point
     */
    function getConfigFileName()
    {
        return $this->configFileName;
    }

    /**
     * @return \StdClass the config content of the entry point, as seen when
     * calling jApp::config()
     */
    function getConfigObj()
    {
        return $this->config;
    }

    /**
     * @param \StdClass $config
     */
    function setConfigObj($config)
    {
        $this->config = $config;
    }

}