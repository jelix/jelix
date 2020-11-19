<?php
/**
* @package     jelix
* @author      Laurent Jouanneau
* @contributor Julien Issler
* @copyright   2006-2015 Laurent Jouanneau
* @copyright   2008 Julien Issler
* @link        http://www.jelix.org
* @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
*/
use Jelix\BuildTools as bt;
use Jelix\BuildTools\Cli\Environment as Environment;
use Jelix\BuildTools\Manifest\Manager as Manifest;
use Jelix\BuildTools\FileSystem\DirUtils as DirUtils;

$BUILD_OPTIONS = array(
'MAIN_TARGET_PATH'=> array(
    "main directory where sources will be copied",  // meaning (false = hidden otion)
    '_dist',                                        // default value (boolean = boolean option)
    '',                                             // regexp for the value or empty=all (only for non-boolean options)
    ),
'APPNAME'=> array(
    "The name of the app you want to generate (testapp)",
    '',
    '/testapp/',
    ),
'PACKAGE_TAR_GZ'=>array(
    "create a tar.gz package",
    false,
    ),
'PACKAGE_ZIP'=>array(
    "create a zip package",
    false,
    ),
'VERSION'=> array(
    false,
    '',
    '',
    ),
'LIB_VERSION'=> array(
    false,
    '',
    ),
'LIB_VERSION_MAX'=> array(
    false,
    '',
    ),
'IS_NIGHTLY'=> array(
    'says if it is a nightly or not',
    false,
    ),
'SOURCE_REVISION'=> array(
    false,
    ),
'TODAY'=> array(
    false,
    '',
    ),
);
require(__DIR__.'/vendor/autoload.php');
bt\Cli\Bootstrap::start($BUILD_OPTIONS);

//----------------- Prepare environment variables

if(!$APPNAME){
    die("Error: APPNAME is empty");
}
$APPDIR = DirUtils::normalizeDir($APPNAME);
$MAIN_TARGET_PATH = DirUtils::normalizeDir($MAIN_TARGET_PATH);
$TODAY = date('Y-m-d H:i');

Environment::setFromFile('VERSION',$APPDIR.'/VERSION',true);
$VERSION = preg_replace('/\s+/m', '', $VERSION);
$SOURCE_REVISION = bt\FileSystem\Git::revision(__DIR__.'/../');
$PACKAGE_NAME=$APPNAME.'-'.$VERSION;

Environment::setFromFile('LIB_VERSION','lib/jelix/VERSION', true);
$LIB_VERSION = preg_replace('/\s+/m', '', $LIB_VERSION);

if ($IS_NIGHTLY) {
    $VERSION .= '.'. $SOURCE_REVISION;
    $LIB_VERSION .= '.'. $SOURCE_REVISION;
}

if (preg_match('/^[0-9]+\.[0-9]+\.([a-z0-9\-\.]+)$/i', $LIB_VERSION, $m))
    $LIB_VERSION_MAX =  substr($LIB_VERSION, 0, - strlen($m[1]))."*";
else
    $LIB_VERSION_MAX = $LIB_VERSION;




if($PACKAGE_TAR_GZ || $PACKAGE_ZIP ){
    //$MAIN_TARGET_PATH = DirUtils::normalizeDir($MAIN_TARGET_PATH).$PACKAGE_NAME;
}

//----------------- Source generation

//... directories creation
DirUtils::createDir($MAIN_TARGET_PATH);

//... manifests execution
Manifest::process('build/manifests/'.$APPNAME.'.mn', '.', $MAIN_TARGET_PATH, Environment::getAll());

file_put_contents($MAIN_TARGET_PATH.$APPDIR.'/VERSION', $VERSION);

if ($IS_NIGHTLY && $APPNAME == 'testapp') {
    require(__DIR__.'/changeVersion.lib.php');
    $modifier = new ChangeVersion($MAIN_TARGET_PATH);
    $modifier->changeVersionInTestapp($VERSION);
}

//... packages
if ($PACKAGE_TAR_GZ || $PACKAGE_ZIP) {
  file_put_contents($MAIN_TARGET_PATH.'/PACKAGE_TESTAPP_NAME', $PACKAGE_NAME);
}

if($PACKAGE_TAR_GZ){
    exec('tar czf '.$MAIN_TARGET_PATH.$PACKAGE_NAME.'.tar.gz -C '.$MAIN_TARGET_PATH.' '.$APPNAME);
}

if($PACKAGE_ZIP){
    $oldpath = getcwd();
    chdir($MAIN_TARGET_PATH);
    exec('zip -r '.$PACKAGE_NAME.'.zip '.$APPNAME);
    chdir($oldpath);
}

exit(0);
