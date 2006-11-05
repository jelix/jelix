<?php
/**
* @package     jelix
* @author      Jouanneau Laurent
* @contributor
* @copyright   2006 Jouanneau laurent
* @link        http://www.jelix.org
* @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
*/

include(dirname(__FILE__).'/lib/jBuild.inc.php');

Env::init(array(
'MAIN_TARGET_PATH', // repertoire où les sources seront déposées
'VERSION',
'APPNAME',
));

Env::initBool(array(
'PACKAGE_TAR_GZ', // indique de créer un paquet tar.gz
'PACKAGE_ZIP', // indique de créer un paquet zip
'NIGHTLY_NAME',
));

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

Env::set('MAIN_TARGET_PATH', '_dist', true);


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
jManifest::process('build/manifests/'.$APPNAME.'.mn', '.', $MAIN_TARGET_PATH, $GLOBALS);


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