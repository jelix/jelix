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
    "main directory where sources will be copied",  // signification (false = option cachée)
    '_dist',                                        // valeur par défaut (boolean = option booleene)
    '',                                             // regexp pour la valeur ou vide=tout (seulement pour option non booleene)
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
include(dirname(__FILE__).'/lib/jBuild.inc.php');

//----------------- Preparation des variables d'environnement

Env::setFromFile('VERSION','build/VERSION', true);
$VERSION = preg_replace('/\s+/m', '', $VERSION);
$SOURCE_REVISION = Git::revision(dirname(__FILE__).'/../');

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


//----------------- Génération des sources

//... creation des repertoires
jBuildUtils::createDir($BUILD_TARGET_PATH);

//... execution des manifests
jManifest::process('build/manifests/jbuildtools.mn', 'build/', $BUILD_TARGET_PATH, ENV::getAll());


file_put_contents($BUILD_TARGET_PATH.'/VERSION', $VERSION);

//... packages

if($PACKAGE_TAR_GZ){
    exec('tar czf '.$MAIN_TARGET_PATH.'/'.$PACKAGE_NAME.'.tar.gz -C '.$MAIN_TARGET_PATH.' '.$PACKAGE_NAME);
}

if($PACKAGE_ZIP){
    chdir($MAIN_TARGET_PATH);
    exec('zip -r '.$PACKAGE_NAME.'.zip '.$PACKAGE_NAME);
    chdir(dirname(__FILE__));
}

exit(0);
?>