<?php
/**
 * @package    jelix-modules
 * @subpackage jelix-module
 *
 * @author      Laurent Jouanneau
 * @copyright   2018-2019 Laurent Jouanneau
 *
 * @see        http://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */
class jelixModuleConfigurator extends \Jelix\Installer\Module\Configurator
{
    public function getDefaultParameters()
    {
        return array('wwwfiles' => '');
    }

    public function configure(Jelix\Installer\Module\API\ConfigurationHelpers $helpers)
    {
        $this->migrate($helpers);
        $cli = $helpers->cli();
        $this->parameters['wwwfiles'] = $cli->askInChoice(
            'How to install the web assets of Jelix, (the jelix-www files)?'.
            "\n   copy: will be copied into the www/ directory".
            "\n   symlink: a symbolic link into the www/ directory will point to the lib/jelix-www directory".
            "\n   vhost: you have to configure your web server to set an alias to the lib/jelix-www directory".
            "\n          and the installer will remove symlink or existing assets directory".
            "\n   nosetup: nothing will be done, you have to install assets yourself as you want.",
            array('copy', 'vhost', 'symlink', 'nosetup'),
            $this->parameters['wwwfiles']
        );

        $configIni = $helpers->getConfigIni();
        $jelixWWWPath = $configIni->getValue('jelixWWWPath', 'urlengine');
        $jelixWWWPath = $cli->askInformation('Web path to the content of lib/jelix-www?', $jelixWWWPath);
        if ($jelixWWWPath == '') {
            $jelixWWWPath = 'jelix/';
        }

        $configIni->setValue('jelixWWWPath', $jelixWWWPath, 'urlengine');

        $storage = $configIni->getValue('storage', 'sessions');

        if ($cli->askConfirmation('Do you want to store sessions into a database?', $storage == 'dao')) {
            $configIni->setValue('storage', 'dao', 'sessions');
            if (!$configIni->getValue('dao_selector', 'sessions')) {
                $dao = $cli->askInformation('Indicate the dao selector to store session data', 'jelix~jsession');
                $configIni->setValue('dao_selector', $dao, 'sessions');
            }
        } elseif ($cli->askConfirmation('Do you want to store sessions as files into a specific directory?', $storage == 'files')) {
            $configIni->setValue('storage', 'files', 'sessions');
            $path = $cli->askInformation('Indicate the path of the directory', $configIni->getValue('files_path', 'sessions'));
            if ($path) {
                $configIni->setValue('storage', 'files', 'sessions');
                $configIni->setValue('files_path', $path, 'sessions');
            } else {
                $configIni->setValue('storage', '', 'sessions');
            }
        } else {
            $configIni->setValue('storage', '', 'sessions');
        }
    }

    public function localConfigure(Jelix\Installer\Module\API\LocalConfigurationHelpers $helpers)
    {
        $this->migrateLocal($helpers);
        $cli = $helpers->cli();

        $profilesIni = $helpers->getProfilesIni();
        $defaultAliasProfile = $profilesIni->getValue('default', 'jdb');
        if ($defaultAliasProfile) {
            $profile = $profilesIni->getValues('jdb:'.$defaultAliasProfile);
        } else {
            $profile = $profilesIni->getValues('jdb:default');
        }

        if ((!$profile || !isset($profile['driver']) || $profile['driver'] == '' || !isset($profile['database']) || $profile['database'] == '' )
            && $cli->askConfirmation('Do you want to configure the default access to the database?')
        ) {
            $profile = $cli->askDbProfile($profile);
            if ($defaultAliasProfile) {
                $helpers->declareDbProfile($defaultAliasProfile, $profile, true);
            } else {
                $helpers->declareDbProfile('default', $profile, true);
            }
        }
    }

    protected function migrate(Jelix\Installer\Module\API\ConfigurationHelpers $helpers)
    {
        $this->migrateConfig($helpers->getConfigIni()['main']);
        foreach ($helpers->getEntryPointsList() as $entryPoint) {
            $this->migrateConfig($entryPoint->getConfigIni()['entrypoint']);
        }
    }

    protected function migrateLocal(Jelix\Installer\Module\API\LocalConfigurationHelpers $helpers)
    {
        $ini = $helpers->getProfilesIni();
        foreach ($helpers->getEntryPointsList() as $entryPoint) {
            $this->migrateConfig($entryPoint->getConfigIni()['localentrypoint'], true);

            foreach ($ini->getSectionList() as $section) {
                if (strpos($section, 'jkvdb:') === 0) {
                    $driver = $ini->getValue('driver', $section);
                    if ($driver == 'redis'
                        && isset($entryPoint->getConfigObj()->_pluginsPathList_kvdb['redis_php'])
                    ) {
                        $ini->setValue('driver', 'redis_php', $section);
                    }
                } elseif (strpos($section, 'jcache:') === 0) {
                    $driver = $ini->getValue('driver', $section);
                    if ($driver == 'redis'
                        && isset($entryPoint->getConfigObj()->_pluginsPathList_cache['redis_php'])
                    ) {
                        $ini->setValue('driver', 'redis_php', $section);
                    }
                }
                // profiles.ini.php change mysql driver from "mysql" to "mysqli"
                elseif (strpos($section, 'jdb:') === 0) {
                    $driver = $ini->getValue('driver', $section);
                    if ($driver == 'mysql') {
                        $ini->setValue('driver', 'mysqli', $section);
                    }
                }
            }
        }
    }

    /**
     * @param \Jelix\IniFile\IniReaderInterface $ini
     * @param mixed                             $forLocal
     */
    protected function migrateConfig($ini, $forLocal = false)
    {
        if (!$ini instanceof \Jelix\IniFile\IniModifierInterface) {
            echo 'ERROR '.$ini->getFileName()." not allowed to be writable by the Jelix configurator\n";
        }

        $val = $ini->getValue('notfoundAct', 'urlengine');
        if ($val !== null) {
            if (!$forLocal) {
                // we don't remove old parameter, to support the case where
                // the mainconfig is not updated in instances
                $ini->removeValue('notfoundAct', 'urlengine');
            }
            $ini->setValue('notFoundAct', $val, 'urlengine');
        }
        $ini->save();
    }
}
