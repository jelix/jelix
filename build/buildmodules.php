<?php
/**
* @package     jelix
* @author      Laurent Jouanneau
* @contributor
* @copyright   2006-2012 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
*/
$BUILD_OPTIONS = array(
'MAIN_TARGET_PATH'=> array(
    "main directory where sources will be copied",  // meaning (false = hidden otion)
    '_dist',                                        // default value (boolean = boolean option)
    '',                                             // regexp for the value or empty=all (only for non-boolean options)
    ),
'TARGET_REPOSITORY'=> array(
    "The type of the version control system you use on the target directory : none (default), git, hg or svn",
    '',
    '/^(git|svn|hg|rm|none)?$/',
    ),
'PHP_VERSION_TARGET'=> array(
    "PHP5 version for which jelix will be generated (by default, the target is php 5.3)",
    '5.3'
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
'DELETE_DEPRECATED_FILES'=> array(
    "If 'on', deprecated files will be deleted",
    true
    ),
'TARGET_REPOSITORY'=> array(
    "The type of the version control system you use on the target directory : none (default), git, hg or svn",
    '',
    '/^(git|svn|hg|rm|none)?$/',
    ),
'VERBOSE'=> array(
    "show messages",
    false,
    ),
'SOURCE_REVISION'=> array(
    false,
    ),
'TODAY'=> array(
    false,
    '',
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
);

include(__DIR__.'/lib/jBuild.inc.php');

//----------------- Prepare environment variables

$MAIN_TARGET_PATH = jBuildUtils::normalizeDir($MAIN_TARGET_PATH);

$SOURCE_REVISION = Git::revision(__DIR__.'/../');
$TODAY = date('Y-m-d H:i');

Env::setFromFile('LIB_VERSION','lib/jelix/VERSION', true);
$LIB_VERSION = preg_replace('/\s+/m', '', $LIB_VERSION);
$IS_NIGHTLY = (strpos($LIB_VERSION,'SERIAL') !== false);

if($IS_NIGHTLY){
    $LIB_VERSION = str_replace('SERIAL', $SOURCE_REVISION, $LIB_VERSION);
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


if ($PACKAGE_TAR_GZ || $PACKAGE_ZIP ) {
    $BUILD_SUBPATH = 'additionnal-modules/';
}
else {
    $BUILD_SUBPATH = 'lib/jelix-modules/';
}

if ($TARGET_REPOSITORY == 'none')
    $TARGET_REPOSITORY = '';

if ($TARGET_REPOSITORY != '') {
    $DELETE_DEPRECATED_FILES = true;
}

jManifest::$verbose = ($VERBOSE == '1');
jManifest::$usedVcs = $TARGET_REPOSITORY;
jManifest::$sourcePropertiesFilesDefaultCharset = $DEFAULT_CHARSET;
jManifest::$targetPropertiesFilesCharset = $PROPERTIES_CHARSET_TARGET;

//----------------- Source generation

function generateModulePackage($moduleName, $modulesRepositoryPath) {
    extract(ENV::getAll());
    global $BUILD_SUBPATH;

    // retrieve the version from module.xml
    $xml = simplexml_load_file($modulesRepositoryPath.'/'.$moduleName.'/module.xml');
    $version = (string) $xml->info[0]->version;
    if ($IS_NIGHTLY) {
        $version .= '.'.$SOURCE_REVISION;
        $packageName = $moduleName.'-nightly';
    }
    else {
        $packageName = $moduleName.'-'.$version;
    }

    // create the directory
    jBuildUtils::createDir($MAIN_TARGET_PATH.$BUILD_SUBPATH);
    // execute manifest
    if ($DELETE_DEPRECATED_FILES && file_exists('build/manifests/modules/'.$moduleName.'-deprecated.mn')) {
        jManifest::removeFiles('build/manifests/modules/'.$moduleName.'-deprecated.mn', $MAIN_TARGET_PATH.$BUILD_SUBPATH);
    }

    jManifest::process('build/manifests/modules/'.$moduleName.'.mn', $modulesRepositoryPath , $MAIN_TARGET_PATH.$BUILD_SUBPATH, ENV::getAll());


    
    // do package
    if($PACKAGE_TAR_GZ){
        exec('tar czf '.$MAIN_TARGET_PATH.$packageName.'.tar.gz -C '.$MAIN_TARGET_PATH.$BUILD_SUBPATH. ' '. $moduleName);
    }

    if($PACKAGE_ZIP){
        $dir = getcwd();
        chdir($MAIN_TARGET_PATH.$BUILD_SUBPATH);
echo 'zip -r '.$dir.'/'.$MAIN_TARGET_PATH.$packageName.'.zip '.$moduleName."\n";
        exec('zip -r '.$dir.'/'.$MAIN_TARGET_PATH.$packageName.'.zip '.$moduleName);
        chdir($dir);
    }
    return;
}


//generateModulePackage('jtcpdf', 'lib/jelix-modules/');




exit(0);
