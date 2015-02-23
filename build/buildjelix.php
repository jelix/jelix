<?php
/**
* @package     jelix
* @author      Laurent Jouanneau
* @contributor Kévin Lepeltier
* @copyright   2006-2015 Laurent Jouanneau
* @copyright   2008 Kévin Lepeltier
* @link        http://jelix.org
* @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
*/
use Jelix\BuildTools as bt;
use Jelix\BuildTools\Cli\Environment as Environment;
use Jelix\BuildTools\Manifest\Manager as Manifest;
use Jelix\BuildTools\FileSystem\DirUtils as DirUtils;

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
'ENABLE_DEVELOPER'=>array(
    "include all developers tools in the distribution",
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
'VERBOSE_MODE'=> array(
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

require(__DIR__.'/../vendor/autoload.php');
bt\Cli\Bootstrap::start($BUILD_OPTIONS);

//----------------- Prepare environment variables

Environment::setFromFile('LIB_VERSION','lib/jelix-legacy/VERSION', true);
$SOURCE_REVISION = bt\FileSystem\Git::revision(__DIR__.'/../');
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

    $BUILD_TARGET_PATH = DirUtils::normalizeDir($MAIN_TARGET_PATH).$PACKAGE_NAME.'/';
}
else {
    $BUILD_TARGET_PATH = DirUtils::normalizeDir($MAIN_TARGET_PATH);
}

if ($TARGET_REPOSITORY == 'none')
  $TARGET_REPOSITORY = '';

if ($TARGET_REPOSITORY != '') {
    $DELETE_DEPRECATED_FILES = true;
}

//----------------- Génération des sources

//... creation des repertoires
DirUtils::createDir($BUILD_TARGET_PATH);

Manifest::$stripComment = ($STRIP_COMMENT == '1');
Manifest::$verbose = ($VERBOSE_MODE == '1');
Manifest::setFileSystem($TARGET_REPOSITORY);
Manifest::$sourcePropertiesFilesDefaultCharset = $DEFAULT_CHARSET;
Manifest::$targetPropertiesFilesCharset = $PROPERTIES_CHARSET_TARGET;

if ($DELETE_DEPRECATED_FILES) {
    Manifest::removeFiles('build/manifests/jelix-deprecated.mn', $BUILD_TARGET_PATH);
    if($ENABLE_DEVELOPER){
        Manifest::removeFiles('build/manifests/jelix-deprecated-dev.mn', $BUILD_TARGET_PATH);
    }
}

//... execution des manifests
Manifest::process('build/manifests/jelix-lib.mn', '.', $BUILD_TARGET_PATH, Environment::getAll(), true);
Manifest::process('build/manifests/jelix-www.mn', '.', $BUILD_TARGET_PATH, Environment::getAll(), true);

Manifest::$stripComment = false;

Manifest::process('build/manifests/jelix-vendors.mn', '.', $BUILD_TARGET_PATH , Environment::getAll(), true);
Manifest::process('build/manifests/jelix-scripts.mn','.', $BUILD_TARGET_PATH , Environment::getAll());
Manifest::process('build/manifests/jelix-modules.mn', '.', $BUILD_TARGET_PATH, Environment::getAll(), true);
Manifest::process('build/manifests/jelix-admin-modules.mn', '.', $BUILD_TARGET_PATH, Environment::getAll());

// jtpl standalone for wizard

Environment::setFromFile('JTPL_VERSION','lib/jelix-legacy/tpl/VERSION', true);
if($IS_NIGHTLY){
    $JTPL_VERSION = str_replace('SERIAL', $SOURCE_REVISION, $JTPL_VERSION);
}

$var = Environment::getAll();
$jtplpath = $BUILD_TARGET_PATH.'lib/installwizard/jtpl/';
DirUtils::createDir($jtplpath);
Manifest::process('build/manifests/jtpl-standalone.mn', '.', $jtplpath, $var);
file_put_contents($jtplpath.'/VERSION', $JTPL_VERSION);


// the standalone checker

$var = Environment::getAll();
Manifest::process('build/manifests/jelix-checker.mn','.', $BUILD_TARGET_PATH , $var);

file_put_contents($BUILD_TARGET_PATH.'lib/jelix-legacy/VERSION', $LIB_VERSION);

// create the build info file
$view = array('EDITION_NAME', 'PHP_VERSION_TARGET', 'SOURCE_REVISION',
    'ENABLE_DEVELOPER',
    'ENABLE_OPTIMIZED_SOURCE', 'STRIP_COMMENT' );

$infos = '; --- build date:  '.$TODAY."\n; --- lib version: $LIB_VERSION\n".Environment::getIniContent($view);

file_put_contents($BUILD_TARGET_PATH.'lib/jelix-legacy/BUILD', $infos);

//... packages
if ($PACKAGE_TAR_GZ || $PACKAGE_ZIP) {
  file_put_contents($MAIN_TARGET_PATH.'/PACKAGE_NAME',$PACKAGE_NAME);
}


if($PACKAGE_TAR_GZ){
    exec('tar czf '.$MAIN_TARGET_PATH.'/'.$PACKAGE_NAME.'.tar.gz -C '.$MAIN_TARGET_PATH.' '.$PACKAGE_NAME);
}

if($PACKAGE_ZIP){
    $oldpath = getcwd();
    chdir($MAIN_TARGET_PATH);
    exec('zip -r '.$PACKAGE_NAME.'.zip '.$PACKAGE_NAME);
    chdir($oldpath);
}

exit(0);
