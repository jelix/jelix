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

    protected $useDatabase = true;

    public function setEntryPoint($ep, $config, $dbProfile) {
        $authconfig = $config->getValue('auth','coordplugins');
        if ($authconfig) {
            $conf = new jIniFileModifier(JELIX_APP_CONFIG_PATH.$authconfig);
            if ($conf->getValue('driver'))
                $dbProfile = $conf->getValue('profile', 'Db');
        }

        parent::setEntryPoint($ep, $config, $dbProfile);

        return md5('-' . $authconfig.'-'.$this->dbProfile);
    }
}