<?php
/**
 * @author      Laurent Jouanneau
 * @copyright   2018-2024 Laurent Jouanneau
 *
 * @see        http://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

namespace Jelix\Installer\Module\API;

use Jelix\IniFile\IniReaderInterface;

/**
 * @since 1.7
 */
class PreConfigurationHelpers
{
    /**
     * global setup.
     *
     * @var \Jelix\Installer\GlobalSetup
     */
    protected $globalSetup;

    public function __construct(\Jelix\Installer\GlobalSetup $setup)
    {
        $this->globalSetup = $setup;
    }

    public function forLocalConfiguration()
    {
        return $this->globalSetup->forLocalConfiguration();
    }

    /**
     * default config, main config combined with or without local config.
     *
     * @return \Jelix\IniFile\IniModifierArray
     */
    public function getConfigIni()
    {
        if ($this->globalSetup->forLocalConfiguration()) {
            $ini = $this->globalSetup->getSystemConfigIni(true);
            $ini['local'] = $this->globalSetup->getLocalConfigIni();

            return $ini;
        }

        return $this->globalSetup->getSystemConfigIni();
    }

    /**
     * return the section name of configuration of a plugin for the coordinator
     * or the IniModifier for the configuration file of the plugin if it exists.
     *
     * @param string             $pluginName
     * @param IniReaderInterface $config     the configuration file from which we
     *                                       should extract the plugin configuration. default
     *                                       is the full configuration.
     *
     * @throws \Exception when the configuration filename is not found
     *
     * @return null|array null if plugin is unknown, else array($iniModifier, $section)
     */
    public function getCoordPluginConfig($pluginName, ?IniReaderInterface $config = null)
    {
        if (!$config) {
            $config = $this->getConfigIni();
        }

        return $this->globalSetup->getCoordPluginConf($config, $pluginName);
    }

    /**
     * main config or local config ini file alone.
     *
     * @return \Jelix\IniFile\IniModifierInterface|\Jelix\IniFile\IniReaderInterface
     */
    public function getSingleConfigIni()
    {
        if ($this->globalSetup->forLocalConfiguration()) {
            return $this->globalSetup->getLocalConfigIni();
        }

        return $this->globalSetup->getMainConfigIni();
    }

    public function getProfilesIni()
    {
        return $this->globalSetup->getProfilesIni();
    }

    /**
     * Main entrypoint of the application (in most of case, index.php).
     *
     * @return \Jelix\Installer\EntryPointPreConfigurator
     */
    public function getMainEntryPoint()
    {
        $ep = $this->globalSetup->getMainEntryPoint();
        $flc = $this->globalSetup->forLocalConfiguration();

        return new \Jelix\Installer\EntryPointPreConfigurator($ep, $this->globalSetup, $flc);
    }

    /**
     * List of entry points of the application.
     *
     * @return \Jelix\Installer\EntryPointPreConfigurator[]
     */
    public function getEntryPointsList()
    {
        $list = $this->globalSetup->getEntryPointsList();
        $globalSetup = $this->globalSetup;
        $flc = $this->globalSetup->forLocalConfiguration();

        return array_map(function ($ep) use ($globalSetup, $flc) {
            return new \Jelix\Installer\EntryPointPreConfigurator($ep, $globalSetup, $flc);
        }, $list);
    }

    /**
     * @param string $type
     *
     * @return \Jelix\Installer\EntryPointPreConfigurator[]
     */
    public function getEntryPointsByType($type = 'classic')
    {
        $list = $this->globalSetup->getEntryPointsByType($type);
        $globalSetup = $this->globalSetup;
        $flc = $this->globalSetup->forLocalConfiguration();

        return array_map(function ($ep) use ($globalSetup, $flc) {
            return new \Jelix\Installer\EntryPointPreConfigurator($ep, $globalSetup, $flc);
        }, $list);
    }

    /**
     * @param $epId
     *
     * @return \Jelix\Installer\EntryPointPreConfigurator
     */
    public function getEntryPointsById($epId)
    {
        $ep = $this->globalSetup->getEntryPointById($epId);
        if ($ep) {
            $flc = $this->globalSetup->forLocalConfiguration();
            $ep = new \Jelix\Installer\EntryPointPreConfigurator($ep, $this->globalSetup, $flc);
        }

        return $ep;
    }

    /**
     * Path to the configuration directory.
     *
     * It gives the path to app/system or local/config, depending on if the
     * configuration is for the application or for the instance
     *
     * @param string $file
     *
     * @return string the path
     */
    public function configFilePath($file = '')
    {
        if (! $this->globalSetup->forLocalConfiguration()) {
            return \Jelix\Core\App::appSystemPath($file);
        }

        return \Jelix\Core\App::varConfigPath($file);
    }
}
