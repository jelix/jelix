<?php

/**
* @package     jelix
* @subpackage  core
* @author      Laurent Jouanneau
* @copyright   2012 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

class jelixModuleUpgrader_modulejacl2 extends jInstallerModule2 {

    public $targetVersions = array('1.5a1.2504');
    public $date = '2012-09-19 11:05';

    function installEntrypoint(\Jelix\Installer\EntryPoint $entryPoint) {
        $this->_upgradeconf('jacl2', $entryPoint);
        $this->_upgradeconf('jacl', $entryPoint);
    }
    
    protected function _upgradeconf($module, \Jelix\Installer\EntryPoint $entryPoint) {
        // move options from jacl2 configuration file to global configuration

        $conf = null;
        // get from entrypoint config
        $globalConf = $entryPoint->getConfigIni();
        $aclConfig = $this->getCoordPluginConf($globalConf['entrypoint'], $module);
        if (!$aclConfig) {
            $globalConf = $this->getLocalConfigIni();
            $aclConfig = $this->getCoordPluginConf($globalConf['local'], $module);
            if (!$aclConfig) {
                $globalConf = $this->getConfigIni();
                $aclConfig = $this->getCoordPluginConf($globalConf['main'], $module);
                if (!$aclConfig) {
                    return;
                }
            }
        }

        list($conf, $section) = $aclConfig;
        if ($section !== 0) {
            // $conf is the global conf file
            return;
        }

        $message = $conf->getValue('error_message');
        if ($message == "jelix~errors.acl.action.right.needed") {
            $message = $module."~errors.action.right.needed";
        }
        $onerror = $conf->getValue('on_error');
        $on_error_action = $conf->getValue('on_error_action');

        $globalConf->setValue($module, '1', 'coordplugins');
        $globalConf->setValue('on_error', $onerror, 'coordplugin_'.$module);
        $globalConf->setValue('error_message', $message, 'coordplugin_'.$module);
        $globalConf->setValue('on_error_action', $on_error_action, 'coordplugin_'.$module);
        $globalConf->save();
    }
}