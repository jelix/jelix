<?php
/**
* @package     jelix
* @subpackage  jauth module
* @author      Laurent Jouanneau
* @copyright   2018 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

class jauthModuleUpgrader_movepersistantkey extends jInstallerModule2 {

    protected $targetVersions = array('1.3.1', '1.7.0-beta.2');
    protected $date = '2018-05-28 22:20';

    function install() {

        // remove deprecated key from all auth.coord.ini.php
        foreach($this->getEntryPointsList() as $entryPoint) {
            $config = $entryPoint->getAppConfigIni();
            $authconfig = $this->getCoordPluginConf($config, 'auth');
            if ($authconfig) {
                list($conf, $section) = $authconfig;
                $conf->removeValue('persistant_crypt_key', $section);
                $conf->save();
            }
        }

        $localConfigIni = $this->getLocalConfigIni();

        // remove deprecated key from localconfig.ini.php
        $key = $localConfigIni->getValue('persistant_encryption_key', 'coordplugin_auth');
        if ($key !== null) {
            $localConfigIni->removeValue('persistant_encryption_key', 'coordplugin_auth');
        }
        $key = $localConfigIni->getValue('persistant_crypt_key', 'coordplugin_auth');
        if ($key !== null) {
            $localConfigIni->removeValue('persistant_crypt_key', 'coordplugin_auth');
        }

        // setup new key on liveconfig.ini.php
        $liveConfigIni = $this->getLiveConfigIni();
        $key = $liveConfigIni->getValue('persistant_encryption_key', 'coordplugin_auth');
        if ($key === null) {
            $cryptokey = \Defuse\Crypto\Key::createNewRandomKey();
            $key = $cryptokey->saveToAsciiSafeString();

            $liveConfigIni->setValue('persistant_encryption_key', $key, 'coordplugin_auth');
        }
    }
}
