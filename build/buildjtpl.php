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
'VERSION'=> array(
    false,
    '',
    ),
'IS_NIGHTLY'=> array(
    false,
    false,
    ),
'HG_REVISION'=> array(
    false,
    ),
'JTPL_STANDALONE'=> array(
    false,
    '1',
    ),
);

include(dirname(__FILE__).'/lib/jBuild.inc.php');

//----------------- Preparation des variables d'environnement

Env::setFromFile('VERSION','lib/jelix/tpl/VERSION', true);
$HG_REVISION = Mercurial::revision(dirname(__FILE__).'/../');

$IS_NIGHTLY = (strpos($VERSION,'SERIAL') !== false);

if($IS_NIGHTLY){
    $PACKAGE_NAME='jtpl-'.str_replace('SERIAL', '', $VERSION);
    if(substr($PACKAGE_NAME,-1,1) == '.')
      $PACKAGE_NAME = substr($PACKAGE_NAME,0,-1);
    $VERSION = str_replace('SERIAL', $HG_REVISION, $VERSION);
}
else {
    $PACKAGE_NAME='jtpl-'.$VERSION;
}

if($PACKAGE_TAR_GZ || $PACKAGE_ZIP ){
    $BUILD_TARGET_PATH = jBuildUtils::normalizeDir($MAIN_TARGET_PATH).$PACKAGE_NAME.'/';
}else{
    $BUILD_TARGET_PATH = jBuildUtils::normalizeDir($MAIN_TARGET_PATH);
}


//----------------- Génération des sources

//... creation des repertoires
jBuildUtils::createDir($BUILD_TARGET_PATH);

//... execution des manifests
jManifest::process('build/manifests/jtpl-standalone.mn', '.', $BUILD_TARGET_PATH, ENV::getAll());

if($WITH_TESTS) {
    jManifest::process('build/manifests/jtpl-standalone-tests.mn', '.', $BUILD_TARGET_PATH, ENV::getAll());
}



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