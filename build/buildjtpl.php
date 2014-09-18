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
'PHP_VERSION_TARGET'=> array(
    "PHP5 version for which jTpl will be generated (by default, the target is php 5.3)",
    '5.3'
    ),
'PACKAGE_TAR_GZ'=>array(
    "create a tar.gz package",
    false,
    ),
'PACKAGE_ZIP'=>array(
    "create a zip package",
    false,
    ),
'WITH_TESTS'=>array(
    "includes tests",
    false,
    ),
'JTPL_VERSION'=> array(
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
'PHP53'=> array(
    false,
    false,
    ),
'PHP54'=> array(
    false,
    false,
    ),
'PHP54ORMORE'=> array(
    false,
    false,
    ),
);

include(__DIR__.'/lib/jBuild.inc.php');

//----------------- Prepare environment variables

Env::setFromFile('JTPL_VERSION','lib/jelix/tpl/VERSION', true);
$SOURCE_REVISION = Git::revision(__DIR__.'/../');

$IS_NIGHTLY = (strpos($JTPL_VERSION,'SERIAL') !== false);

if($IS_NIGHTLY){
    $PACKAGE_NAME='jtpl-'.str_replace('SERIAL', '', $JTPL_VERSION);
    if(substr($PACKAGE_NAME,-1,1) == '.')
      $PACKAGE_NAME = substr($PACKAGE_NAME,0,-1);
    $JTPL_VERSION = str_replace('SERIAL', $SOURCE_REVISION, $JTPL_VERSION);
}
else {
    $PACKAGE_NAME='jtpl-'.$JTPL_VERSION;
}

if($PACKAGE_TAR_GZ || $PACKAGE_ZIP ){
    $BUILD_TARGET_PATH = jBuildUtils::normalizeDir($MAIN_TARGET_PATH).$PACKAGE_NAME.'/';
}else{
    $BUILD_TARGET_PATH = jBuildUtils::normalizeDir($MAIN_TARGET_PATH);
}

if($PHP_VERSION_TARGET){
    if(version_compare($PHP_VERSION_TARGET, '5.4') > -1){
        $PHP54 = 1;
        $PHP54ORMORE = 1;
    }
    elseif (version_compare($PHP_VERSION_TARGET, '5.3') > -1) {
        $PHP53 = 1;
    }
    else{
        die("PHP VERSION ".$PHP_VERSION_TARGET." is not supported");
    }
}else{
    // no defined target, so php 5.3
    $PHP53 = 1;
}


//----------------- Source generation

//... directories creation
jBuildUtils::createDir($BUILD_TARGET_PATH);

//... manifests execution
jManifest::process('build/manifests/jtpl-standalone.mn', '.', $BUILD_TARGET_PATH, ENV::getAll());

if($WITH_TESTS) {
    jManifest::process('build/manifests/jtpl-standalone-tests.mn', '.', $BUILD_TARGET_PATH, ENV::getAll());
}

file_put_contents($BUILD_TARGET_PATH.'/VERSION', $JTPL_VERSION);

//... packages

if($PACKAGE_TAR_GZ){
    exec('tar czf '.$MAIN_TARGET_PATH.'/'.$PACKAGE_NAME.'.tar.gz -C '.$MAIN_TARGET_PATH.' '.$PACKAGE_NAME);
}

if($PACKAGE_ZIP){
    $oldpath = getcwd();
    chdir($MAIN_TARGET_PATH);
    exec('zip -r '.$PACKAGE_NAME.'.zip '.$PACKAGE_NAME);
    chdir($oldpath);
}

exit(0);
?>
