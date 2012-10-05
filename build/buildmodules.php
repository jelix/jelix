<?php
/**
* @package     jelix
* @author      Laurent Jouanneau
* @contributor
* @copyright   2006-2007 Laurent Jouanneau
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
<<<<<<< HEAD
=======
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
'VERBOSE_MODE'=> array(
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
>>>>>>> 766a26a... Fix verbose mode not working with -v switch.
'IS_NIGHTLY'=> array(
    false,
    false,
    ),
'SOURCE_REVISION'=> array(
    false,
    ),
'VERSION'=> array(
    false,
    'SERIAL',
    '',
    ),
);

include(dirname(__FILE__).'/lib/jBuild.inc.php');

//----------------- Preparation des variables d'environnement

$MAIN_TARGET_PATH = jBuildUtils::normalizeDir($MAIN_TARGET_PATH);

$SOURCE_REVISION = Git::revision(dirname(__FILE__).'/../');

if($VERSION == 'SERIAL'){
    $VERSION = 'SERIAL-'.$SOURCE_REVISION;
    $IS_NIGHTLY = true;
}else{
    $IS_NIGHTLY = false;
}


if($PACKAGE_TAR_GZ || $PACKAGE_ZIP ){
    $BUILD_SUBPATH = 'additionnal-modules/';
    if($IS_NIGHTLY)
        $PACKAGE_NAME='additionnal-modules-nightly';
    else
        $PACKAGE_NAME='additionnal-modules-HG-'.$SOURCE_REVISION;
}else{
    $BUILD_SUBPATH = 'lib/jelix-modules/';

}

<<<<<<< HEAD
//----------------- Génération des sources
=======
jManifest::$verbose = ($VERBOSE_MODE == '1');
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
        $packageName = 'module-'.$moduleName.'-nightly';
    }
    else {
        $packageName = 'module-'.$moduleName.'-'.$version;
    }
>>>>>>> 766a26a... Fix verbose mode not working with -v switch.

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
