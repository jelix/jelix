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
class Configure {


    static function launch() {
        Utils::checkEnv();
        // init Jelix environment
        \jApp::setEnv('configure');
        Utils::checkTempPath();

        $application = new SingleCommandApplication(
            new ConfigureCommand(), "Configuration");
        $application ->run();

    }

}