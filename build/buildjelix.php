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
'PHP_VERSION_TARGET'=> array(
    "PHP5 version for which jelix will be generated (by default, the target is php 5.1)",
    '5.1'
    ),
'EDITION_NAME'=> array(
    "The edition name of the version (optional)",
    '',
    ),
'ENABLE_PHP_FILTER'=>array(
    "true if jelix can use php filter api (api included in PHP>=5.2)",
    false,
    ),
'ENABLE_PHP_JSON'=>array(
    "true if jelix can use php json api (api included in PHP>=5.2)",
    false,
    ),
'ENABLE_PHP_XMLRPC'=>array(
    "true if jelix can use php xmlrpc api",
    false,
    ),
'ENABLE_PHP_JELIX'=>array(
    "true if jelix can use jelix php extension. WARNING ! EXPERIMENTAL !",
    false,
    ),
'WITH_BYTECODE_CACHE'=> array(
    "says which bytecode cache engine will be recognized by jelix. Possible values :  'auto' (automatic detection), 'apc', 'eaccelerator' or '' for  none",
    'auto',
    '/^(auto|apc|eaccelerator)?$/',
    ),
'ENABLE_DEVELOPER'=>array(
    "include all developers tools in the distribution (simpletest &cie)",
    true,
    ),
'ENABLE_OPTIMIZED_SOURCE'=>array(
    "true if you want on optimized version of source code, for production server",
    false,
    ),
'STRIP_COMMENT'=>array(
    "true if you want sources with PHP comments deleted (valid only if ENABLE_OPTIMIZED_SOURCE is true)",
    false,
    ),
'PACKAGE_TAR_GZ'=>array(
    "create a tar.gz package",
    false,
    ),
'PACKAGE_ZIP'=>array(
    "create a zip package",
    false,
    ),
'ENABLE_OLD_CLASS_NAMING'=>array(
    "old module class naming (jelix <= 1.0a5) can be used. deprecated for Jelix 1.0 and higher.",
    false,
    ),
'ENABLE_OLD_ACTION_SELECTOR'=>array(
    "old action selector can be used. deprecated for Jelix 1.1 and higher.",
    true,
    ),
'INCLUDE_ALL_FONTS'=>array(
    "True if you want to include lib/fonts content for tcpdf or other",
    false,
    ),
'PHP50'=> array(
    false,   // hidden option
    false,
    ),
'PHP51'=> array(
    false,
    false,
    ),
'PHP52'=> array(
    false,
    false,
    ),
'SVN_REVISION'=> array(
    false,
    ),
'LIB_VERSION'=> array(
    false,
    '',
    ),
'IS_NIGHTLY'=> array(
    false,
    false,
    ),
'BUILD_FLAGS'=> array(
    false,
    '',
    ),
'EDITION_NAME_x'=> array(
    false,
    '',
    ),
/*''=> array(
    "",
    '',
    '',
    ),*/
);


include(dirname(__FILE__).'/lib/jBuild.inc.php');

//----------------- Preparation des variables d'environnement

Env::setFromFile('LIB_VERSION','lib/jelix/VERSION', true);
$SVN_REVISION = Subversion::revision();

if($LIB_VERSION == 'SVN'){
    $LIB_VERSION = 'SVN-'.$SVN_REVISION;
    $IS_NIGHTLY = true;
}else{
    $IS_NIGHTLY = false;
}




if($PHP_VERSION_TARGET){
    if(version_compare($PHP_VERSION_TARGET, '5.2') > -1){
        // filter et json sont en standard dans >=5.2 : on le force
        $ENABLE_PHP_FILTER = 1;
        $ENABLE_PHP_JSON = 1;
        $PHP52 = 1;
    }elseif(version_compare($PHP_VERSION_TARGET, '5.1') > -1){
        $PHP51=1;
    }else{
        $PHP50=1;
    }
}else{
    // pas de target définie : donc php 5.0
    $PHP50=1;
}

