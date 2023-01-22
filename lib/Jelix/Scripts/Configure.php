<?php
/**
 * @author      Laurent Jouanneau
 * @copyright   2018-2023 Laurent Jouanneau
 *
 * @see        http://www.jelix.org
 * @licence     MIT
 */

namespace Jelix\Scripts;

/**
 * Launch commands from modules.
 *
 * @package Jelix\Scripts
 */
class Configure
{
    public static function launch()
    {
        Utils::checkEnv();
        // init Jelix environment
        \Jelix\Core\App::setEnv('configure');
        Utils::checkTempPath();

        $application = new SingleCommandApplication(
            new ConfigureCommand(),
            'Configuration'
        );
        return $application->run();
    }
}
