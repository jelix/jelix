<?php
/**
* @package     jelix
* @subpackage  jauthdb module
* @author      Laurent Jouanneau
* @contributor
* @copyright   2009-2010 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/


class jauthdbModuleInstallerBase extends jInstallerModule {

    public function setEntryPoint($ep, $config, $dbProfile) {
        parent::setEntryPoint($ep, $config, $dbProfile);
        $authconfig = $this->config->getValue('auth','coordplugins');
        $profile = '';
        if ($authconfig) {
            $conf = new jIniFileModifier(JELIX_APP_CONFIG_PATH.$authconfig);
            if ($conf->getValue('driver'))
                $profile = $conf->getValue('profile', 'Db');
        }
        return md5('-' . $authconfig.'-'.$profile);
    }
}