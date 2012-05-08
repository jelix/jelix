<?php
/**
* @package     jelix
* @author      Laurent Jouanneau
* @contributor
* @copyright   2006-2007 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
*/
$BUILD_OPTIONS = array(
'MAIN_TARGET_PATH'=> array(
    "main directory where sources will be copied",  // meaning (false = hidden otion)
    '_dist',                                        // default value (boolean = boolean option)
    '',                                             // regexp for the value or empty=all (only for non-boolean options)
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
    'SERIAL',
    '',
    ),
'IS_NIGHTLY'=> array(
    false,
    false,
    ),
'SOURCE_REVISIONs'=> array(
    false,
    ),
);
include(__DIR__.'/lib/jBuild.inc.php');

//----------------- Prepare environment variables

Env::setFromFile('VERSION','build/VERSION', true);
$SOURCE_REVISION = Git::revision(__DIR__.'/../');

if($VERSION == 'SERIAL'){
    $VERSION = 'SERIAL-'.$SOURCE_REVISION;
    $IS_NIGHTLY = true;
}else{
    $IS_NIGHTLY = false;
}

if($PACKAGE_TAR_GZ || $PACKAGE_ZIP ){
    if($IS_NIGHTLY)
        $PACKAGE_NAME='jbuildtools-nightly';
    else
        $PACKAGE_NAME='jbuildtools-'.$VERSION;

    $BUILD_TARGET_PATH = jBuildUtils::normalizeDir($MAIN_TARGET_PATH).$PACKAGE_NAME.'/';
}else{
    $BUILD_TARGET_PATH = jBuildUtils::normalizeDir($MAIN_TARGET_PATH);
}


//----------------- Source generation

//... directories creation
jBuildUtils::createDir($BUILD_TARGET_PATH);

//... manifests execution
jManifest::process('build/manifests/jbuildtools.mn', 'build/', $BUILD_TARGET_PATH, ENV::getAll());


file_put_contents($BUILD_TARGET_PATH.'/VERSION', $VERSION);

//... packages

if($PACKAGE_TAR_GZ){
    exec('tar czf '.$MAIN_TARGET_PATH.'/'.$PACKAGE_NAME.'.tar.gz -C '.$MAIN_TARGET_PATH.' '.$PACKAGE_NAME);
}

if($PACKAGE_ZIP){
    chdir($MAIN_TARGET_PATH);
    exec('zip -r '.$PACKAGE_NAME.'.zip '.$PACKAGE_NAME);
    chdir(__DIR__);
}

exit(0);
?>
