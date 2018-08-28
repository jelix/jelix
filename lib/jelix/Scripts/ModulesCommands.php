<?php
/**
 * @author      Laurent Jouanneau
 * @copyright   2018 Laurent Jouanneau
 *
 * @link        http://www.jelix.org
 * @licence     MIT
 */
namespace Jelix\Scripts;

use Symfony\Component\Console\Application;

/**
 * Launch commands from modules
 *
 * @package Jelix\Scripts
 */
class ModulesCommands {


    static function run() {

        Utils::checkEnv();

        // init Jelix environment

        \jApp::setEnv('console');

        Utils::checkTempPath();


        $parser = new \Jelix\Core\Infos\ProjectXmlParser(\jApp::appPath('project.xml'));
        $projectInfos = $parser->parse();
        $ep = $projectInfos->getEntryPointInfo('index');

        \jApp::setConfig(\jConfigCompiler::read($ep->configFile, true, true, 'console.php'));
        \jFile::createDir(\jApp::tempPath(), \jApp::config()->chmodDir);

        // ----- init the Application object
        $application = new Application($projectInfos->name." commands");

        // try to read a commands.php file from each modules
        foreach(\jApp::getEnabledModulesPaths() as $module => $path) {
            if (file_exists($path.'commands.php')) {
                require($path.'commands.php');
            }
        }
        $application ->run();

    }

}