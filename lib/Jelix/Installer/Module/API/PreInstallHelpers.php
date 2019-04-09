<?php
/**
 * @author      Laurent Jouanneau
 * @copyright   2018 Laurent Jouanneau
 *
 * @see        http://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

namespace Jelix\Installer\Module\API;

use Jelix\IniFile\IniReaderInterface;

/**
 * @since 1.7
 */
class PreInstallHelpers
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

    /**
     * default config, main config combined with local config. Read only.
     *
     * @return \Jelix\IniFile\IniModifierArray
     */
    public function getConfigIni()
    {
        $ini = $this->globalSetup->getSystemConfigIni(true);
        $ini['local'] = $this->globalSetup->getLocalConfigIni();

        return $ini;
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
    public function getCoordPluginConfig($pluginName, IniReaderInterface $config = null)
    {
        if (!$config) {
            $config = $this->getConfigIni();
        }

        return $this->globalSetup->getCoordPluginConf($config, $pluginName);
    }

    /**
     * local config ini file alone.
     *
     * @return \Jelix\IniFile\IniModifierInterface|\Jelix\IniFile\IniReaderInterface
     */
    public function getLocalConfigIni()
    {
        return $this->globalSetup->getLocalConfigIni();
    }

    public function getProfilesIni()
    {
        return $this->globalSetup->getProfilesIni();
    }

    /**
     * Main entrypoint of the application (in most of case, index.php).
     *
     * @return \Jelix\Installer\EntryPoint
     */
    public function getMainEntryPoint()
    {
        return $this->globalSetup->getMainEntryPoint();
    }

    /**
     * List of entry points of the application.
     *
     * @return \Jelix\Installer\EntryPoint[]
     */
    public function getEntryPointsList()
    {
        return $this->globalSetup->getEntryPointsList();
    }

    /**
     * @param string $type
     *
     * @return \Jelix\Installer\EntryPoint[]
     */
    public function getEntryPointsByType($type = 'classic')
    {
        return $this->globalSetup->getEntryPointsByType($type);
    }

    /**
     * @param $epId
     *
     * @return \Jelix\Installer\EntryPoint
     */
    public function getEntryPointsById($epId)
    {
        return $this->globalSetup->getEntryPointById($epId);
    }
}
