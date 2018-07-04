<?php
/**
* @package     jelix
* @subpackage  jauth module
* @author      Laurent Jouanneau
* @copyright   2016 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

class jauthModuleUpgrader_newencryption extends jInstallerModule2 {

    public $targetVersions = array('1.7.0-beta.1');
    public $date = '2016-05-22 14:34';

    protected static $key = null;

    function installEntrypoint(jInstallerEntryPoint2 $entryPoint) {

        if (self::$key === null) {
            $cryptokey = \Defuse\Crypto\Key::createNewRandomKey();
            self::$key = $cryptokey->saveToAsciiSafeString();
        }
        $authConfig = $this->getCoordPluginConf($entryPoint->getConfigIni(), 'auth');
        if (!$authConfig) {
            return;
        }
        list($conf, $section) = $authConfig;
        $conf->removeValue('persistant_crypt_key', $section);
        $conf->save();

        $localConfigIni = $this->getLocalConfigIni();
        $localConfigIni->removeValue('persistant_crypt_key', 'coordplugin_auth');
        $localConfigIni->setValue('persistant_encryption_key', self::$key, 'coordplugin_auth');
    }
}
