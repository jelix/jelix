<?php
/**
 * @package    jelix-scripts
 *
 * @author     Laurent Jouanneau
 * @copyright  2011-2016 Laurent Jouanneau
 *
 * @see       http://jelix.org
 * @licence    http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */

namespace Jelix\DevHelper;

use Symfony\Component\Console\Application;

error_reporting(E_ALL);
define('JELIX_SCRIPTS_PATH', __DIR__.'/../');

/**
 * Class JelixCommands.
 *
 * @package Jelix\DevHelper
 */
class JelixCommands
{
    public static function setup()
    {
        \Jelix\Scripts\Utils::checkEnv();

        \jApp::setEnv('jelix-scripts');
        \Jelix\Scripts\Utils::checkTempPath();

        $jelixScriptConfig = \Jelix\DevHelper\JelixScript::loadConfig();
        $jelixScriptConfig->generateUndefinedProperties();

        $application = new Application('Jelix helpers for the developer');
        $application->add(new Command\MigrateApp($jelixScriptConfig));
        $application->add(new Command\InitAdmin($jelixScriptConfig));
        $application->add(new Command\CreateCtrl($jelixScriptConfig));
        $application->add(new Command\CreateDao($jelixScriptConfig));
        $application->add(new Command\CreateDaoCrud($jelixScriptConfig));
        $application->add(new Command\CreateClassFromDao($jelixScriptConfig));
        $application->add(new Command\CreateModule($jelixScriptConfig));
        $application->add(new Command\ConfigureModule($jelixScriptConfig));
        $application->add(new Command\UnconfigureModule($jelixScriptConfig));
        $application->add(new Command\CreateEntryPoint($jelixScriptConfig));
        $application->add(new Command\CreateForm($jelixScriptConfig));
        $application->add(new Command\CreateLangPackage($jelixScriptConfig));
        $application->add(new Command\CreateZone($jelixScriptConfig));
        $application->add(new Command\IniChange($jelixScriptConfig));

        return $application;
    }

    public static function launch(Application $app = null)
    {
        if (!$app) {
            $app = self::setup();
        }
        $app->run();
    }
}
