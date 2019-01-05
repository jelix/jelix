<?php
/**
 * @package    jelix-modules
 * @subpackage jelix-module
 * @author      Laurent Jouanneau
 * @copyright   2018 Laurent Jouanneau
 * @link        http://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

require(__DIR__.'/WebAssetsUpgrader.php');
require(__DIR__.'/UrlEngineUpgrader.php');

class jelixModuleConfigurator extends \Jelix\Installer\Module\Configurator {

    public function getDefaultParameters() {
        return array('wwwfiles'=>'');
    }

    public function configure(\Jelix\Installer\Module\API\ConfigurationHelpers $helpers)
    {
        $this->migrate($helpers);
        $cli = $helpers->cli();
        $this->parameters['wwwfiles'] = $cli->askInChoice(
            "How to install jelix-www files?".
            "\n   copy: will be copied into the www/ directory".
            "\n   filelink: a symbolic link into the www/ directory will point to the lib/jelix-www directory".
            "\n   vhost: you will configure your web server to set an alias to the lib/jelix-www directory"
            ,
            array('copy', 'vhost', 'filelink'), $this->parameters['wwwfiles']
        );

        $configIni = $helpers->getConfigIni();
        $jelixWWWPath = $configIni->getValue('jelixWWWPath', 'urlengine');
        $jelixWWWPath = $cli->askInformation('Web path to the content of lib/jelix-www?', $jelixWWWPath);
        if ($jelixWWWPath == '') {
            $jelixWWWPath = 'jelix/';
        }

        $configIni->setValue('jelixWWWPath', $jelixWWWPath, 'urlengine');


        $storage = $configIni->getValue("storage", "sessions");

        if ($cli->askConfirmation('Do you want to store sessions into a database?', $storage == 'dao')) {
            $configIni->setValue('storage', 'dao', 'session');
            if (!$configIni->getValue("dao_selector", "sessions")) {
                $dao = $cli->askInformation('Indicate the dao selector to store session data', "jelix~jsession");
                $configIni->setValue('dao_selector', $dao,  'session');
            }
        }
        else if ($cli->askConfirmation('Do you want to store sessions as files into a specific directory?', $storage == 'files')) {
            $configIni->setValue('storage', 'files', 'session');
            $path = $cli->askInformation('Indicate the path of the directory', $configIni->getValue("files_path", "sessions"));
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

    public function localConfigure(\Jelix\Installer\Module\API\LocalConfigurationHelpers $helpers)
    {
        $this->migrateLocal($helpers);
        $cli = $helpers->cli();
        if ($cli->askConfirmation('Do you want to configure the default access to the database?')) {
            $profilesIni = $helpers->getProfilesIni();
            $profile = null;
            $defaultAliasProfile = $profilesIni->getValue('default', 'jdb');
            if ($defaultAliasProfile) {
                $profile = $profilesIni->getValues('jdb:'.$defaultAliasProfile);
            }
            else {
                $profile = $profilesIni->getValues('jdb:default');
            }

            $profile = $cli->askDbProfile($profile);
            if ($defaultAliasProfile) {
                $helpers->declareDbProfile($defaultAliasProfile, $profile, true);
            }
            else {
                $helpers->declareDbProfile('default', $profile, true);
            }
        }
    }

    protected function migrate(\Jelix\Installer\Module\API\ConfigurationHelpers $helpers) {
        if (!$helpers->forLocalConfiguration()) {
            $mainConfig = $helpers->getConfigIni();
            $webassets = new WebAssetsUpgrader($mainConfig);
            foreach($helpers->getEntryPointsList() as $entryPoint) {
                $epConfig = $entryPoint->getConfigIni();
                $webassets->changeConfig($epConfig, $epConfig['entrypoint']);
            }
            $webassets = new WebAssetsUpgrader($mainConfig['default']);
            $webassets->changeConfig($mainConfig, $mainConfig['main']);

            foreach($helpers->getEntryPointsList() as $entryPoint) {
                $upgraderUrl = new UrlEngineUpgrader($entryPoint->getConfigIni(),
                    $entryPoint->getEpId(),
                    $entryPoint->getUrlMap());
                $upgraderUrl->upgrade();
            }

            foreach($helpers->getEntryPointsList() as $entryPoint) {
                $upgraderUrl = new UrlEngineUpgrader($entryPoint->getConfigIni(),
                    $entryPoint->getEpId(),
                    $entryPoint->getUrlMap());
                $upgraderUrl->cleanConfig($helpers->getConfigIni()['main']);
            }
        }
    }


    protected function migrateLocal(\Jelix\Installer\Module\API\LocalConfigurationHelpers $helpers) {
        $mainConfig = $helpers->getConfigIni();
        $webassets = new WebAssetsUpgrader($mainConfig);
        foreach($helpers->getEntryPointsList() as $entryPoint) {
            $epConfig = $entryPoint->getConfigIni();
            $webassets->changeConfig($epConfig, $epConfig['localentrypoint']);
        }

        $ini = $helpers->getProfilesIni();
        foreach($helpers->getEntryPointsList() as $entryPoint) {
            foreach($ini->getSectionList() as $section) {
                if (strpos($section, 'jkvdb:') === 0) {
                    $driver = $ini->getValue('driver', $section);
                    if ($driver == 'redis' &&
                        isset ($entryPoint->getConfigObj()->_pluginsPathList_kvdb['redis_php'])
                    ) {
                        $ini->setValue('driver', 'redis_php', $section);
                    }
                }
                else if (strpos($section, 'jcache:') === 0) {
                    $driver = $ini->getValue('driver', $section);
                    if ($driver == 'redis' &&
                        isset ($entryPoint->getConfigObj()->_pluginsPathList_cache['redis_php'])
                    ) {
                        $ini->setValue('driver', 'redis_php', $section);
                    }
                }
                // profiles.ini.php change mysql driver from "mysql" to "mysqli"
                else if (strpos($section, 'jdb:') === 0) {
                    $driver = $ini->getValue('driver', $section);
                    if ($driver == 'mysql') {
                        $ini->setValue('driver', 'mysqli', $section);
                    }
                }
            }
        }
    }
}