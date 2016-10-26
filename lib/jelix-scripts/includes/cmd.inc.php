<?php
/**
* @package     jelix-scripts
* @author      Laurent Jouanneau
* @contributor Loic Mathaud
* @copyright   2005-2016 Laurent Jouanneau
* @link        http://jelix.org
* @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
*/
namespace Jelix\DevHelper\Command;
use Symfony\Component\Console\Application;


error_reporting(E_ALL);
define ('JELIX_SCRIPTS_PATH', __DIR__.'/../');

if(!class_exists('jCoordinator', false)) { // for old application.init.php which doesn't include init.php
    echo "Error: your application.init.php should include the vendor/autoload.php";
    exit(1);
}

if (!\jServer::isCLI()) {
    echo "Error: you're not allowed to execute this script outside a command line shell.\n";
    exit(1);
}

if (!\Jelix\Core\App::isInit()) {
    echo "Error: should run within an application\n";
    exit(1);
}


\Jelix\Core\App::setEnv('jelix-scripts');
\Jelix\DevHelper\JelixScript::checkTempPath();

$jelixScriptConfig = \Jelix\DevHelper\JelixScript::loadConfig();

$application = new Application("Jelix helpers");
$application->add(new InstallApp($jelixScriptConfig));
$application->add(new InstallModule($jelixScriptConfig));
$application->add(new UninstallModule($jelixScriptConfig));
$application->add(new InitAdmin($jelixScriptConfig));
$application->add(new CreateCtrl($jelixScriptConfig));
$application->add(new CreateDao($jelixScriptConfig));
$application->add(new CreateDaoCrud($jelixScriptConfig));
$application->add(new CreateClassFromDao($jelixScriptConfig));
$application->add(new CreateModule($jelixScriptConfig));
$application->add(new CreateEntryPoint($jelixScriptConfig));
$application->add(new CreateForm($jelixScriptConfig));
$application->add(new CreateLangPackage($jelixScriptConfig));
$application->add(new CreateZone($jelixScriptConfig));
$application->add(new ClearTemp($jelixScriptConfig));
$application->add(new CloseApp($jelixScriptConfig));
$application->add(new OpenApp($jelixScriptConfig));
$application->add(new FilesRights($jelixScriptConfig));

$application->add(new Acl2\RightsList($jelixScriptConfig));
$application->add(new Acl2\AddRight($jelixScriptConfig));
$application->add(new Acl2\RemoveRight($jelixScriptConfig));
$application->add(new Acl2\SubjectList($jelixScriptConfig));
$application->add(new Acl2\SubjectCreate($jelixScriptConfig));
$application->add(new Acl2\SubjectDelete($jelixScriptConfig));
$application->add(new Acl2\SubjectGroupList($jelixScriptConfig));
$application->add(new Acl2\SubjectGroupCreate($jelixScriptConfig));
$application->add(new Acl2\SubjectGroupDelete($jelixScriptConfig));

$application->add(new Acl2Groups\GroupsList($jelixScriptConfig));
$application->add(new Acl2Groups\GroupCreate($jelixScriptConfig));
$application->add(new Acl2Groups\GroupDelete($jelixScriptConfig));
$application->add(new Acl2Groups\GroupName($jelixScriptConfig));
$application->add(new Acl2Groups\GroupDefault($jelixScriptConfig));

$application->add(new Acl2Users\UsersList($jelixScriptConfig));
$application->add(new Acl2Users\UserRegister($jelixScriptConfig));
$application->add(new Acl2Users\UserUnregister($jelixScriptConfig));
$application->add(new Acl2Users\UserAddGroup($jelixScriptConfig));
$application->add(new Acl2Users\UserRemoveGroup($jelixScriptConfig));

if(!defined('DECLARE_MYCOMMANDS')) {
    $application->run();
}

