<?php
/**
* @package     jelix
* @subpackage  jauth module
* @author      Laurent Jouanneau
* @copyright   2016-2018 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

class jauthModuleUpgrader_newencryption extends jInstallerModule2 {

    protected $targetVersions = array('1.7.0-beta.1');
    protected $date = '2016-05-22 14:34';

    function install() {

        foreach($this->getEntryPointsList() as $entryPoint) {
            $authConfig = $this->getCoordPluginConf($entryPoint->getConfigIni(), 'auth');
            if (!$authConfig) {
                continue;
            }
            list($conf, $section) = $authConfig;
            $conf->removeValue('persistant_crypt_key', $section);
            $conf->save();
        }

        $cryptokey = \Defuse\Crypto\Key::createNewRandomKey();
        $key = $cryptokey->saveToAsciiSafeString();
        $localConfigIni = $this->getLocalConfigIni();
        $localConfigIni->removeValue('persistant_crypt_key', 'coordplugin_auth');
        $localConfigIni->setValue('persistant_encryption_key', $key, 'coordplugin_auth');
    }
}
