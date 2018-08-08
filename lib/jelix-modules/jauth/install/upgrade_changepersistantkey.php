<?php
/**
* @package     jelix
* @subpackage  jauth module
* @author      Laurent Jouanneau
* @copyright   2016-2018 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

class jauthModuleUpgrader_changepersistantkey extends jInstallerModule2 {

    protected $targetVersions = array('1.3.0', '1.7.0-beta.1');
    protected $date = '2016-05-21 23:55';

    function install() {

        foreach($this->globalSetup->getEntryPointList() as $entryPoint) {
            $config = $entryPoint->getConfigIni();
            $authconfig = $this->getCoordPluginConf($config, 'auth');
            if ($authconfig) {
                list($conf, $section) = $authconfig;
                $conf->removeValue('persistant_crypt_key', $section);
                $conf->save();
            }
        }

        $localConfigIni = $this->getLocalConfigIni();
        $key = jAuth::getRandomPassword(30, true);
        $localConfigIni->setValue('persistant_crypt_key', $key, 'coordplugin_auth');
    }
}
