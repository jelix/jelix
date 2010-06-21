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

    function install() {

        $authconfig = $this->config->getValue('auth','coordplugins');
        $authconfigMaster = $this->config->getValue('auth','coordplugins', null, true);
        $forWS = (in_array($this->entryPoint->type, array('json', 'jsonrpc', 'soap', 'xmlrpc')));

        if (!$authconfig || ($forWS && $authconfig == $authconfigMaster)) {

            if ($forWS) {
                $pluginIni = 'authsw.coord.ini.php';
            }
            else {
                $pluginIni = 'auth.coord.ini.php';
            }

            $authconfig = dirname($this->entryPoint->configFile).'/'.$pluginIni;

            if ($this->firstExec('auth:'.$authconfig)) {
                // no configuration, let's install the plugin for the entry point
                $this->config->setValue('auth', $authconfig, 'coordplugins');
                if (!file_exists(JELIX_APP_CONFIG_PATH.$authconfig)) {
                    $this->copyFile('var/config/'.$pluginIni, JELIX_APP_CONFIG_PATH.$authconfig);
                }
            }
        }
    }
}