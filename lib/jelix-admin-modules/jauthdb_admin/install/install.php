<?php
/**
* @package     jelix
* @subpackage  jauthdb_admin module
* @author      Laurent Jouanneau
* @contributor
* @copyright   2010 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/


class jauthdb_adminModuleInstaller extends jInstallerModule {

   public function setEntryPoint($ep, $config, $dbProfile) {
        parent::setEntryPoint($ep, $config, $dbProfile);
        return md5('-' . $this->config->getValue('auth','coordplugins'));
    }

    function install() {

        $authconfig = $this->config->getValue('auth','coordplugins');

        if ($authconfig && $this->entryPoint->type != 'cmdline') {

            $conf = new jIniFileModifier(JELIX_APP_CONFIG_PATH.$authconfig);
            $driver = $conf->getValue('driver');
            $daoName = $conf->getValue('dao', 'Db');
            $formName = $conf->getValue('form', 'Db');
            if ($driver == 'Db' && $daoName == 'jauthdb~jelixuser' && $formName == '') {
                $conf->setValue('form','jauthdb_admin~jelixuser', 'Db');
                $conf->save();
            }
        }
    }
}