<?php
/**
* @package     jelix
* @author      Laurent Jouanneau
* @contributor Kévin Lepeltier
* @copyright   2006-2011 Laurent Jouanneau
* @copyright   2008 Kévin Lepeltier
* @link        http://jelix.org
* @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
*/

$BUILD_OPTIONS = array(
  // each build options item should be an array
  // 0: help (or false for hidden options)
  // 1: the default value (string)
  // 2: a preg expression to verify the given value
  // or
  // 1: a boolean, which indicates that the option is a boolean value
  //    the value of this boolean is the default value
  // 2: no value
  //
  // if an option in the ini file, is empty :
  //   if there isn't a regexp, the result value will be the empty value
  //   if there is a regexp and if it does match, the value will be the empty value
  //   if there is a regexp and if it does not match,  the value will be the default value
'MAIN_TARGET_PATH'=> array(
    "main directory where sources will be copied",
    '_dist',
    '',
    ),
'PHP_VERSION_TARGET'=> array(
    "PHP5 version for which jelix will be generated (by default, the target is php 5.3)",
    '5.3'
    ),
'EDITION_NAME'=> array(
    "The edition name of the version (optional)",
    'dev',
    ),
'ENABLE_PHP_XMLRPC'=>array(
    "true if jelix can use php xmlrpc api",
    false,
    ),
'ENABLE_PHP_JELIX'=>array(
    "true if jelix can use jelix php extension.",
    false,
    ),
'WITH_BYTECODE_CACHE'=> array(
    "says which bytecode cache engine will be recognized by jelix. Possible values :  'auto' (automatic detection), 'apc', 'eaccelerator', 'xcache' or '' for  none",
    'auto',
    '/^(auto|apc|eaccelerator|xcache)?$/',
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
'INCLUDE_ALL_FONTS'=>array(
    "True if you want to include lib/fonts content for tcpdf or other",
    false,
    ),
'PROPERTIES_CHARSET_TARGET'=> array(
    "List of charset used for command cch (convert charset)",
    'UTF-8,ISO-8859-1,ISO-8859-15',
    '',
    ),
'DEFAULT_CHARSET'=> array(
    "The default charset of file. useful when convertir some files (cch command)",
    'UTF-8',
    '',
    ),
'PHP53'=> array(
    false,
    false,
    ),
'PHP54'=> array(
    false,
    false,
    ),
'PHP54ORMORE'=> array(
    false,
    false,
    ),
'DELETE_DEPRECATED_FILES'=> array(
    "If 'on', deprecated files will be deleted",
    true
    ),
'TARGET_REPOSITORY'=> array(
    "The type of the version control system you use on the target directory : none (default), git, hg or svn",
    '',
    '/^(git|svn|hg|rm|none)?$/',
    ),
'SOURCE_REVISION'=> array(
    false,
    ),
'LIB_VERSION'=> array(
    false,
    '',
    ),
'LIB_VERSION_MAX'=> array(
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
'VERBOSE'=> array(
    "show messages",
    false,
    ),
'JTPL_VERSION'=> array(
    false,
    '',
    ),
'TODAY'=> array(
    false,
    '',
    ),
/*''=> array(
    "",
    '',
    '',
    ),*/
);


include(__DIR__.'/lib/jBuild.inc.php');

//----------------- Prepare environment variables

Env::setFromFile('LIB_VERSION','lib/jelix/VERSION', true);
$SOURCE_REVISION = Git::revision(__DIR__.'/../');
$LIB_VERSION = preg_replace('/\s+/m', '', $LIB_VERSION);
$IS_NIGHTLY = (strpos($LIB_VERSION,'SERIAL') !== false);
$TODAY = date('Y-m-d H:i');

if($IS_NIGHTLY){
    $PACKAGE_NAME='jelix-'.str_replace('SERIAL', '', $LIB_VERSION);
    if(substr($PACKAGE_NAME,-1,1) == '.')
      $PACKAGE_NAME = substr($PACKAGE_NAME,0,-1);
    $LIB_VERSION = str_replace('SERIAL', $SOURCE_REVISION, $LIB_VERSION);
}
else {
    $PACKAGE_NAME='jelix-'.$LIB_VERSION;
}

if (preg_match('/\.([a-z0-9\-]+)$/i', $LIB_VERSION, $m))
    $LIB_VERSION_MAX =  substr($LIB_VERSION, 0, - strlen($m[1]))."*";
else
    $LIB_VERSION_MAX = $LIB_VERSION;

if ($PHP_VERSION_TARGET) {
    if (version_compare($PHP_VERSION_TARGET, '5.4') > -1) {
        $PHP54 = 1;
        $PHP54ORMORE = 1;
    }
    elseif (version_compare($PHP_VERSION_TARGET, '5.3') > -1) {
        $PHP53 = 1;
    }
    else {
        die("PHP VERSION ".$PHP_VERSION_TARGET." is not supported");
    }
}else{
    // no defined target, so php 5.3
    $PHP53 = 1;
}

$BUILD_FLAGS = 0;
if($ENABLE_PHP_JELIX)  $BUILD_FLAGS |=1;
if($ENABLE_PHP_XMLRPC)  $BUILD_FLAGS |=4;

switch($WITH_BYTECODE_CACHE){
    case 'auto': $BUILD_FLAGS |=112; break;
    case 'apc': $BUILD_FLAGS |=16; break;
    case 'eaccelerator': $BUILD_FLAGS |=32; break;
    case 'xcache': $BUILD_FLAGS |=64; break;
}
//if($ENABLE_OLD_CLASS_NAMING)  $BUILD_FLAGS |=256;
//if($ENABLE_OLD_ACTION_SELECTOR) $BUILD_FLAGS |= 512;


if($EDITION_NAME ==''){
    $EDITION_NAME_x='userbuild';
    $EDITION_NAME_x.='-f'.$BUILD_FLAGS;
    if($PHP_VERSION_TARGET){
        $EDITION_NAME_x.='-p'.$PHP_VERSION_TARGET;
    }
}else{
    $EDITION_NAME_x = $EDITION_NAME;
}



if( ! $ENABLE_OPTIMIZED_SOURCE)
    $STRIP_COMMENT='';

if($PACKAGE_TAR_GZ || $PACKAGE_ZIP ){

    if($EDITION_NAME_x != '')
        $PACKAGE_NAME.='-'.$EDITION_NAME_x;

    $BUILD_TARGET_PATH = jBuildUtils::normalizeDir($MAIN_TARGET_PATH).$PACKAGE_NAME.'/';
}
else {
    $BUILD_TARGET_PATH = jBuildUtils::normalizeDir($MAIN_TARGET_PATH);
}

if ($TARGET_REPOSITORY == 'none')
  $TARGET_REPOSITORY = '';

if ($TARGET_REPOSITORY != '') {
    $DELETE_DEPRECATED_FILES = true;
}

//----------------- Génération des sources

//... creation des repertoires
jBuildUtils::createDir($BUILD_TARGET_PATH);

jManifest::$stripComment = ($STRIP_COMMENT == '1');
jManifest::$verbose = ($VERBOSE == '1');
jManifest::$usedVcs = $TARGET_REPOSITORY;
jManifest::$sourcePropertiesFilesDefaultCharset = $DEFAULT_CHARSET;
jManifest::$targetPropertiesFilesCharset = $PROPERTIES_CHARSET_TARGET;

if ($DELETE_DEPRECATED_FILES) {
    jManifest::removeFiles('build/manifests/jelix-deprecated.mn', $BUILD_TARGET_PATH);
    if($ENABLE_DEVELOPER){
        jManifest::removeFiles('build/manifests/jelix-deprecated-dev.mn', $BUILD_TARGET_PATH);
    }
}

//... execution des manifests
jManifest::process('build/manifests/jelix-lib.mn', '.', $BUILD_TARGET_PATH, ENV::getAll(), true);
jManifest::process('build/manifests/jelix-www.mn', '.', $BUILD_TARGET_PATH, ENV::getAll(), true);

jManifest::$stripComment = false;

jManifest::process('build/manifests/jelix-vendors.mn', '.', $BUILD_TARGET_PATH , ENV::getAll(), true);
jManifest::process('build/manifests/jelix-scripts.mn','.', $BUILD_TARGET_PATH , ENV::getAll());
jManifest::process('build/manifests/jelix-modules.mn', '.', $BUILD_TARGET_PATH, ENV::getAll(), true);
jManifest::process('build/manifests/jelix-admin-modules.mn', '.', $BUILD_TARGET_PATH, ENV::getAll());

if($INCLUDE_ALL_FONTS){
    jManifest::process('build/manifests/fonts.mn', '.', $BUILD_TARGET_PATH , ENV::getAll());
}

if($ENABLE_PHP_JELIX && ($PACKAGE_TAR_GZ || $PACKAGE_ZIP)){
   jManifest::process('build/manifests/jelix-ext-php.mn', '.', $BUILD_TARGET_PATH , ENV::getAll());
}

// jtpl standalone for wizard

Env::setFromFile('JTPL_VERSION','lib/jelix/tpl/VERSION', true);
if($IS_NIGHTLY){
    $JTPL_VERSION = str_replace('SERIAL', $SOURCE_REVISION, $JTPL_VERSION);
}

$var = ENV::getAll();
$var['JTPL_STANDALONE'] = true;
$jtplpath = $BUILD_TARGET_PATH.'lib/installwizard/jtpl/';
jBuildUtils::createDir($jtplpath);
jManifest::process('build/manifests/jtpl-standalone.mn', '.', $jtplpath, $var);
file_put_contents($jtplpath.'/VERSION', $JTPL_VERSION);


// the standalone checker

$var = ENV::getAll();
$var['STANDALONE_CHECKER'] = true;
jManifest::process('build/manifests/jelix-checker.mn','.', $BUILD_TARGET_PATH , $var);

file_put_contents($BUILD_TARGET_PATH.'lib/jelix/VERSION', $LIB_VERSION);

// create the build info file
$view = array('EDITION_NAME', 'PHP_VERSION_TARGET', 'SOURCE_REVISION',
    'ENABLE_PHP_XMLRPC','ENABLE_PHP_JELIX', 'WITH_BYTECODE_CACHE', 'ENABLE_DEVELOPER',
    'ENABLE_OPTIMIZED_SOURCE', 'STRIP_COMMENT' );

$infos = '; --- build date:  '.$TODAY."\n; --- lib version: $LIB_VERSION\n".ENV::getIniContent($view);

file_put_contents($BUILD_TARGET_PATH.'lib/jelix/BUILD', $infos);

//... packages
if ($PACKAGE_TAR_GZ || $PACKAGE_ZIP) {
  file_put_contents($MAIN_TARGET_PATH.'/PACKAGE_NAME',$PACKAGE_NAME);
}


if($PACKAGE_TAR_GZ){
    exec('tar czf '.$MAIN_TARGET_PATH.'/'.$PACKAGE_NAME.'.tar.gz -C '.$MAIN_TARGET_PATH.' '.$PACKAGE_NAME);
}

if($PACKAGE_ZIP){
    chdir($MAIN_TARGET_PATH);
    exec('zip -r '.$PACKAGE_NAME.'.zip '.$PACKAGE_NAME);
    chdir(__DIR__);
}

exit(0);
