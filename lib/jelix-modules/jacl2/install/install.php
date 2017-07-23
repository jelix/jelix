<?php
/**
* @package     jacl2
* @author      Laurent Jouanneau
* @contributor
* @copyright   2012 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

class jacl2ModuleInstaller extends jInstallerModule2 {
    function installEntrypoint(\Jelix\Installer\EntryPoint $entryPoint) {
        if ($entryPoint->firstConfExec()) {
            $conf = $entryPoint->getConfigIni();
            if (null == $conf->getValue('jacl2', 'coordplugins')) {
                $conf->setValue('jacl2', '1', 'coordplugins');
                if ($entryPoint->getType() != 'classic') {
                    $onerror = 1;
                }
                else {
                    $onerror = 2;
                }
                $conf->setValue('on_error', $onerror, 'coordplugin_jacl2');
                $conf->setValue('error_message', "jacl2~errors.action.right.needed", 'coordplugin_jacl2');
                $conf->setValue('on_error_action', "jelix~error:badright", 'coordplugin_jacl2');
            }
        }
    }
}
