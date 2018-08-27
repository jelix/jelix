<?php
/**
* @author      Laurent Jouanneau
* @copyright   2016-2018 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/
namespace Jelix\Installer;

use \Jelix\IniFile\IniModifier;

/**
 * do changes in the application before the installation of modules can be done
 *
 * It is used for directory changes etc.
 *
 * @since 1.7
 */
class Migration {
    
    /**
     * the object responsible of the results output
     * @var Reporter\ReporterInterface
     */
    protected $reporter;

    function __construct(Reporter\ReporterInterface $reporter) {
        $this->reporter = $reporter;
    }

    public function migrate() {
        $this->reporter->start();

        // functions called here should be idempotent
        $this->migrate_1_7_0();

        $this->reporter->end();
    }

    protected function migrate_1_7_0() {
        $this->reporter->message('Start migration to Jelix 1.7.0', 'notice');
        $newConfigPath = \jApp::appConfigPath();
        if (!file_exists($newConfigPath)) {
            $this->reporter->message('Create app/config/', 'notice');
            \jFile::createDir($newConfigPath);
        }

        // move mainconfig.php to app/config/
        if (!file_exists($newConfigPath.'mainconfig.ini.php')) {
            if (!file_exists(\jApp::varConfigPath('mainconfig.ini.php'))) {
                if (!file_exists(\jApp::varConfigPath('defaultconfig.ini.php'))) {
                    throw new \Exception("Migration to Jelix 1.7.0 canceled: where is your mainconfig.ini.php?");
                }
                $this->reporter->message('Move var/config/defaultconfig.ini.php to app/config/mainconfig.ini.php', 'notice');
                rename(\jApp::varConfigPath('defaultconfig.ini.php'), $newConfigPath.'mainconfig.ini.php');
            }
            else {
                $this->reporter->message('Move var/config/mainconfig.ini.php to app/config/', 'notice');
                rename(\jApp::varConfigPath('mainconfig.ini.php'), $newConfigPath.'mainconfig.ini.php');
            }
        }

        $mainConfigIni = new IniModifier(\jApp::appConfigPath('mainconfig.ini.php'));
        $entrypointsConfigIni = array();
        // move entrypoint configs to app/config
        $projectxml = simplexml_load_file(\jApp::appPath('project.xml'));
        // read all entry points data
        foreach ($projectxml->entrypoints->entry as $entrypoint) {
            $configFile = (string)$entrypoint['config'];
            $dest = \jApp::appConfigPath($configFile);
            if (!file_exists($dest)) {
                if (!file_exists(\jApp::varConfigPath($configFile))) {
                    $this->reporter->message("Config file var/config/$configFile indicated in project.xml, does not exist", 'warning');
                    continue;
                }

                $this->reporter->message("Move var/config/$configFile to app/config/", 'notice');
                \jFile::createDir(dirname($dest));
                rename(\jApp::varConfigPath($configFile), $dest);
            }

            $epConfigIni = new IniModifier(\jApp::appConfigPath($configFile));
            $entrypointsConfigIni[] = $epConfigIni;
            $urlFile = $epConfigIni->getValue('significantFile', 'urlengine');
            if ($urlFile != '') {
                if (!file_exists(\jApp::appConfigPath($urlFile)) && file_exists(\jApp::varConfigPath($urlFile))) {
                    $this->reporter->message("Move var/config/$urlFile to app/config/", 'notice');
                    rename(\jApp::varConfigPath($urlFile), \jApp::appConfigPath($urlFile));
                }
            }
            if ($epConfigIni->getValues('modules')) {
                $this->reporter->message('Migrate modules section from app/config/'.$configFile.' content to mainconfig.ini.php', 'notice');
                $this->migrateModulesSection_1_7_0($mainConfigIni, $epConfigIni);
            }
        }

        // move urls.xml to app/config
        $urlFile = $mainConfigIni->getValue('significantFile', 'urlengine');
        if ($urlFile == null) {
            $urlFile = 'urls.xml';
        }
        if (!file_exists(\jApp::appConfigPath($urlFile)) && file_exists(\jApp::varConfigPath($urlFile))) {
            $this->reporter->message("Move var/config/$urlFile to app/config/", 'notice');
            rename(\jApp::varConfigPath($urlFile), \jApp::appConfigPath($urlFile));
        }

        if (!file_exists(\jApp::appPath('app/responses'))) {
            $this->reporter->message("Move responses/ to app/responses/", 'notice');
            rename(\jApp::appPath('responses'), \jApp::appPath('app/responses'));
        }

        if (file_exists(\jApp::varConfigPath('profiles.ini.php'))) {
            $profilesini = new IniModifier(\jApp::varConfigPath('profiles.ini.php'));
            $this->migrateProfilesIni_1_7_0($profilesini);
        }
        if (file_exists(\jApp::varConfigPath('profiles.ini.php.dist'))) {
            $profilesini = new IniModifier(\jApp::varConfigPath('profiles.ini.php.dist'));
            $this->migrateProfilesIni_1_7_0($profilesini);
        }

        // move plugin configuration file to global config
        $this->migrateCoordPluginsConf_1_7_0($mainConfigIni);
        foreach ($entrypointsConfigIni as $epConfig) {
            $this->migrateCoordPluginsConf_1_7_0($epConfig);
        }

        $localConfigPath = \jApp::varConfigPath('localconfig.ini.php');
        $localConfigIni = null;
        if (file_exists($localConfigPath)) {
            $localConfigIni = new IniModifier($localConfigPath);
            $this->migrateCoordPluginsConf_1_7_0($localConfigIni);
        }
        foreach ($projectxml->entrypoints->entry as $entrypoint) {
            $configFile = \jApp::varConfigPath((string)$entrypoint['config']);
            if (file_exists($configFile)) {
                $localEpConfigIni = new IniModifier($configFile);
                $this->migrateCoordPluginsConf_1_7_0($localEpConfigIni);
                if ($localConfigIni && $localEpConfigIni->getValues('modules')) {
                    $this->reporter->message('Migrate modules section from var/config/'.(string)$entrypoint['config'].' content to localconfig.ini.php', 'notice');
                    $this->migrateModulesSection_1_7_0($localConfigIni, $localEpConfigIni);
                }
            }
        }

        $this->migrateAccessValue_1_7_0($mainConfigIni);
        if ($localConfigIni) {
            $this->migrateAccessValue_1_7_0($localConfigIni);
        }

        // migrate installer.ini
        $installerIni = new IniModifier(\jApp::varConfigPath('installer.ini.php'));
        if (!$installerIni->isSection('modules')) {
            $this->reporter->message('Migrate var/config/installer.ini.php content', 'notice');
            $allModules = array();
            foreach ($installerIni->getSectionList() as $section) {
                if ($section == '__modules_data') {
                    continue;
                }
                $modules = array();
                foreach ($installerIni->getValues($section) as $name => $value) {
                    list($module, $param) = explode('.', $name, 2);
                    if (!isset($modules[$module])) {
                        $modules[$module] = array();
                    }
                    $modules[$module][$param] = $value;
                }

                foreach ($modules as $module => $params) {
                    if (
                        isset($allModules[$module]) ||
                        !isset($params['installed']) ||
                        $params['installed'] == 0
                    ) {
                        continue;
                    }
                    $allModules[$module] = $params;
                }
            }

            foreach ($allModules as $module => $params) {
                foreach ($params as $name => $value) {
                    $installerIni->setValue($module . '.' . $name, $value, 'modules');
                }
            }
            foreach ($installerIni->getSectionList() as $section) {
                if ($section == '__modules_data' || $section == 'modules') {
                    continue;
                }
                $installerIni->removeValue('', $section);
            }
            $installerIni->save();
        }

        // set installparameters for the jelix module
        $jelixWWWPath = $mainConfigIni->getValue('jelixWWWPath', 'urlengine');
        $targetPath = \jApp::wwwPath($jelixWWWPath);
        $jelixWWWDirExists = $jelixWWWLinkExists = false;
        if (file_exists($targetPath)) {
            if (is_dir($targetPath)) {
                $wwwfiles = '';
            }
            else if (is_link($targetPath)) {
                $wwwfiles = 'link';
            }
        }
        else {
            // no file, so the path to jelix-www should probably be set into the
            // web server configuration
            $wwwfiles = 'vhost';
        }
        $jelixInstallParams = $originalJelixInstallParams = $mainConfigIni->getValue('jelix.installparam', 'modules');
        if ($jelixInstallParams) {
            $jelixInstallParams = ModuleStatus::unserializeParameters($jelixInstallParams);
            if (!isset($jelixInstallParams['wwwfiles'])) {
                $jelixInstallParams['wwwfiles'] = $wwwfiles;
            }
        }
        else {
            $jelixInstallParams = array('wwwfiles'=>$wwwfiles);
        }
        $jelixInstallParams = ModuleStatus::serializeParameters($jelixInstallParams);
        if ($jelixInstallParams != $originalJelixInstallParams) {
            $this->reporter->message('Update installer parameters for the jelix module', 'notice');
            $mainConfigIni->setValue('jelix.installparam', $jelixInstallParams, 'modules');
        }

        $this->reporter->message('Migration to Jelix 1.7.0 is done', 'notice');
    }

