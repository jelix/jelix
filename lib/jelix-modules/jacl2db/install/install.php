<?php
/**
* @package     jelix
* @subpackage  jacl2db module
* @author      Laurent Jouanneau
* @contributor
* @copyright   2009-2010 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/


class jacl2dbModuleInstaller extends jInstallerModule {

    public function setEntryPoint($ep, $config, $dbProfile) {
        parent::setEntryPoint($ep, $config, 'jacl2_profile');
        return md5($ep->configFile);
    }

    function install() {
        if ($this->entryPoint->type == 'cmdline')
            return;

        $aclconfig = $this->config->getValue('jacl2','coordplugins');

        if (!$aclconfig) {

            $pluginIni = 'jacl2.coord.ini.php';            
            $configDir = dirname($this->entryPoint->configFile).'/';

            // no configuration, let's install the plugin for the entry point
            $this->config->setValue('jacl2', $configDir.$pluginIni,'coordplugins');

            if (!file_exists(JELIX_APP_CONFIG_PATH.$configDir.$pluginIni)) {
                $this->copyFile('var/config/'.$pluginIni , JELIX_APP_CONFIG_PATH.$configDir.$pluginIni);
            }
            $aclconfig = $configDir.$pluginIni;
        }

        if (in_array($this->entryPoint->type, array('json', 'jsonrpc', 'soap', 'xmlrpc'))) {
            $cf = new jIniFileModifier(JELIX_APP_CONFIG_PATH.$aclconfig);
            $cf->setValue('on_error', 1);
            $cf->save();
        }

        $this->declareDbProfile('jacl2_profile', null, false);

        $this->config->setValue('driver','db','acl2');
        $this->execSQLScript('install_jacl2.schema', 'jacl2_profile');
        $this->execSQLScript('install_jacl2.data', 'jacl2_profile');
    }
}