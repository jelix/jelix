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

$MAIN_TARGET_PATH = jBuildUtils::normalizeDir($MAIN_TARGET_PATH);

$SVN_REVISION = Subversion::revision('lib');

Env::set('MAIN_TARGET_PATH', '_dist/modules/', true);


if($PACKAGE_TAR_GZ || $PACKAGE_ZIP ){
    $BUILD_SUBPATH = 'additionnal-modules/';
    if($NIGHTLY_NAME)
        $PACKAGE_NAME='additionnal-modules-nightly';
    else
        $PACKAGE_NAME='additionnal-modules-SVN-'.$SVN_REVISION;
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