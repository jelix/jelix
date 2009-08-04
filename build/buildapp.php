<?php
/**
* @package     jelix
* @author      Jouanneau Laurent
* @contributor Julien Issler
* @copyright   2006-2007 Jouanneau laurent
* @copyright   2008 Julien Issler
* @link        http://www.jelix.org
* @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
*/
$BUILD_OPTIONS = array(
'MAIN_TARGET_PATH'=> array(
    "main directory where sources will be copied",  // signification (false = option cachée)
    '_dist',                                        // valeur par défaut (boolean = option booleene)
    '',                                             // regexp pour la valeur ou vide=tout (seulement pour option non booleene)
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
'IS_NIGHTLY'=> array(
    false,
    false,
    ),
'HG_REVISION'=> array(
    false,
    ),
);
include(dirname(__FILE__).'/lib/jBuild.inc.php');

//----------------- Preparation des variables d'environnement

if(!$APPNAME){
    die("Error: APPNAME is empty");
}
$APPDIR = jBuildUtils::normalizeDir($APPNAME);
$MAIN_TARGET_PATH = jBuildUtils::normalizeDir($MAIN_TARGET_PATH);

Env::setFromFile('VERSION',$APPDIR.'/VERSION',true);
$HG_REVISION = Mercurial::revision(dirname(__FILE__).'/../');

$IS_NIGHTLY = (strpos($VERSION,'SERIAL') !== false);

if($IS_NIGHTLY){
    $PACKAGE_NAME=$APPNAME.'-'.str_replace('SERIAL', '', $VERSION);
    if(substr($PACKAGE_NAME,-1,1) == '.')
      $PACKAGE_NAME = substr($PACKAGE_NAME,0,-1);
    $VERSION = str_replace('SERIAL', $HG_REVISION, $VERSION);
}
else {
    $PACKAGE_NAME=$APPNAME.'-'.$VERSION;
}


if($PACKAGE_TAR_GZ || $PACKAGE_ZIP ){
    //$MAIN_TARGET_PATH = jBuildUtils::normalizeDir($MAIN_TARGET_PATH).$PACKAGE_NAME;
}

//----------------- Génération des sources

//... creation des repertoires
jBuildUtils::createDir($MAIN_TARGET_PATH);

//... execution des manifests
jManifest::process('build/manifests/'.$APPNAME.'.mn', '.', $MAIN_TARGET_PATH, ENV::getAll());


file_put_contents($MAIN_TARGET_PATH.$APPDIR.'/VERSION', $VERSION);

//... packages

if($PACKAGE_TAR_GZ){
    exec('tar czf '.$MAIN_TARGET_PATH.$PACKAGE_NAME.'.tar.gz -C '.$MAIN_TARGET_PATH.' '.$APPNAME);
}

if($PACKAGE_ZIP){
    chdir($MAIN_TARGET_PATH);
    exec('zip -r '.$PACKAGE_NAME.'.zip '.$APPNAME);
    chdir(dirname(__FILE__));
}

exit(0);
?>