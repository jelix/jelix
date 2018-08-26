<?php
/**
 * @package    jelix-modules
 * @subpackage jelix-module
 * @author      Laurent Jouanneau
 * @copyright   2018 Laurent Jouanneau
 * @link        http://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

class jelixModuleConfigurator extends \Jelix\Installer\Module\Configurator {

    public function getDefaultParameters() {
        return array('wwwfiles'=>'');
    }


    public function askParameters()
    {

        $this->parameters['wwwfiles'] = $this->askInChoice(
            "How to install jelix-www files?".
            "\ncopy: will be copied int the www/ directory".
            "\nfilelink: a file system link into the www/ directory will point to the jelix-www directory".
            "\nvhost: you will configure your web server to set an alias to the jelix-www directory"
            ,
            array('copy', 'vhost', 'filelink'), 0
            );

        $configIni = $this->getConfigIni();
        $jelixWWWPath = $configIni->getValue('jelixWWWPath', 'urlengine');
        $jelixWWWPath = $this->askInformation('Web path to the content of jelix-www?', $jelixWWWPath);
        if ($jelixWWWPath == '') {
            $jelixWWWPath = 'jelix/';
        }
        $configIni->setValue('jelixWWWPath', $jelixWWWPath, 'urlengine');

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