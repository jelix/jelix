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
        $this->reporter->message('Start migration to 1.7.0', 'notice');
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

        // move jSoapClient classmap files
        if (file_exists(jApp::varConfigPath('profiles.ini.php'))) {
            $profilesini = parse_ini_file(jApp::varConfigPath('profiles.ini.php'), true);
            foreach ($profilesini as $name => $profile) {
                if (strpos($name, 'jsoapclient:') === 0 &&
                    isset($profile['classmap_file']) &&
                    trim($profile['classmap_file']) != '' &&
                    file_exists(jApp::varConfigPath($profile['classmap_file']))
                ) {
                    $this->reporter->message("Move ".$profile['classmap_file']." to app/config/", 'notice');
                    rename(jApp::varConfigPath($profile['classmap_file']), jApp::appConfigPath($profile['classmap_file']));
                }
            }
        }

        // move plugin configuration file to global config
        $this->migrateCoordPluginsConf(jApp::appConfigPath('mainconfig.ini.php'));
        foreach ($projectxml->entrypoints->entry as $entrypoint) {
            $configFile = (string)$entrypoint['config'];
            $this->migrateCoordPluginsConf(jApp::appConfigPath($configFile));
        }
        $this->migrateCoordPluginsConf(jApp::varConfigPath('localconfig.ini.php'));
        foreach ($projectxml->entrypoints->entry as $entrypoint) {
            $configFile = (string)$entrypoint['config'];
            $this->migrateCoordPluginsConf(jApp::varConfigPath($configFile));
        }

        $this->reporter->message('Migration to 1.7.0 is done', 'notice');

    }

    protected $allPluginConfigs = array();

    private function migrateCoordPluginsConf($configFileName) {
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