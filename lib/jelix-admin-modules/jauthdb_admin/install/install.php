<?php
/**
* @package     jelix
* @subpackage  jauthdb_admin module
* @author      Laurent Jouanneau
* @contributor
* @copyright   2010-2012 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/


class jauthdb_adminModuleInstaller extends jInstallerModule {

    function install() {
        $config = $this->getConfigIni();
        $authconfig = $this->getCoordPluginConf($config, 'auth');

        if ($authconfig &&  $this->entryPoint->type != 'cmdline' && $this->firstExec('authdbadmin')) {
            list($conf, $section) = $authconfig;
            if ($section === 0) {
                $section_db = 'Db';
            }
            else {
                $section_db = 'auth_db';
            }
            $driver = $conf->getValue('driver', $section);
            $daoName = $conf->getValue('dao', $section_db);
            $formName = $conf->getValue('form', $section_db);
            if ($driver == 'Db' && $daoName == 'jauthdb~jelixuser' && $formName == '') {
                $conf->setValue('form','jauthdb_admin~jelixuser', $section_db);
                $conf->save();
            }
        }
    }
}