<?php
/**
* @package     jelix
* @subpackage  installer
* @author      Laurent Jouanneau
* @copyright   2008-2018 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

/**
 * A class that does processing to uninstall a module from an instance of
 * the application. A module should have a class that inherits from it
 * in order to remove things from the application.
 *
 * @package     jelix
 * @subpackage  installer
 * @since 1.7
 */
class jInstallerModule2Uninstaller  extends jInstallerModule2Abstract implements jIInstallerComponent2Uninstaller {

    use jInstallerUninstallerHelpersTrait;

    /**
     * @inheritdoc
     */
    function preUninstall() {

    }

    /**
     * @inheritdoc
     */
    function uninstall() {

    }

    /**
     * @inheritdoc
     */
    function postUninstall() {

    }

    /**
     * return the section name of configuration of a plugin for the coordinator
     * or the IniModifier for the configuration file of the plugin if it exists.
     * @param \Jelix\IniFile\IniModifier $config the global configuration content
     * @param string $pluginName
     * @return array|null null if plugin is unknown, else array($iniModifier, $section)
     * @throws Exception when the configuration filename is not found
     */
    public function getCoordPluginConf(\Jelix\IniFile\IniModifierInterface $config, $pluginName)
    {
        return $this->globalSetup->getCoordPluginConf($config, $pluginName);
    }
}

