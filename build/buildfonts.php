<?php
/**
* @package     jelix
* @author      Laurent Jouanneau
* @contributor
* @copyright   2007-2011 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
*/
$BUILD_OPTIONS = array(
  // each build options item should be an array
  // 0: help (or false for hidden options)
  // 1: the default value (string)
  // 2: a preg expression to verify the given value
  // or
  // 1: a boolean, which indicates that the option is a boolean value
  //    the value of this boolean is the default value
  // 2: no value
  //
  // if an option in the ini file, is empty :
  //   if there isn't a regexp, the result value will be the empty value
  //   if there is a regexp and if it does match, the value will be the empty value
  //   if there is a regexp and if it does not match,  the value will be the default value


'MAIN_TARGET_PATH'=> array(
    "main directory where sources will be copied",
    '_dist',
    '',
    ), 
/*'PACKAGE_TAR_GZ'=>array(
    "create a tar.gz package",
    false,
    ),*/
'PACKAGE_ZIP'=>array(
    "create a zip package",
    false,
    ),
'LIB_VERSION'=> array(
    false,
    '',
    ),
);
include(dirname(__FILE__).'/lib/jBuild.inc.php');

//----------------- initialize variables
Env::setFromFile('LIB_VERSION','lib/jelix/VERSION', true);
$LIB_VERSION = preg_replace('/\s+/m', '', $LIB_VERSION);
$SOURCE_REVISION = Git::revision(dirname(__FILE__).'/../');

$IS_NIGHTLY = (strpos($LIB_VERSION,'SERIAL') !== false);

if($IS_NIGHTLY){
    $PACKAGE_NAME='jelix-'.str_replace('SERIAL', '', $LIB_VERSION);
    if(substr($PACKAGE_NAME,-1,1) == '.')
      $PACKAGE_NAME = substr($PACKAGE_NAME,0,-1);
    $PACKAGE_NAME .= '-pdf-fonts';
    $LIB_VERSION = str_replace('SERIAL', $SOURCE_REVISION, $LIB_VERSION);
}
else {
    $PACKAGE_NAME='jelix-'.$LIB_VERSION.'-pdf-fonts';
}



if(/*$PACKAGE_TAR_GZ ||*/ $PACKAGE_ZIP ){
    $BUILD_TARGET_PATH = jBuildUtils::normalizeDir($MAIN_TARGET_PATH).$PACKAGE_NAME.'/';
}else{
    $BUILD_TARGET_PATH = jBuildUtils::normalizeDir($MAIN_TARGET_PATH);
}


//----------------- build the package

//... directory creation
jBuildUtils::createDir($BUILD_TARGET_PATH);

//... copying files
jManifest::process('build/manifests/fonts.mn', '.', $BUILD_TARGET_PATH, ENV::getAll());

//... packages

//if($PACKAGE_TAR_GZ){
//    exec('tar czf '.$MAIN_TARGET_PATH.'/'.$PACKAGE_NAME.'.tar.gz -C '.$MAIN_TARGET_PATH.' '.$PACKAGE_NAME);
//}

if($PACKAGE_ZIP){
    file_put_contents($MAIN_TARGET_PATH.'/PACKAGE_FONTS_NAME', $PACKAGE_NAME);
    chdir($MAIN_TARGET_PATH);
    exec('zip -r '.$PACKAGE_NAME.'.zip '.$PACKAGE_NAME);
    chdir(dirname(__FILE__));
}

exit(0);
