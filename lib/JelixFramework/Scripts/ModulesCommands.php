<?php
/**
 * @author      Laurent Jouanneau
 * @copyright   2018-2026 Laurent Jouanneau
 *
 * @see         https://www.jelix.org
 * @licence     MIT
 */

namespace Jelix\Scripts;

use Jelix\Core\Config\AppConfig;
use Jelix\Core\Config\FrameworkRuntimeConfig;
use Symfony\Component\Console\Application;

/**
 * Launch commands from modules.
 */
class ModulesCommands
{
    public static function run()
    {
        Utils::checkEnv();

        // init Jelix environment

        \jApp::setEnv('console');

        Utils::checkTempPath();

        $fmkInfos = \Jelix\Core\Infos\FrameworkInfos::load();
        $ep = $fmkInfos->getDefaultEntryPointInfo();

        // load framework config
        $fmkConfig = FrameworkRuntimeConfig::loadForRuntime(\jApp::varLibPath());

        $config = AppConfig::loadForCli($ep->getConfigFile(), $fmkConfig, 'console.php');
        \jApp::setConfig($config, $fmkConfig);
        \jApp::setCoord(new \jCoordinator());
        \jFile::createDir(\jApp::tempPath(), \jApp::config()->chmodDir);

        // ----- init the Application object
        $projectInfos = \Jelix\Core\Infos\AppInfos::load();
        $application = new Application($projectInfos->name.' commands');

        // try to read a commands.php file from each modules
        foreach ($fmkConfig->getEnabledModulePathsList() as $module => $path) {
            if (file_exists($path.'commands.php')) {
                require $path.'commands.php';
            }
        }
        return $application->run();
    }
}
