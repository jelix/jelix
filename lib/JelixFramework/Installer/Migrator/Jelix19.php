<?php
/**
 * @package     jelix
 *
 * @author      Laurent Jouanneau
 * @copyright   2025 Laurent Jouanneau
 *
 * @see         https://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

namespace Jelix\Installer\Migrator;

use \jApp as App;
use Jelix\Core\Config\AppConfig;
use Jelix\IniFile\IniModifier;

class Jelix19
{
    /**
     * the object responsible of the results output.
     *
     * @var \Jelix\Installer\Reporter\ReporterInterface
     */
    protected $reporter;

    /**
     * @var \Jelix\IniFile\IniReader
     */
    protected $defaultConfigIni;

    public function __construct(\Jelix\Installer\Reporter\ReporterInterface $reporter)
    {
        $this->reporter = $reporter;
        $this->defaultConfigIni = new \Jelix\IniFile\IniReader(AppConfig::getDefaultConfigFile());
    }

    public function migrate()
    {
        $this->reporter->message('Start migration to Jelix 1.9.0', 'notice');

        // migration of values of the 'modules' section to framework.ini.php and localframework.ini.php
        $mainConfigIni = new IniModifier(App::appSystemPath('mainconfig.ini.php'));
        $frameworkIni = new IniModifier(App::appSystemPath('framework.ini.php'));
        $localFrameworkIni = new IniModifier(App::varConfigPath('localframework.ini.php'));
        $this->moveModulesStatusToFrameworkIni($mainConfigIni, $frameworkIni, $localFrameworkIni);

        $this->reporter->message('Migration to Jelix 1.9.0 is done', 'notice');
    }

    public function localMigrate()
    {
        $this->reporter->message('Start migration to Jelix 1.9.0', 'notice');
        // create the var/lib/ directory
        if (!file_exists(\jApp::varLibPath())) {
            $this->reporter->message('Create new directory var/lib/', 'notice');
            \Jelix\FileUtilities\Directory::create(\jApp::varLibPath());
            file_put_contents(\jApp::varLibPath('.dummy'), '');
        }

        // migration of values of the 'modules' section to framework.ini.php and localframework.ini.php
        $localConfigIni = new IniModifier(App::varConfigPath('localconfig.ini.php'));
        $frameworkIni = new IniModifier(App::appSystemPath('framework.ini.php'));
        $localFrameworkIni = new IniModifier(App::varConfigPath('localframework.ini.php'));

        $this->moveModulesStatusToFrameworkIni($localConfigIni, $frameworkIni, $localFrameworkIni, true);

        $this->reporter->message('Migration of local configuration to Jelix 1.7.0 is done', 'notice');
    }


    public function moveModulesStatusToFrameworkIni(
        IniModifier $configIni,
        IniModifier $frameworkIni,
        IniModifier $localFrameworkIni,
        $localMode = false
    )
    {
        $modulesInfo = array();

        foreach($configIni->getValues('modules') as $key => $value)
        {
            if (!preg_match('/^([a-zA-Z_0-9]+)\\.(.*)$/', $key, $m)) {
                continue;
            }
            $name = $m[1];
            if (!isset($modulesInfo[$name])) {
                $modulesInfo[$name] = array();
            }
            $key = $m[2];
            $modulesInfo[$name][$key] = $value;
        }

        foreach($modulesInfo as $module => $values) {
            if (isset($values['localconf'])) {
                $localConf = $values['localconf'];
                unset($values['localconf']);
                if ($localConf) {
                    $this->reporter->message('Move declaration module of '.$module. ' to localframework.ini.php', 'notice');
                    $localFrameworkIni->setValues($values, 'module:'.$module);
                }
                else {
                    $this->reporter->message('Move declaration module of '.$module. ' to framework.ini.php', 'notice');
                    $frameworkIni->setValues($values, 'module:'.$module);
                }
            }
            else if ($localMode) {
                $this->reporter->message('Move declaration module of '.$module. ' to localframework.ini.php', 'notice');
                $localFrameworkIni->setValues($values, 'module:'.$module);
            }
            else {
                $this->reporter->message('Move declaration module of '.$module. ' to framework.ini.php', 'notice');
                $frameworkIni->setValues($values, 'module:'.$module);
            }
        }

        $localFrameworkIni->save();
        $frameworkIni->save();

        $configIni->removeSection('modules');
        $configIni->save();
    }

    protected function error($msg)
    {
        $this->reporter->message($msg, 'error');
    }

    protected function ok($msg)
    {
        $this->reporter->message($msg, '');
    }

    protected function warning($msg)
    {
        $this->reporter->message($msg, 'warning');
    }

    protected function notice($msg)
    {
        $this->reporter->message($msg, 'notice');
    }
}
