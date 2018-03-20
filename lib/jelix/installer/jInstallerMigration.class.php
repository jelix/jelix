<?php
/**
* @package     jelix
* @subpackage  installer
* @author      Laurent Jouanneau
* @copyright   2016 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/
require_once(JELIX_LIB_PATH.'installer/jIInstallReporter.iface.php');
require_once(JELIX_LIB_PATH.'installer/jInstallerReporterTrait.trait.php');
require_once(JELIX_LIB_PATH.'installer/textInstallReporter.class.php');
require_once(JELIX_LIB_PATH.'installer/ghostInstallReporter.class.php');
/**
 * do changes in the application before the installation of modules can be done
 *
 * It is used for directory changes etc.
 */
class jInstallerMigration {
    
    /**
     * the object responsible of the results output
     * @var jIInstallReporter
     */
    protected $reporter;

    function __construct(jIInstallReporter $reporter) {
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
        $newConfigPath = jApp::appConfigPath();
        if (!file_exists($newConfigPath)) {
            $this->reporter->message('Create app/config/', 'notice');
            jFile::createDir($newConfigPath);
        }

        // move mainconfig.php to app/config/
        if (!file_exists($newConfigPath.'mainconfig.ini.php')) {
            if (!file_exists(jApp::varConfigPath('mainconfig.ini.php'))) {
                if (!file_exists(jApp::varConfigPath('defaultconfig.ini.php'))) {
                    throw new \Exception("Migration to Jelix 1.7.0 canceled: where is your mainconfig.ini.php?");
                }
                $this->reporter->message('Move var/config/defaultconfig.ini.php to app/config/mainconfig.ini.php', 'notice');
                rename(jApp::varConfigPath('defaultconfig.ini.php'), $newConfigPath.'mainconfig.ini.php');
            }
            else {
                $this->reporter->message('Move var/config/mainconfig.ini.php to app/config/', 'notice');
                rename(jApp::varConfigPath('mainconfig.ini.php'), $newConfigPath.'mainconfig.ini.php');
            }
        }

        // move entrypoint configs to app/config
        $projectxml = simplexml_load_file(jApp::appPath('project.xml'));
        // read all entry points data
        foreach ($projectxml->entrypoints->entry as $entrypoint) {
            $configFile = (string)$entrypoint['config'];
            $dest = jApp::appConfigPath($configFile);
            if (!file_exists($dest)) {
                if (!file_exists(jApp::varConfigPath($configFile))) {
                    $this->reporter->message("Config file var/config/$configFile indicated in project.xml, does not exist", 'warning');
                    continue;
                }

                $this->reporter->message("Move var/config/$configFile to app/config/", 'notice');
                jFile::createDir(dirname($dest));
                rename(jApp::varConfigPath($configFile), $dest);
            }

            $config = parse_ini_file(jApp::appConfigPath($configFile), true);
            if (isset($config['urlengine']['significantFile'])) {
                $urlFile = $config['urlengine']['significantFile'];
                if (!file_exists(jApp::appConfigPath($urlFile)) && file_exists(jApp::varConfigPath($urlFile))) {
                    $this->reporter->message("Move var/config/$urlFile to app/config/", 'notice');
                    rename(jApp::varConfigPath($urlFile), jApp::appConfigPath($urlFile));
                }
            }
        }

        // move urls.xml to app/config
        $mainconfig = parse_ini_file(jApp::appConfigPath('mainconfig.ini.php'), true);
        if (isset($mainconfig['urlengine']['significantFile'])) {
            $urlFile = $mainconfig['urlengine']['significantFile'];
        }
        else {
            $urlFile = 'urls.xml';
        }
        if (!file_exists(jApp::appConfigPath($urlFile)) && file_exists(jApp::varConfigPath($urlFile))) {
            $this->reporter->message("Move var/config/$urlFile to app/config/", 'notice');
            rename(jApp::varConfigPath($urlFile), jApp::appConfigPath($urlFile));
        }

        if (!file_exists(jApp::appPath('app/responses'))) {
            $this->reporter->message("Move responses/ to app/responses/", 'notice');
            rename(jApp::appPath('responses'), jApp::appPath('app/responses'));
        }

        if (file_exists(jApp::varConfigPath('profiles.ini.php'))) {
            $profilesini = new \Jelix\IniFile\IniModifier(jApp::varConfigPath('profiles.ini.php'));
            $this->migrateProfilesIni_1_7_0($profilesini);
        }
        if (file_exists(jApp::varConfigPath('profiles.ini.php.dist'))) {
            $profilesini = new \Jelix\IniFile\IniModifier(jApp::varConfigPath('profiles.ini.php.dist'));
            $this->migrateProfilesIni_1_7_0($profilesini);
        }

        // move plugin configuration file to global config
        $this->migrateCoordPluginsConf_1_7_0(jApp::appConfigPath('mainconfig.ini.php'));
        foreach ($projectxml->entrypoints->entry as $entrypoint) {
            $configFile = (string)$entrypoint['config'];
            $this->migrateCoordPluginsConf_1_7_0(jApp::appConfigPath($configFile));
        }
        $this->migrateCoordPluginsConf_1_7_0(jApp::varConfigPath('localconfig.ini.php'));
        foreach ($projectxml->entrypoints->entry as $entrypoint) {
            $configFile = (string)$entrypoint['config'];
            $this->migrateCoordPluginsConf_1_7_0(jApp::varConfigPath($configFile));
        }

        $this->reporter->message('Migration to Jelix 1.7.0 is done', 'notice');
    }

    private function migrateProfilesIni_1_7_0(\Jelix\IniFile\IniModifier $profilesini) {
        foreach ($profilesini->getSectionList() as $name) {
            // move jSoapClient classmap files
            if (strpos($name, 'jsoapclient:') === 0) {
                $classmapFile = $profilesini->getValue('classmap_file', $name);
                if ($classmapFile != '' &&
                    file_exists(jApp::varConfigPath($classmapFile))
                ) {
                    $this->reporter->message("Move " . $classmapFile . " to app/config/", 'notice');
                    rename(jApp::varConfigPath($classmapFile), jApp::appConfigPath($classmapFile));
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

    private function migrateCoordPluginsConf_1_7_0($configFileName) {
        $config = new \Jelix\IniFile\IniModifier($configFileName);
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
                $confPath = jApp::varConfigPath($conf);
                if (!file_exists($confPath)) {
                    continue;
                }
                $ini = new \Jelix\IniFile\IniModifier($confPath);
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
                    $rpath = \Jelix\FileUtilities\Path::shortestPath(jApp::varConfigPath(), $ini->getFileName());
                    $this->reporter->message("Move plugin conf file ".$rpath." to app/config/", 'notice');
                    rename ($ini->getFileName(),jApp::appConfigPath($rpath));
                }
                continue;
            }
            $this->reporter->message("Import plugin conf file ".$rpath." into global configuration", 'notice');
            $config->import($ini, $name);
            $config->setValue($name, '1', 'coordplugins');
            unlink($ini->getFileName());
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