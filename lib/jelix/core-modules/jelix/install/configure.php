<?php
/**
 * @package    jelix-modules
 * @subpackage jelix-module
 * @author      Laurent Jouanneau
 * @copyright   2018 Laurent Jouanneau
 * @link        http://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

class jelixModuleConfigurator extends jInstallerModuleConfigurator {

    public function askParameters()
    {
        $configIni = $this->getConfigIni();
        if ($this->askConfirmation('Do you want to store sessions into a database?', false)) {
            $configIni->setValue('storage', 'dao', 'session');
            if (!$configIni->getValue("dao_selector", "sessions")) {
                $dao = $this->askInformation('Indicate the dao selector to store session data', "jelix~jsession");
                $configIni->setValue('dao_selector', $dao,  'session');
            }
        }
        else if ($this->askConfirmation('Do you want to store sessions as files into a specific directory?', false)) {
            $configIni->setValue('storage', 'files', 'session');
            $path = $this->askInformation('Indicate the path of the directory', $configIni->getValue("files_path", "sessions"));
            if ($path) {
                $configIni->setValue('storage', 'files', 'session');
                $configIni->setValue('files_path', $path,  'session');
            }
            else {
                $configIni->setValue('storage', '', 'session');
            }
        }
        else {
            $configIni->setValue('storage', '', 'session');
        }
    }

    public function configure() {

    }

}