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
     * @var string name of the component
     */
    private $componentName;

    /**
     * @var string name of the installer
     */
    private $name;


    /**
     * The path of the module
     * @var string
     */
    private $path;

    /**
     * global setup
     * @var \Jelix\Installer\GlobalSetup
     */
    private $globalSetup;

    /**
     * parameters for the installer, indicated in the configuration file or
     * dynamically, by a launcher in a command line for instance.
     * @var array
     */
    protected $parameters = array();

    final function getName() {
        return $this->name;
    }

    final function getPath() {
        return $this->path;
    }

    /**
     * @inheritdoc
     */
    public final function setParameters($parameters) {
        $this->parameters = $parameters;
    }


    final function getParameter($name) {
        if (isset($this->parameters[$name]))
            return $this->parameters[$name];
        else
            return null;
    }

    /**
     * @inheritdoc
     */
    public final function getParameters() {
        return $this->parameters;
    }

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
        $config = $this->getConfigIni();
        $this->globalSetup->declareWebAssetsInConfig($config['main'], $name, $values, $collection, $force);
    }

    /**
     * remove web assets from the main configuration
     *
     * @param string $name the name of webassets
     * @param string $collection the name of the webassets collection
     */
    protected final function removeGlobalWebAssets($name, $collection)
    {
        $config = $this->globalSetup->getConfigIni();
        $this->globalSetup->removeWebAssetsFromConfig($config['main'], $name, $collection);
    }

    protected final function getProfilesIni() {
        return $this->globalSetup->getProfilesIni();
    }


}