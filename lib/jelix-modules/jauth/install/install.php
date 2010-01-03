<?php
/**
* @package     jelix
* @subpackage  jauth module
* @author      Laurent Jouanneau
* @contributor
* @copyright   2009-2010 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

class jauthModuleInstaller extends jInstallerModule {

    public function setEntryPoint($ep, $config, $dbProfile) {
        parent::setEntryPoint($ep, $config, $dbProfile);
        return md5($ep->configFile);
    }

    function install() {
        $authconfig = $this->config->getValue('auth','coordplugins');

        if (!$authconfig) {
            if ($this->entryPoint->type == 'cmdline') {
                return;
            }
            
            if ($this->entryPoint->type == 'classic') {
                $pluginIni = 'auth.coord.ini.php';
            }
            else {
                $pluginIni = 'authsw.coord.ini.php';
            }
            
            $configDir = dirname($this->entryPoint->configFile).'/';

            // no configuration, let's install the plugin for the entry point
            $this->config->setValue('auth', $configDir.$pluginIni, 'coordplugins');

            if (!file_exists(JELIX_APP_CONFIG_PATH.$configDir.$pluginIni)) {
                $this->copyFile('var/config/'.$pluginIni, JELIX_APP_CONFIG_PATH.$configDir.$pluginIni);
            }
        }
    }
}