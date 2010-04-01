<?php
/**
* @package     jelix
* @subpackage  jauthdb module
* @author      Laurent Jouanneau
* @contributor
* @copyright   2010 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

require_once(dirname(__FILE__).'/_installclass.php');

class jauthdbModuleUpgrader_newdao extends jauthdbModuleInstallerBase {

    function install() {

        $authconfig = $this->config->getValue('auth','coordplugins');

        if ($authconfig) {

            $conf = new jIniFileModifier(JELIX_APP_CONFIG_PATH.$authconfig);
            $driver = $conf->getValue('driver');

            if ($driver == '') {
                $driver = 'Db';
                $conf->setValue('driver','Db');
                $conf->setValue('dao','jauthdb~jelixuser', 'Db');
                $conf->save();
            }
            else if ($driver != 'Db') {
                return;
            }

            $daoName = $conf->getValue('dao', 'Db');
            if ($daoName == 'jauth~jelixuser') {
                $conf->setValue('dao', 'jauthdb~jelixuser','Db');
                $conf->save();
            }
        }
    }
}