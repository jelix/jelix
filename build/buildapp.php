<?php
/**
* @package     jelix
* @author      Laurent Jouanneau
* @contributor Julien Issler
* @copyright   2006-2007 Laurent Jouanneau
* @copyright   2008 Julien Issler
* @link        http://www.jelix.org
* @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
*/
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
    false,
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
include(__DIR__.'/lib/jBuild.inc.php');

//----------------- Prepare environment variables

if(!$APPNAME){
    die("Error: APPNAME is empty");
}
$APPDIR = jBuildUtils::normalizeDir($APPNAME);
$MAIN_TARGET_PATH = jBuildUtils::normalizeDir($MAIN_TARGET_PATH);
$TODAY = date('Y-m-d H:i');

Env::setFromFile('VERSION',$APPDIR.'/VERSION',true);
$VERSION = preg_replace('/\s+/m', '', $VERSION);
$SOURCE_REVISION = Git::revision(__DIR__.'/../');

$IS_NIGHTLY = (strpos($VERSION,'SERIAL') !== false);

if($IS_NIGHTLY){
    $PACKAGE_NAME=$APPNAME.'-'.str_replace('SERIAL', '', $VERSION);
    if(substr($PACKAGE_NAME,-1,1) == '.')
      $PACKAGE_NAME = substr($PACKAGE_NAME,0,-1);
    $VERSION = str_replace('SERIAL', $SOURCE_REVISION, $VERSION);
}
else {
    $PACKAGE_NAME=$APPNAME.'-'.$VERSION;
}


Env::setFromFile('LIB_VERSION','lib/jelix/VERSION', true);
$LIB_VERSION = preg_replace('/\s+/m', '', $LIB_VERSION);
$IS_LIB_NIGHTLY = (strpos($LIB_VERSION,'SERIAL') !== false);

if($IS_LIB_NIGHTLY){
    $LIB_VERSION = str_replace('SERIAL', $SOURCE_REVISION, $LIB_VERSION);
}

if (preg_match('/\.([a-z0-9\-]+)$/i', $LIB_VERSION, $m))
    $LIB_VERSION_MAX =  substr($LIB_VERSION, 0, - strlen($m[1]))."*";
else
    $LIB_VERSION_MAX = $LIB_VERSION;


if($PACKAGE_TAR_GZ || $PACKAGE_ZIP ){
    //$MAIN_TARGET_PATH = jBuildUtils::normalizeDir($MAIN_TARGET_PATH).$PACKAGE_NAME;
}

//----------------- Source generation

//... directories creation
jBuildUtils::createDir($MAIN_TARGET_PATH);

//... manifests execution
jManifest::process('build/manifests/'.$APPNAME.'.mn', '.', $MAIN_TARGET_PATH, ENV::getAll());


file_put_contents($MAIN_TARGET_PATH.$APPDIR.'/VERSION', $VERSION);

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
?>