    private function migrateProfilesIni_1_7_0(IniModifier $profilesini) {
        foreach ($profilesini->getSectionList() as $name) {
            // move jSoapClient classmap files
            if (strpos($name, 'jsoapclient:') === 0) {
                $classmapFile = $profilesini->getValue('classmap_file', $name);
                if ($classmapFile != '' &&
                    file_exists(\jApp::varConfigPath($classmapFile))
                ) {
                    $this->reporter->message("Move " . $classmapFile . " to app/config/", 'notice');
                    rename(\jApp::varConfigPath($classmapFile), \jApp::appConfigPath($classmapFile));
                }
            }
            // profiles.ini.php change mysql driver from "mysql" to "mysqli"
            else if (strpos($name, 'jdb:') === 0) {
                $driver = $profilesini->getValue('driver', $name);
                if ($driver == 'mysql') {
                    $this->reporter->message("Profiles.ini: change db driver from mysql to mysqli for ".$name." profile", 'notice');
                    $profilesini->setValue('driver', 'mysqli', $name);
                }
                else if ($driver == 'sqlite') {
                    $this->reporter->message("Profiles.ini: you still use the sqlite driver in the profile ".$name, 'warning');
                    $this->reporter->message("You must convert your databases to sqlite3 and use the sqlite3 driver for jdb", 'warning');
                }
            }
        }
        $profilesini->save();
    }

