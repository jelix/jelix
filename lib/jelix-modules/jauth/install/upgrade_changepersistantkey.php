<?php
/**
* @package     jelix
* @subpackage  jauth module
* @author      Laurent Jouanneau
* @copyright   2016 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

class jauthModuleUpgrader_changepersistantkey extends jInstallerModule {

    public $targetVersions = array('1.3.0', '1.7.0-beta.1');
    public $date = '2016-05-21 23:55';

    protected static $key = null;

    function install() {

        if (self::$key === null) {
            self::$key = jAuth::getRandomPassword(30, true);
        }

        $conf = $this->getConfigIni()->getValue('auth', 'coordplugins');
        if ($conf != '1') {
            $conff = jApp::varConfigPath($conf);
            if (file_exists($conff)) {
                $ini = new \Jelix\IniFile\IniModifier($conff);
                $ini->removeValue('persistant_crypt_key');
                $ini->save();
            }
        }

        $localConfigIni = $this->getLocalConfigIni();
        $localConfigIni->getMaster()->setValue('persistant_crypt_key', self::$key, 'coordplugin_auth');
    }
}