$BUILD_FLAGS = '';
if($ENABLE_PHP_JELIX)  $BUILD_FLAGS.='j';
if($ENABLE_PHP_JSON)  $BUILD_FLAGS.='s';
if($ENABLE_PHP_XMLRPC)  $BUILD_FLAGS.='x';
if($ENABLE_PHP_FILTER)  $BUILD_FLAGS.='f';
if($ENABLE_OLD_CLASS_NAMING)  $BUILD_FLAGS.='c';
switch($WITH_BYTECODE_CACHE){
    case 'auto': $BUILD_FLAGS.='o'; break;
    case 'apc': $BUILD_FLAGS.='a'; break;
    case 'eaccelerator': $BUILD_FLAGS.='e'; break;
}

if($EDITION_NAME ==''){
    $EDITION_NAME_x='userbuild';
    if($BUILD_FLAGS !=''){
        $EDITION_NAME_x.='-'.$BUILD_FLAGS;
    }
    if($PHP_VERSION_TARGET){
        $EDITION_NAME_x.='-p'.$PHP_VERSION_TARGET;
    }
}else{
    $EDITION_NAME_x = $EDITION_NAME;
}



if( ! $ENABLE_OPTIMIZED_SOURCE)
    $STRIP_COMMENT='';

if($PACKAGE_TAR_GZ || $PACKAGE_ZIP ){
    if($IS_NIGHTLY)
        $PACKAGE_NAME='jelix-nightly';
    else
        $PACKAGE_NAME='jelix-'.$LIB_VERSION;

    if($EDITION_NAME_x != '')
        $PACKAGE_NAME.='-'.$EDITION_NAME_x;

    $BUILD_TARGET_PATH = jBuildUtils::normalizeDir($MAIN_TARGET_PATH).$PACKAGE_NAME.'/';
}else{
    $BUILD_TARGET_PATH = jBuildUtils::normalizeDir($MAIN_TARGET_PATH);
}

//----------------- Génération des sources

//... creation des repertoires
jBuildUtils::createDir($BUILD_TARGET_PATH);

//... execution des manifests
jManifest::process('build/manifests/jelix-lib.mn', '.', $BUILD_TARGET_PATH, ENV::getAll(), $STRIP_COMMENT);
if( ! $ENABLE_OPTIMIZED_SOURCE){
    jManifest::process('build/manifests/jelix-no-opt.mn', '.', $BUILD_TARGET_PATH , ENV::getAll(), $STRIP_COMMENT);
}
if( ! $ENABLE_PHP_JELIX && ! $ENABLE_OPTIMIZED_SOURCE){
    jManifest::process('build/manifests/jelix-no-ext.mn', '.', $BUILD_TARGET_PATH , ENV::getAll(), $STRIP_COMMENT);
}

if($ENABLE_DEVELOPER){
    jManifest::process('build/manifests/jelix-dev.mn', '.', $BUILD_TARGET_PATH , ENV::getAll());
}
if(!$ENABLE_PHP_JSON){
    jManifest::process('build/manifests/lib-json.mn', '.', $BUILD_TARGET_PATH , ENV::getAll());
}
jManifest::process('build/manifests/jelix-others.mn','.', $BUILD_TARGET_PATH , ENV::getAll());

if($INCLUDE_ALL_FONTS){
    jManifest::process('build/manifests/fonts.mn', '.', $BUILD_TARGET_PATH , ENV::getAll());
}

if($ENABLE_PHP_JELIX && ($PACKAGE_TAR_GZ || $PACKAGE_ZIP)){
   jManifest::process('build/manifests/jelix-ext-php.mn', '.', $BUILD_TARGET_PATH , ENV::getAll());
}

file_put_contents($BUILD_TARGET_PATH.'lib/jelix/VERSION', $LIB_VERSION);

// creation du fichier d'infos sur le build
$view = array('EDITION_NAME', 'PHP_VERSION_TARGET', 'SVN_REVISION', 'ENABLE_PHP_FILTER',
    'ENABLE_PHP_JSON', 'ENABLE_PHP_XMLRPC','ENABLE_PHP_JELIX', 'WITH_BYTECODE_CACHE', 'ENABLE_DEVELOPER',
    'ENABLE_OPTIMIZED_SOURCE', 'STRIP_COMMENT', 'ENABLE_OLD_CLASS_NAMING', 'ENABLE_OLD_ACTION_SELECTOR' );

$infos = '; --- build date:  '.date('Y-m-d H:i')."\n; --- lib version: $LIB_VERSION\n".ENV::getIniContent($view);

file_put_contents($BUILD_TARGET_PATH.'lib/jelix/BUILD', $infos);

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