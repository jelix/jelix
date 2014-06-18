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
'IS_NIGHTLY'=> array(
    false,
    false,
    ),
'SOURCE_REVISION'=> array(
    false,
    ),
'VERSION'=> array(
    false,
    'SERIAL',
    '',
    ),
);

include(dirname(__FILE__).'/lib/jBuild.inc.php');

//----------------- Prepare environment variables

$MAIN_TARGET_PATH = jBuildUtils::normalizeDir($MAIN_TARGET_PATH);

$SOURCE_REVISION = Git::revision(dirname(__FILE__).'/../');

if($VERSION == 'SERIAL'){
    $VERSION = 'SERIAL-'.$SOURCE_REVISION;
    $IS_NIGHTLY = true;
}else{
    $IS_NIGHTLY = false;
}


if($PACKAGE_TAR_GZ || $PACKAGE_ZIP ){
    $BUILD_SUBPATH = 'additionnal-modules/';
    if($IS_NIGHTLY)
        $PACKAGE_NAME='additionnal-modules-nightly';
    else
        $PACKAGE_NAME='additionnal-modules-HG-'.$SOURCE_REVISION;
}else{
    $BUILD_SUBPATH = 'lib/jelix-modules/';

}

//----------------- Source generation

//... directories creation
jBuildUtils::createDir($MAIN_TARGET_PATH.$BUILD_SUBPATH);

//... manifests execution
jManifest::process('build/manifests/jelix-modules.mn', 'lib/jelix-modules/', $MAIN_TARGET_PATH.$BUILD_SUBPATH, ENV::getAll());

//... packages

if($PACKAGE_TAR_GZ){
    exec('tar czf '.$MAIN_TARGET_PATH.$PACKAGE_NAME.'.tar.gz -C '.$MAIN_TARGET_PATH.' '.$BUILD_SUBPATH);
}

if($PACKAGE_ZIP){
    $oldpath = getcwd();
    chdir($MAIN_TARGET_PATH);
    exec('zip -r '.$PACKAGE_NAME.'.zip '.$BUILD_SUBPATH);
    chdir($oldpath);
}

exit(0);
?>
