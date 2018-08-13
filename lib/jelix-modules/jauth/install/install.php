<?php
/**
* @package     jelix
* @subpackage  jauth module
* @author      Laurent Jouanneau
* @contributor Julien Issler
* @copyright   2009-2018 Laurent Jouanneau
* @copyright   2011 Julien Issler
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

class jauthModuleInstaller extends jInstallerModule2 {

    function install() {

        $cryptokey = \Defuse\Crypto\Key::createNewRandomKey();
        $key = $cryptokey->saveToAsciiSafeString();
        $this->getLiveConfigIni()->setValue('persistant_encryption_key', $key, 'coordplugin_auth');


        $authconfigMaster = $this->getConfigIni()->getValue('auth','coordplugins');

        $authConfigList = [];
        foreach($this->getEntryPointsList() as $entryPoint) {
            $authconfig = $entryPoint->getConfigIni()->getValue('auth', 'coordplugins');

            $forWS = (in_array($entryPoint->getType(), array('json', 'jsonrpc', 'soap', 'xmlrpc')));

            if (!$authconfig || ($forWS && $authconfig == $authconfigMaster)) {

                if ($forWS) {
                    $pluginIni = 'authsw.coord.ini.php';
                } else {
                    $pluginIni = 'auth.coord.ini.php';
                }

                $authconfig = dirname($entryPoint->getConfigFile()) . '/' . $pluginIni;

                if (!isset($authConfigList[$authconfig])) {
                    $authConfigList[$authconfig] = true;
                    // no configuration, let's install the plugin for the entry point
                    $entryPoint->getConfigIni()->setValue('auth', $authconfig, 'coordplugins');
                    if (!file_exists(jApp::appConfigPath($authconfig))) {
                        $this->copyFile('var/config/' . $pluginIni, jApp::appConfigPath($authconfig));
                    }
                }
            }
        }

    }
}