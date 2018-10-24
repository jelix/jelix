<?php
/**
 * @package     jelix
 * @subpackage  jauthdb_admin
 * @author      Laurent Jouanneau
 * @copyright   2018 Laurent Jouanneau
 * @link        http://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

class jauthdb_adminModuleConfigurator extends \Jelix\Installer\Module\Configurator {

    public function configure() {
        foreach($this->getEntryPointsList() as $entrypoint) {
            if ($this->setEpConf($entrypoint)) {
                break;
            }
        }
    }

    protected function setEpConf(\Jelix\Installer\EntryPointConfigurator $entryPoint) {
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