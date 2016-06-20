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
            if (!file_exists(jApp::configPath('mainconfig.ini.php'))) {
                if (!file_exists(jApp::configPath('defaultconfig.ini.php'))) {
                    throw new \Exception("Migration to Jelix 1.7.0 canceled: where is your mainconfig.ini.php?");
                }
                $this->reporter->message('Move var/config/defaultconfig.ini.php to app/config/mainconfig.ini.php', 'notice');
                rename(jApp::configPath('defaultconfig.ini.php'), $newConfigPath.'mainconfig.ini.php');
            }
            else {
                $this->reporter->message('Move var/config/mainconfig.ini.php to app/config/', 'notice');
                rename(jApp::configPath('mainconfig.ini.php'), $newConfigPath.'mainconfig.ini.php');
            }
        }

        // move entrypoint configs to app/config
        $projectxml = simplexml_load_file(jApp::appPath('project.xml'));
        // read all entry points data
        foreach ($projectxml->entrypoints->entry as $entrypoint) {
            $configFile = (string)$entrypoint['config'];
            $dest = jApp::appConfigPath($configFile);
            if (!file_exists($dest)) {
                if (!file_exists(jApp::configPath($configFile))) {
                    $this->reporter->message("Config file var/config/$configFile indicated in project.xml, does not exist", 'warning');
                    continue;
                }

                $this->reporter->message("Move var/config/$configFile to app/config/", 'notice');
                jFile::createDir(dirname($dest));
                rename(jApp::configPath($configFile), $dest);
            }

            $config = parse_ini_file(jApp::appConfigPath($configFile), true);
            if (isset($config['urlengine']['significantFile'])) {
                $urlFile = $config['urlengine']['significantFile'];
                if (!file_exists(jApp::appConfigPath($urlFile)) && file_exists(jApp::configPath($urlFile))) {
                    $this->reporter->message("Move var/config/$urlFile to app/config/", 'notice');
                    rename(jApp::configPath($urlFile), jApp::appConfigPath($urlFile));
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
        if (!file_exists(jApp::appConfigPath($urlFile)) && file_exists(jApp::configPath($urlFile))) {
            $this->reporter->message("Move var/config/$urlFile to app/config/", 'notice');
            rename(jApp::configPath($urlFile), jApp::appConfigPath($urlFile));
        }

        $this->reporter->message('Migration to 1.7.0 is done', 'notice');

        if (!file_exists(jApp::appPath('app/responses'))) {
            $this->reporter->message("Move responses/ to app/responses/", 'notice');
            rename(jApp::appPath('responses'), jApp::appPath('app/responses'));
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