    protected $allPluginConfigs = array();

    private function migrateCoordPluginsConf_1_7_0(IniModifier $config) {
        $pluginsConf = $config->getValues('coordplugins');
        foreach($pluginsConf as $name => $conf) {
            if (strpos($name, '.') !== false) {
                continue;
            }
            if ($conf == '1' || $conf == '') {
                continue;
            }
            // the configuration value is a filename
            if (!isset($this->allPluginConfigs[$conf])) {
                $confPath = \jApp::varConfigPath($conf);
                if (!file_exists($confPath)) {
                    continue;
                }
                $ini = new IniModifier($confPath);
                $this->allPluginConfigs[$conf] = $ini;
            }
            else {
                $ini = $this->allPluginConfigs[$conf];
            }
            $sections = $ini->getSectionList();
            if (count($sections)) {
                // the file has some section, we cannot merge it into $config as
                // is, so just move it to app/config
                if (file_exists($ini->getFileName())) {
                    $rpath = \Jelix\FileUtilities\Path::shortestPath(\jApp::varConfigPath(), $ini->getFileName());
                    $this->reporter->message("Move plugin conf file ".$rpath." to app/config/", 'notice');
                    rename ($ini->getFileName(), \jApp::appConfigPath($rpath));
                }
                continue;
            }
            $this->reporter->message("Import plugin conf file ".$rpath." into global configuration", 'notice');
            $config->import($ini, $name);
            $config->setValue($name, '1', 'coordplugins');
            unlink($ini->getFileName());
        }
    }

    protected function migrateModulesSection_1_7_0(IniModifier $masterConfigIni, IniModifier $epConfigIni) {
        $modulesParameters = $epConfigIni->getValues('modules');
        if ($modulesParameters) {
            $modules = array();
            foreach($modulesParameters as $name => $value) {
                list($module, $param) = explode('.', $name, 2);
                if (!isset($modules[$module])) {
                    $modules[$module] = array();
                }
                $modules[$module][$param] = $value;
            }

            foreach($modules as $name => $parameters) {
                if (!isset($parameters['access']) || $parameters['access'] == 0) {
                    continue;
                }
                $mainAccess = $masterConfigIni->getValue($name.'.access', 'modules');
                if ($mainAccess === null || $mainAccess === 0) {
                    foreach($parameters as $paramName => $paramValue) {
                        $masterConfigIni->setValue($name.'.'.$paramName, $paramValue, 'modules');
                    }
                }
                else if ($mainAccess < $parameters['access'] ) {
                    $masterConfigIni->setValue($name.'.access', $parameters['access'], 'modules');
                }
            }

            $epConfigIni->removeValue('', 'modules');
            $epConfigIni->save();
            $masterConfigIni->save();
        }
    }

    protected function migrateAccessValue_1_7_0(IniModifier $masterConfigIni) {
        $modulesParameters = $masterConfigIni->getValues('modules');
        if ($modulesParameters) {
            foreach($modulesParameters as $name => $value) {
                list($module, $param) = explode('.', $name, 2);
                if ($param == 'access') {
                    $masterConfigIni->setValue($module.'.enabled', ($value > 0), 'modules');
                    $masterConfigIni->removeValue($module.'.access', 'modules');
                }
            }
            $masterConfigIni->save();
        }
    }

    protected function error($msg){
        $this->reporter->message($msg, 'error');
    }

    protected function ok($msg){
        $this->reporter->message($msg, '');
    }

    protected function warning($msg){
        $this->reporter->message($msg, 'warning');
    }

    protected function notice($msg){
        $this->reporter->message($msg, 'notice');
    }
}