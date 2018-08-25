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

        if ($this->askConfirmation('Do you want to configure the access to the database?')) {
            $profilesIni = $this->getProfilesIni();
            $profile = null;
            $defaultAliasProfile = $profilesIni->getValue('default', 'jdb');
            if ($defaultAliasProfile) {
                $profile = $profilesIni->getValues('jdb:'.$defaultAliasProfile);
            }
            else {
                $profile = $profilesIni->getValues('jdb:default');
            }

            $profile = $this->askDbProfile($profile);
            if ($defaultAliasProfile) {
                $this->declareDbProfile($defaultAliasProfile, $profile, true);
            }
            else {
                $this->declareDbProfile('default', $profile, true);
            }
        }

        $configIni = $this->getConfigIni();

        $storage = $configIni->getValue("storage", "sessions");

        if ($this->askConfirmation('Do you want to store sessions into a database?', $storage == 'dao')) {
            $configIni->setValue('storage', 'dao', 'session');
            if (!$configIni->getValue("dao_selector", "sessions")) {
                $dao = $this->askInformation('Indicate the dao selector to store session data', "jelix~jsession");
                $configIni->setValue('dao_selector', $dao,  'session');
            }
        }
        else if ($this->askConfirmation('Do you want to store sessions as files into a specific directory?', $storage == 'files')) {
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