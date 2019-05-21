<?php
/**
 * @author      Laurent Jouanneau
 * @copyright   2018 Laurent Jouanneau
 *
 * @see        http://www.jelix.org
 * @licence     MIT
 */

namespace Jelix\Scripts;

use Symfony\Component\Console\Application;
use Jelix\Core\App;

/**
 * Launch commands from modules.
 *
 * @package Jelix\Scripts
 */
class ModulesCommands
{
    public static function run()
    {
        Utils::checkEnv();

        // init Jelix environment

        App::setEnv('console');

        Utils::checkTempPath();

        $fmkInfos = \Jelix\Core\Infos\FrameworkInfos::load();
        $ep = $fmkInfos->getEntryPointInfo('index');

        App::setConfig(\jConfigCompiler::read($ep->getConfigFile(), true, true, 'console.php'));
        \jFile::createDir(App::tempPath(), App::config()->chmodDir);

        // ----- init the Application object
        $projectInfos = \Jelix\Core\Infos\AppInfos::load();
        $application = new Application($projectInfos->name.' commands');

        // try to read a commands.php file from each modules
        foreach (App::getEnabledModulesPaths() as $module => $path) {
            if (file_exists($path.'commands.php')) {
                require $path.'commands.php';
            }
        }
        $application->run();
    }
}
