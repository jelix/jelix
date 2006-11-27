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
/*
'LIB_PATH', // plus tard
'TEMP_PATH', // plus tard
'APP_PATH', // plus tard
'WWW_PATH', // plus tard
'SCRIPT_PATH', // plus tard
*/
'PHP_VERSION_TARGET', // version de php pour laquelle il faut générer jelix (5.x)
'LIB_VERSION', // version de lib jelix si on veut forcer un numero de version spécifique
));

if(!isset($GLOBALS['ENABLE_OLD_CLASS_NAMING'])) //TODO à enlever pour la 1.0 finale
    $GLOBALS['ENABLE_OLD_CLASS_NAMING']= '1';

Env::initBool(array(
'ENABLE_OPTIMIZE', // indique que l'on veut une version optimisée pour un serveur de production

// indique les api de php que l'on dispose (jelix n'utilisera alors pas ses propres implementations)
'ENABLE_PHP_FILTER', // indique à jelix d'utiliser l'api filter de php (en standard dans >=5.2)
'ENABLE_PHP_JSON', // indique à jelix d'utiliser l'api json de php  (en standard dans >=5.2)
'ENABLE_PHP_XMLRPC', // indique à jelix d'utiliser l'api xmlrpc de php

'ENABLE_DEVELOPER', // indique de créer une version avec les outils de tests (simpletest &co)
'PACKAGE_TAR_GZ', // indique de créer un paquet tar.gz
'PACKAGE_ZIP', // indique de créer un paquet zip
//'PACKAGE_DEB',
'STRIP_COMMENT',
'NIGHTLY_NAME',

'ENABLE_OLD_CLASS_NAMING', // indique si on veut activer l'ancien nommage de certaines classes dans 
                     // jelix < 1.0beta1
));

//----------------- Preparation des variables d'environnement

Env::setFromFile('LIB_VERSION','lib/jelix/VERSION', true);
$SVN_REVISION = Subversion::revision();

if($LIB_VERSION == 'SVN')
    $LIB_VERSION = 'SVN-'.$SVN_REVISION;

Env::set('MAIN_TARGET_PATH', '_dist', true);
/*
Env::set('LIB_PATH' , $MAIN_TARGET_PATH, true);
Env::set('TEMP_PATH', $MAIN_TARGET_PATH, true);
Env::set('APP_PATH' , $MAIN_TARGET_PATH, true);
Env::set('WWW_PATH' , $MAIN_TARGET_PATH, true);
Env::set('SCRIPT_PATH', $MAIN_TARGET_PATH, true);
*/

if($PHP_VERSION_TARGET){
    if(version_compare($PHP_VERSION_TARGET, '5.2') > -1){
        // filter et json sont en standard dans >=5.2 : on le force
        $ENABLE_PHP_FILTER = 1;
        $ENABLE_PHP_JSON = 1;
        $PHP52 = 1;
    }

    if($PHP_VERSION_TARGET == '5.1') $PHP51=1;
    if($PHP_VERSION_TARGET == '5.0') $PHP50=1;
}else{
    // pas de target définie : donc php 5.0
    $PHP50=1;
}

if(!$ENABLE_OPTIMIZE)
    $STRIP_COMMENT='';

if($PACKAGE_TAR_GZ || $PACKAGE_ZIP ){
    if($NIGHTLY_NAME)
        $PACKAGE_NAME='jelix-nightly';
    else
        $PACKAGE_NAME='jelix-'.$LIB_VERSION;

    if($PHP_VERSION_TARGET)
        $PACKAGE_NAME.='-php'.$PHP_VERSION_TARGET;

    if($ENABLE_OPTIMIZE && $ENABLE_DEVELOPER)
        $PACKAGE_NAME.='-optdev';
    elseif($ENABLE_OPTIMIZE)
        $PACKAGE_NAME.='-opt';
    elseif($ENABLE_DEVELOPER)
        $PACKAGE_NAME.='-dev';


    $BUILD_TARGET_PATH = jBuildUtils::normalizeDir($MAIN_TARGET_PATH).$PACKAGE_NAME.'/';
}else{
    $BUILD_TARGET_PATH = jBuildUtils::normalizeDir($MAIN_TARGET_PATH);
}

//----------------- Génération des sources

//... creation des repertoires
jBuildUtils::createDir($BUILD_TARGET_PATH);

//... execution des manifests
jManifest::process('build/manifests/jelix-lib.mn', '.', $BUILD_TARGET_PATH, $GLOBALS, $STRIP_COMMENT);
if(!$ENABLE_OPTIMIZE){
    jManifest::process('build/manifests/jelix-no-opt.mn', '.', $BUILD_TARGET_PATH , $GLOBALS, $STRIP_COMMENT);
}
if($ENABLE_DEVELOPER){
    jManifest::process('build/manifests/jelix-dev.mn', '.', $BUILD_TARGET_PATH , $GLOBALS);
}
if(!$ENABLE_PHP_JSON){
    jManifest::process('build/manifests/lib-json.mn', '.', $BUILD_TARGET_PATH , $GLOBALS);
}
jManifest::process('build/manifests/jelix-others.mn','.', $BUILD_TARGET_PATH , $GLOBALS);


file_put_contents($BUILD_TARGET_PATH.'lib/jelix/VERSION', $LIB_VERSION);

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