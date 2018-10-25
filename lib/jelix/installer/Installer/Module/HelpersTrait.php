<?php
/**
 * @author      Laurent Jouanneau
 * @copyright   2018 Laurent Jouanneau
 * @link        http://jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */
namespace Jelix\Installer\Module;

/**
 * Trait for installer/configurator classes
 *
 * @since 1.7
 */
trait HelpersTrait
{
    /**
     * global setup
     * @var \Jelix\Installer\GlobalSetup
     */
    private $globalSetup;

    final function setGlobalSetup(\Jelix\Installer\GlobalSetup $setup) {
        $this->globalSetup = $setup;
    }

    /**
     * return the section name of configuration of a plugin for the coordinator
     * or the IniModifier for the configuration file of the plugin if it exists.
     * @param \Jelix\IniFile\IniModifier $config the global configuration content
     * @param string $pluginName
     * @return array|null null if plugin is unknown, else array($iniModifier, $section)
     * @throws \Exception when the configuration filename is not found
     */
    protected final function getCoordPluginConf(\Jelix\IniFile\IniModifierInterface $config, $pluginName)
    {
        return $this->globalSetup->getCoordPluginConf($config, $pluginName);
    }

    /**
     * declare web assets into the main configuration
     * @param string $name the name of webassets
     * @param array $values should be an array with one or more of these keys 'css' (array), 'js'  (array), 'require' (string)
     * @param string $collection the name of the webassets collection
     * @param bool $force
     */
    protected final function declareGlobalWebAssets($name, array $values, $collection, $force)
    {
        $config = $this->getSingleConfigIni();
        $this->globalSetup->declareWebAssetsInConfig($config, $name, $values, $collection, $force);
    }

    /**
     * remove web assets from the main configuration
     *
     * @param string $name the name of webassets
     * @param string $collection the name of the webassets collection
     */
    protected final function removeGlobalWebAssets($name, $collection)
    {
        $config = $this->getSingleConfigIni();
        $this->globalSetup->removeWebAssetsFromConfig($config, $name, $collection);
    }

    protected final function getProfilesIni() {
        return $this->globalSetup->getProfilesIni();
    }


}