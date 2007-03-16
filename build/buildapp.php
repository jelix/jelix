<?php
/**
* @package     jelix
* @author      Jouanneau Laurent
* @contributor
* @copyright   2006-2007 Jouanneau laurent
* @link        http://www.jelix.org
* @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
*/
$BUILD_OPTIONS = array(
'MAIN_TARGET_PATH'=> array(
    "main directory where sources will be copied",  // signification (false = option cachée)
    '_dist',                                        // valeur par défaut (boolean = option booleene)
    '',                                             // regexp pour la valeur ou vide=tout (seulement pour option non booleene)
    ), 
'VERSION'=> array(
    "Version number you want to set for this package",
    '',
    '',
    ),
'APPNAME'=> array(
    "The name of the app you want to generate (demoxul, myapp, testapp)",
    '',
    '/demoxul|myapp|testapp/',
    ),
'PACKAGE_TAR_GZ'=>array(
    "create a tar.gz package",
    false,
    ),
'PACKAGE_ZIP'=>array(
    "create a zip package",
    false,
    ),
'NIGHTLY_NAME'=>array(
    "",
    true,
    ),
'SVN_REVISION'=> array(
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

Env::setFromFile('VERSION',$APPDIR.'/VERSION', true);
$SVN_REVISION = Subversion::revision($APPDIR);

if($VERSION == 'SVN')
    $VERSION = 'SVN-'.$SVN_REVISION;

if($PACKAGE_TAR_GZ || $PACKAGE_ZIP ){
    if($NIGHTLY_NAME)
        $PACKAGE_NAME=$APPNAME.'-nightly';
    else
        $PACKAGE_NAME=$APPNAME.'-'.$VERSION;
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