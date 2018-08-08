<?php
/**
* @package     jelix
* @subpackage  jauthdb_admin module
* @author      Laurent Jouanneau
* @contributor
* @copyright   2010-2018 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/


class jauthdb_adminModuleInstaller extends jInstallerModule2 {
    function install()
    {
        foreach($this->globalSetup->getEntryPointList() as $entrypoint) {
            if ($this->setEpConf($entrypoint)) {
                break;
            }
        }
    }

    function setEpConf(jInstallerEntryPoint2 $entryPoint) {
        $config = $entryPoint->getConfigIni();
        $authconfig = $this->getCoordPluginConf($config, 'auth');

        if ($authconfig &&  $entryPoint->getType() != 'cmdline') {
            list($conf, $section) = $authconfig;
            if ($section === 0) {
                $section_db = 'Db';
            }
            else {
                $section_db = 'auth_db';
            }
            $daoName = $conf->getValue('dao', $section_db);
            $formName = $conf->getValue('form', $section_db);
            if ($daoName == 'jauthdb~jelixuser' && $formName == '') {
                $conf->setValue('form','jauthdb_admin~jelixuser', $section_db);
                $conf->save();
            }
            return true;
        }
        return false;
    }
}