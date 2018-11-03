<?php
/**
 * @author      Laurent Jouanneau
 * @copyright   2018 Laurent Jouanneau
 * @link        http://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */
namespace Jelix\Installer\Module\API;


/**
 *
 * @since 1.7
 */
class PreConfigurationHelpers {

    /**
     * global setup
     * @var \Jelix\Installer\GlobalSetup
     */
    protected $globalSetup;

    function __construct(\Jelix\Installer\GlobalSetup $setup) {
        $this->globalSetup = $setup;
    }

    public function forLocalConfiguration() {
        return $this->globalSetup->forLocalConfiguration();
    }

    /**
     * default config, main config combined with or without local config
     * @return \Jelix\IniFile\IniModifierArray
     */
    public function getConfigIni() {
        if ($this->globalSetup->forLocalConfiguration()) {
            $ini = $this->globalSetup->getAppConfigIni(true);
            $ini['local'] = $this->globalSetup->getLocalConfigIni();
            return $ini;
        }
        return $this->globalSetup->getAppConfigIni();
    }

    /**
     * return the section name of configuration of a plugin for the coordinator
     * or the IniModifier for the configuration file of the plugin if it exists.
     *
     * @param string $pluginName
     * @return array|null null if plugin is unknown, else array($iniModifier, $section)
     * @throws \Exception when the configuration filename is not found
     */
    public function getCoordPluginConf($pluginName)
    {
        return $this->globalSetup->getCoordPluginConf($this->getConfigIni(), $pluginName);
    }

    /**
     * main config or local config ini file alone
     *
     * @return \Jelix\IniFile\IniModifierInterface|\Jelix\IniFile\IniReaderInterface
     */
    public function getSingleConfigIni() {
        if ($this->globalSetup->forLocalConfiguration()) {
            return $this->globalSetup->getLocalConfigIni();
        }
        else {
            return $this->globalSetup->getMainConfigIni();
        }
    }

    public function getProfilesIni() {
        return $this->globalSetup->getProfilesIni();
    }


    /**
     * Main entrypoint of the application (in most of case, index.php)
     * @return \Jelix\Installer\EntryPointPreConfigurator
     */
    public function getMainEntryPoint() {
        $ep = $this->globalSetup->getMainEntryPoint();
        $flc = $this->globalSetup->forLocalConfiguration();
        return new \Jelix\Installer\EntryPointPreConfigurator($ep, $this->globalSetup, $flc);
    }

    /**
     * List of entry points of the application
     *
     * @return \Jelix\Installer\EntryPointPreConfigurator[]
     */
    public function getEntryPointsList() {
        $list = $this->globalSetup->getEntryPointsList();
        $globalSetup = $this->globalSetup;
        $flc = $this->globalSetup->forLocalConfiguration();
        return array_map(function($ep) use($globalSetup, $flc) {
            return new \Jelix\Installer\EntryPointPreConfigurator($ep, $globalSetup, $flc);
        }, $list);
    }

    /**
     * @param string $type
     * @return \Jelix\Installer\EntryPointPreConfigurator[]
     */
    public function getEntryPointsByType($type='classic') {
        $list = $this->globalSetup->getEntryPointsByType($type);
        $globalSetup = $this->globalSetup;
        $flc = $this->globalSetup->forLocalConfiguration();
        return array_map(function($ep) use($globalSetup, $flc) {
            return new \Jelix\Installer\EntryPointPreConfigurator($ep, $globalSetup, $flc);
        }, $list);
    }

    /**
     * @param $epId
     * @return \Jelix\Installer\EntryPointPreConfigurator
     */
    public function getEntryPointsById($epId) {
        $ep = $this->globalSetup->getEntryPointById($epId);
        if ($ep) {
            $ep = new \Jelix\Installer\EntryPointPreConfigurator($ep, $this->globalSetup, $this->forLocalConfiguration);
        }
        return $ep;
    }
}
