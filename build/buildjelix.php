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
    "PHP5 version for which jelix will be generated (by default, the target is php 7.4)",
    '7.4'
    ),
'ENABLE_DEVELOPER'=>array(
    "include all developers tools in the distribution",
    true,
    ),
'STRIP_COMMENT'=>array(
    "true if you want sources with PHP comments deleted",
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
    'says if it is a nightly or not',
    false,
    ),
'BUILD_FLAGS'=> array(
    false,
    '',
    ),
'VERBOSE_MODE'=> array(
    "show messages",
    false,
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

require(__DIR__.'/vendor/autoload.php');
bt\Cli\Bootstrap::start($BUILD_OPTIONS);

//----------------- Prepare environment variables

Environment::setFromFile('LIB_VERSION','lib/jelix-legacy/VERSION', true);
$SOURCE_REVISION = bt\FileSystem\Git::revision(__DIR__.'/../');
$LIB_VERSION = preg_replace('/\s+/m', '', $LIB_VERSION);
$TODAY = date('Y-m-d H:i');
$PACKAGE_NAME='jelix-'.$LIB_VERSION;

if ($IS_NIGHTLY) {
    $LIB_VERSION .= '.'. $SOURCE_REVISION;
}

if (preg_match('/^[0-9]+\.[0-9]+\.([a-z0-9\-\.]+)$/i', $LIB_VERSION, $m))
    $LIB_VERSION_MAX =  substr($LIB_VERSION, 0, - strlen($m[1]))."*";
else
    $LIB_VERSION_MAX = $LIB_VERSION;

if ($PHP_VERSION_TARGET) {
    if (version_compare($PHP_VERSION_TARGET, '7.4') == -1) {
        die("PHP VERSION ".$PHP_VERSION_TARGET." is not supported");
    }
}

$BUILD_FLAGS = 0;

if ($PACKAGE_TAR_GZ || $PACKAGE_ZIP ) {
    $BUILD_TARGET_PATH = DirUtils::normalizeDir($MAIN_TARGET_PATH).$PACKAGE_NAME.'/';
}
else {
    $BUILD_TARGET_PATH = DirUtils::normalizeDir($MAIN_TARGET_PATH);
}

if ($TARGET_REPOSITORY == 'none') {
    $TARGET_REPOSITORY = '';
}

//----------------- Package building

//... creating directories
DirUtils::createDir($BUILD_TARGET_PATH);

Manifest::$stripComment = ($STRIP_COMMENT == '1');
Manifest::$verbose = ($VERBOSE_MODE == '1');
Manifest::setFileSystem($TARGET_REPOSITORY);
Manifest::$sourcePropertiesFilesDefaultCharset = $DEFAULT_CHARSET;
Manifest::$targetPropertiesFilesCharset = $PROPERTIES_CHARSET_TARGET;

//... processing manifest
$repoDir = realpath(__DIR__.'/../');
Manifest::process('build/manifests/jelix-lib.mn', $repoDir, $BUILD_TARGET_PATH, Environment::getAll(), true);
Manifest::process('build/manifests/jelix-www.mn', $repoDir, $BUILD_TARGET_PATH, Environment::getAll(), true);

Manifest::$stripComment = false;

Manifest::process('build/manifests/jelix-scripts.mn',$repoDir, $BUILD_TARGET_PATH , Environment::getAll());
Manifest::process('build/manifests/jelix-modules.mn', $repoDir, $BUILD_TARGET_PATH, Environment::getAll(), true);
Manifest::process('build/manifests/jelix-admin-modules.mn', $repoDir, $BUILD_TARGET_PATH, Environment::getAll());

// jtpl standalone for wizard

$var = Environment::getAll();

file_put_contents($BUILD_TARGET_PATH.'lib/jelix-legacy/VERSION', $LIB_VERSION);

// create the build info file
$view = array('PHP_VERSION_TARGET', 'SOURCE_REVISION', 'ENABLE_DEVELOPER', 'STRIP_COMMENT' );

$infos = '; --- build date:  '.$TODAY."\n; --- lib version: $LIB_VERSION\n".Environment::getIniContent($view);

file_put_contents($BUILD_TARGET_PATH.'lib/jelix-legacy/BUILD', $infos);


if ($IS_NIGHTLY) {
    require(__DIR__.'/changeVersion.lib.php');
    $modifier = new ChangeVersion($BUILD_TARGET_PATH);
    $modifier->changeVersionInJelix($LIB_VERSION);
}

//... packages
$oldpath = getcwd();
if ($PACKAGE_TAR_GZ || $PACKAGE_ZIP) {
  file_put_contents($MAIN_TARGET_PATH.'/PACKAGE_NAME',$PACKAGE_NAME);
  chdir($BUILD_TARGET_PATH.'/lib');
  exec('composer install --prefer-dist --no-dev --no-progress --no-ansi --no-interaction --quiet');
  unlink('composer.json');
  unlink('composer.lock');
  chdir($oldpath);
}

if($PACKAGE_TAR_GZ){
    exec('tar czf '.$MAIN_TARGET_PATH.'/'.$PACKAGE_NAME.'.tar.gz -C '.$MAIN_TARGET_PATH.' '.$PACKAGE_NAME);
}

if($PACKAGE_ZIP){
    chdir($MAIN_TARGET_PATH);
    exec('zip -r '.$PACKAGE_NAME.'.zip '.$PACKAGE_NAME);
    
}
chdir($oldpath);
exit(0);
