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
'PHP_VERSION_TARGET'=> array(
    "PHP5 version for which jTpl will be generated (by default, the target is php 5.2)",
    '5.2'
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
'HG_REVISION'=> array(
    false,
    ),
'JTPL_STANDALONE'=> array(
    false,
    '1',
    ),
'PHP52'=> array(
    false,
    false,
    ),
'PHP53'=> array(
    false,
    false,
    ),
'PHP52ORMORE'=> array(
    false,
    false,
    ),
'PHP53ORMORE'=> array(
    false,
    false,
    ),
);

include(dirname(__FILE__).'/lib/jBuild.inc.php');

//----------------- Preparation des variables d'environnement

Env::setFromFile('JTPL_VERSION','lib/jelix/tpl/VERSION', true);
$HG_REVISION = Mercurial::revision(dirname(__FILE__).'/../');

$IS_NIGHTLY = (strpos($JTPL_VERSION,'SERIAL') !== false);

if($IS_NIGHTLY){
    $PACKAGE_NAME='jtpl-'.str_replace('SERIAL', '', $JTPL_VERSION);
    if(substr($PACKAGE_NAME,-1,1) == '.')
      $PACKAGE_NAME = substr($PACKAGE_NAME,0,-1);
    $JTPL_VERSION = str_replace('SERIAL', $HG_REVISION, $JTPL_VERSION);
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
    if(version_compare($PHP_VERSION_TARGET, '5.3') > -1){
        $PHP53 = 1;
        $PHP53ORMORE = 1;
    }elseif(version_compare($PHP_VERSION_TARGET, '5.2') > -1){
        $PHP52 = 1;
        $PHP52ORMORE = 1;
    }else{
        die("PHP VERSION ".$PHP_VERSION_TARGET." is not supported");
    }
}else{
    // no defined target, so php 5.2
    $PHP52=1;
    $PHP2ORMORE=1;
}


//----------------- Génération des sources

//... creation des repertoires
jBuildUtils::createDir($BUILD_TARGET_PATH);

//... execution des manifests
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
    chdir($MAIN_TARGET_PATH);
    exec('zip -r '.$PACKAGE_NAME.'.zip '.$PACKAGE_NAME);
    chdir(dirname(__FILE__));
}

exit(0);
?>