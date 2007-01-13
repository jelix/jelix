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
'JTPL_STANDALONE'=> array(
    false,
    '1',
    ),
);

include(dirname(__FILE__).'/lib/jBuild.inc.php');

//----------------- Preparation des variables d'environnement

Env::setFromFile('VERSION','lib/jelix/tpl/VERSION', true);
$SVN_REVISION = Subversion::revision();

if($VERSION == 'SVN')
    $VERSION = 'SVN-'.$SVN_REVISION;

Env::set('MAIN_TARGET_PATH', '_dist', true);
Env::set('JTPL_STANDALONE','1');

if($PACKAGE_TAR_GZ || $PACKAGE_ZIP ){
    if($NIGHTLY_NAME)
        $PACKAGE_NAME = 'jtpl-nightly';
    else
        $PACKAGE_NAME = 'jtpl-'.$VERSION;

    $BUILD_TARGET_PATH = jBuildUtils::normalizeDir($MAIN_TARGET_PATH).$PACKAGE_NAME.'/';
}else{
    $BUILD_TARGET_PATH = jBuildUtils::normalizeDir($MAIN_TARGET_PATH);
}


//----------------- Génération des sources

//... creation des repertoires
jBuildUtils::createDir($BUILD_TARGET_PATH);

//... execution des manifests
jManifest::process('build/manifests/jtpl-standalone.mn', '.', $BUILD_TARGET_PATH, ENV::getAll());


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