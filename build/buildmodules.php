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

//----------------- Preparation des variables d'environnement

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

//----------------- Génération des sources

//... creation des repertoires
jBuildUtils::createDir($MAIN_TARGET_PATH.$BUILD_SUBPATH);

//... execution des manifests
jManifest::process('build/manifests/jelix-modules.mn', 'lib/jelix-modules/', $MAIN_TARGET_PATH.$BUILD_SUBPATH, ENV::getAll());

//... packages

if($PACKAGE_TAR_GZ){
    exec('tar czf '.$MAIN_TARGET_PATH.$PACKAGE_NAME.'.tar.gz -C '.$MAIN_TARGET_PATH.' '.$BUILD_SUBPATH);
}

if($PACKAGE_ZIP){
    chdir($MAIN_TARGET_PATH);
    exec('zip -r '.$PACKAGE_NAME.'.zip '.$BUILD_SUBPATH);
    chdir(dirname(__FILE__));
}

exit(0);
?>
