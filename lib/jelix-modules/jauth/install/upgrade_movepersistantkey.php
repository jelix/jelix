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

    public $targetVersions = array('1.3.1', '1.7.0-beta.2');
    public $date = '2018-05-28 22:20';

    function installEntrypoint(jInstallerEntryPoint2 $entryPoint) {
        $liveConfigIni = $this->getLiveConfigIni();
        $localConfigIni = $this->getLocalConfigIni();
        $key = $localConfigIni->getValue('persistant_encryption_key', 'coordplugin_auth');
        if ($key != 'exampleOfCryptKey' && $key != '') {
            $localConfigIni->removeValue('persistant_encryption_key', 'coordplugin_auth');
            $liveConfigIni->setValue('persistant_encryption_key', $key, 'coordplugin_auth');
        }
    }
}
