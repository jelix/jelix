<?php
/**
 * @author      Laurent Jouanneau
 * @copyright   2009-2023 Laurent Jouanneau
 *
 * @see        http://jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

namespace Jelix\Installer;

use Jelix\IniFile\IniModifier;
use Jelix\IniFile\IniModifierReadOnly;
use Jelix\Core\Config\Compiler;

/**
 * container for entry points properties, for installers.
 *
 * @since 1.7
 */
class EntryPoint
{
    /**
     * @var \StdClass configuration parameters. compiled content of config files
     *                result of the merge of entry point config, localconfig.ini.php,
     *                mainconfig.ini.php and defaultconfig.ini.php
     */
    protected $config;

    /**
     * @var string the filename of the configuration file dedicated to the entry point
     *             ex: <apppath>/app/system/index/config.ini.php
     */
    protected $configFileName;

    /**
     * application entry point configuration.
     *
     * * @var \Jelix\IniFile\IniModifier
     */
    protected $appEpConfigIni;

    /**
     * local entry point configuration.
     *
     * @var \Jelix\IniFile\IniModifier
     */
    protected $localEpConfigIni;

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
     * @var bool true if the entrypoint is a local entrypoint
     */
    protected $isLocalEp = false;

    /**
     * @param GlobalSetup $globalSetup
     * @param string      $configFile  the path of the configuration file, relative
     *                                 to the app/system directory
     * @param string      $file        the filename of the entry point
     * @param string      $type        type of the entry point ('classic', 'cli', 'xmlrpc'....)
     */
    public function __construct(
        GlobalSetup $globalSetup,
        $configFile,
        $file,
        $type,
        $isLocalEp
    ) {
        $this->type = $type;
        $this->configFileName = $configFile;
        $this->scriptName = '/';
        $this->file = $file;
        $this->globalSetup = $globalSetup;
        $this->isLocalEp = $isLocalEp;

        $appSystemPath = \Jelix\Core\App::appSystemPath($configFile);
        $varConfigPath = \Jelix\Core\App::varConfigPath($configFile);

        if ( !$isLocalEp && !file_exists($appSystemPath)) {
            \jFile::createDir(dirname($appSystemPath));
            file_put_contents($appSystemPath, ';<' . '?php die(\'\');?' . '>');
        }

        $this->appEpConfigIni = new IniModifier($appSystemPath, ';<'.'?php die(\'\');?'.'>');
        $this->localEpConfigIni = new IniModifier($varConfigPath, ';<'.'?php die(\'\');?'.'>');

        $compiler = new Compiler($configFile,
            $this->scriptName
        );
        $this->config = $compiler->read(true);
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
     * @deprecated
     */
    public function isCliScript()
    {
        return false;
    }

    /**
     * @return string the entry point id
     */
    public function getEpId()
    {
        return $this->config->urlengine['urlScriptId'];
    }

    /**
     * @return string[] the list of all available modules (installed or not)
     *                       and their path, as stored in the compiled configuration file
     */
    public function getModulesList()
    {
        return $this->config->_allModulesPathList;
    }

    /**
     * Return a reader/modifier of the list of file ini, depending of the
     * context.
     *
     * In the context of dev, the list is:
     * - 'default': defaultconfig.ini
     * - 'main': mainconfig.ini
     * - 'entrypoint' : app/system/<ep>/config.ini
     *
     * In the context of local configuration, the list is
     *
     * - 'default': defaultconfig.ini
     * - 'main': mainconfig.ini
     * - 'entrypoint': app/system/<ep>/config.ini
     * - 'local': localconfig.ini
     * - 'localentrypoint': var/config/<ep>/config.ini
     *
     * @return \Jelix\IniFile\IniModifierArray list of ini content of the
     *                                         configuration
     */
    public function getConfigIni()
    {

        if (!$this->globalSetup->isReadWriteConfigMode() || $this->isLocalEp) {
            $appCf = new IniModifierReadOnly($this->appEpConfigIni);
        }
        else {
            $appCf = $this->appEpConfigIni;
        }

        if ($this->globalSetup->forLocalConfiguration()) {
            $ini = $this->globalSetup->getSystemConfigIni(true);
            $ini['entrypoint'] = $appCf;
            $ini['local'] = $this->globalSetup->getLocalConfigIni();
            if ($this->globalSetup->isReadWriteConfigMode()) {
                $ini['localentrypoint'] = $this->localEpConfigIni;
            } else {
                $ini['localentrypoint'] = new IniModifierReadOnly($this->localEpConfigIni);
            }

            return $ini;
        }

        $ini = $this->globalSetup->getSystemConfigIni();
        $ini['entrypoint'] = $appCf;

        return $ini;
    }

    /**
     * @return IniModifier|IniModifierReadOnly ini content of the main configuration
     *                                         of the entry point, or its local configuration
     */
    public function getSingleConfigIni()
    {
        if ($this->isLocalEp) {
            $ini = $this->localEpConfigIni;
        } else {
            $ini = $this->appEpConfigIni;
        }

        if ($this->globalSetup->isReadWriteConfigMode()) {
            return $ini;
        }

        return new IniModifierReadOnly($ini);
    }

    /**
     * @return string the config file name of the entry point
     */
    public function getConfigFileName()
    {
        return $this->configFileName;
    }

    /**
     * @return \StdClass the config content of the entry point, as seen when
     *                   calling App::config()
     */
    public function getConfigObj()
    {
        return $this->config;
    }

    /**
     * @param \StdClass $config
     */
    public function setConfigObj($config)
    {
        $this->config = $config;
    }

    /**
     * return the section name of configuration of a plugin for the coordinator
     * or the IniModifier for the configuration file of the plugin if it exists.
     *
     * @param string $pluginName
     *
     * @throws \Exception when the configuration filename is not found
     *
     * @return null|array null if plugin is unknown, else array($iniModifier, $section)
     */
    public function getCoordPluginConfig($pluginName)
    {
        return $this->globalSetup->getCoordPluginConf($this->getConfigIni(), $pluginName);
    }
}
