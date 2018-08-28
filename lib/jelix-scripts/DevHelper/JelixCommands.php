<?php
/**
 * @package    jelix-scripts
 * @author     Laurent Jouanneau
 * @copyright  2011-2016 Laurent Jouanneau
 * @link       http://jelix.org
 * @licence    http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */
namespace Jelix\DevHelper;
use Symfony\Component\Console\Application;
use Jelix\DevHelper\Command;

error_reporting(E_ALL);
define ('JELIX_SCRIPTS_PATH', __DIR__.'/../');

/**
 * Class JelixCommands
 * @package Jelix\DevHelper
 */
class JelixCommands {

    static function setup() {

        if (!\jServer::isCLI()) {
            echo "Error: you're not allowed to execute this script outside a command line shell.\n";
            exit(1);
        }

        if (!\jApp::isInit()) {
            echo "Error: should run within an application\n";
            exit(1);
        }

        \jApp::setEnv('jelix-scripts');
        \Jelix\DevHelper\JelixScript::checkTempPath();

        $jelixScriptConfig = \Jelix\DevHelper\JelixScript::loadConfig();

        $application = new Application("Jelix helpers");
        $application->add(new Command\InstallApp($jelixScriptConfig));
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
        $application->add(new Command\ClearTemp($jelixScriptConfig));
        $application->add(new Command\CloseApp($jelixScriptConfig));
        $application->add(new Command\OpenApp($jelixScriptConfig));
        $application->add(new Command\FilesRights($jelixScriptConfig));

        $application->add(new Command\Acl2\RightsList($jelixScriptConfig));
        $application->add(new Command\Acl2\AddRight($jelixScriptConfig));
        $application->add(new Command\Acl2\RemoveRight($jelixScriptConfig));
        $application->add(new Command\Acl2\SubjectList($jelixScriptConfig));
        $application->add(new Command\Acl2\SubjectCreate($jelixScriptConfig));
        $application->add(new Command\Acl2\SubjectDelete($jelixScriptConfig));
        $application->add(new Command\Acl2\SubjectGroupList($jelixScriptConfig));
        $application->add(new Command\Acl2\SubjectGroupCreate($jelixScriptConfig));
        $application->add(new Command\Acl2\SubjectGroupDelete($jelixScriptConfig));

        $application->add(new Command\Acl2Groups\GroupsList($jelixScriptConfig));
        $application->add(new Command\Acl2Groups\GroupCreate($jelixScriptConfig));
        $application->add(new Command\Acl2Groups\GroupDelete($jelixScriptConfig));
        $application->add(new Command\Acl2Groups\GroupName($jelixScriptConfig));
        $application->add(new Command\Acl2Groups\GroupDefault($jelixScriptConfig));

        $application->add(new Command\Acl2Users\UsersList($jelixScriptConfig));
        $application->add(new Command\Acl2Users\UserRegister($jelixScriptConfig));
        $application->add(new Command\Acl2Users\UserUnregister($jelixScriptConfig));
        $application->add(new Command\Acl2Users\UserAddGroup($jelixScriptConfig));
        $application->add(new Command\Acl2Users\UserRemoveGroup($jelixScriptConfig));

        return $application;
    }

    static function launch(Application $app = null) {
        if (!$app) {
            $app = self::setup();
        }
        $app->run();
    }

}