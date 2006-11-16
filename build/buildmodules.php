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
));

Env::initBool(array(
'PACKAGE_TAR_GZ', // indique de créer un paquet tar.gz
'PACKAGE_ZIP', // indique de créer un paquet zip
'NIGHTLY_NAME',
));

//----------------- Preparation des variables d'environnement

$MAIN_TARGET_PATH = jBuildUtils::normalizeDir($MAIN_TARGET_PATH);

$SVN_REVISION = Subversion::revision('lib');

Env::set('MAIN_TARGET_PATH', '_dist/modules/', true);


if($PACKAGE_TAR_GZ || $PACKAGE_ZIP ){
    if($NIGHTLY_NAME)
        $PACKAGE_NAME=$APPNAME.'-nightly';
    else
        $PACKAGE_NAME=$APPNAME.'-SVN-'.$SVN_REVISION;
}

//----------------- Génération des sources

//... creation des repertoires
jBuildUtils::createDir($MAIN_TARGET_PATH);

//... execution des manifests
jManifest::process('build/manifests/jelix-modules.mn', 'lib/jelix-modules/', $MAIN_TARGET_PATH, $GLOBALS);

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