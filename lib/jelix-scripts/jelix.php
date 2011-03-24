<?php
/**
* @package     jelix-scripts
* @author      Laurent Jouanneau
* @contributor Loic Mathaud
* @copyright   2005-2011 Laurent Jouanneau
* @link        http://jelix.org
* @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
*/

error_reporting(E_ALL);
define ('JELIX_SCRIPTS_PATH', dirname(__FILE__).'/');

function GetAppsRepository($relatedPath) {
    $path = realpath(dirname(__FILE__).'/'.$relatedPath);
    $last = substr($path, -1,1);
    if ($last == '\\' || $last == '/')
        $path = substr($path, 0, -1);
    return $path;
}

// ------------- retrieve the name of the jelix command and the name of the application

if ($_SERVER['argc'] < 2) {
    echo "Error: command is missing. See '".$_SERVER['argv'][0]." help'.\n";
    exit(1);
}

$argv = $_SERVER['argv'];
$scriptName = array_shift($argv); // shift the script name
$commandName = array_shift($argv); // get the command name

// verify if the first argument is the application name
if (preg_match('/^\-\-([\w\-\.:]+)$/', $commandName, $m)) {
    $APPNAME = $m[1];
    if ($_SERVER['argc'] < 3) {
       echo "Error: command is missing. See '".$scriptName." help'.\n";
       exit(1);
    }
    $commandName = array_shift($argv);
}
else {
    if (isset($_SERVER['JELIX_APP_NAME'])) {
        $APPNAME = $_SERVER['JELIX_APP_NAME'];
    }
    else {
        $APPNAME='';
    }
}

$entryPointName = '';
if ( ($p = strpos($APPNAME, ':')) !== false) {
    $entryPointName = substr($APPNAME, $p+1);
    $APPNAME = substr($APPNAME, 0, $p);
}

$allEntryPoint = false;
if ($entryPointName == '') {
    $entryPointName = 'index.php';
    $entryPointId = 'index';
    $allEntryPoint = true;
}
else if (($p =strpos($entryPointName,'.php')) === false) {
    $entryPointId = $entryPointName;
    $entryPointName.='.php';
}
else {
    $entryPointId = substr($entryPointName, 0, $p);
}


// --------------  Load the command object

include('includes/command.class.php');
include('includes/utils.lib.php');

$command = jxs_load_command($commandName);

if ($APPNAME == '' && $command->applicationRequired) {
    echo "Error: an application name is required\n";
    exit(1);
}

// --------------  retrieve the configuration for the script commands

if (!isset($_SERVER['JELIX_CONFIG'])) {
    if ($APPNAME != '') {
        $jelix_config = JELIX_SCRIPTS_PATH.'my.'.$APPNAME.'.conf.php';
        if (!file_exists($jelix_config)) {
            $jelix_config = JELIX_SCRIPTS_PATH.'my.default.conf.php';
        }
    }
    else {
        $jelix_config = JELIX_SCRIPTS_PATH.'my.default.conf.php';
    }
    if (file_exists($jelix_config)) {
        require($jelix_config);
    }

}
elseif (!file_exists($_SERVER['JELIX_CONFIG'])) {
    echo("Error: path given by the JELIX_CONFIG environnement variable doesn't exist (".$_SERVER['JELIX_CONFIG']." )\n");
    exit(1);
}
else {
    require($_SERVER['JELIX_CONFIG']);
}

require(JELIX_SCRIPTS_PATH.'default.conf.php');

if (file_exists(JELIXS_APPTPL_PATH) && file_exists(JELIXS_APPTPL_PATH.'application.init.php')) {

    include (JELIXS_APPTPL_PATH.'application.init.php');
    jApp::setEnv('jelix-scripts');

    if(!class_exists('jCoordinator', false)) { // for old application.init.php which doesn't include init.php
        echo "Error: your application.init.php should include the lib/jelix/init.php";
        exit(1);
    }

    jApp::initLegacy();

    $tempBasePath = jApp::tempBasePath();

    // we always clean the temp directory. But first, let's check some values (see ticket #840)...

    if ($tempBasePath == DIRECTORY_SEPARATOR || $tempBasePath == '' || $tempBasePath == '/') {
        echo "Error: bad path in jApp::tempBasePath(), it is equals to '".$tempBasePath."' !!\n";
        echo "       Jelix cannot clear the content of the temp directory.\n";
        echo "       Correct the path for the temp directory or create the directory you\n";
        echo "       indicated with jApp in your application.init.php.\n";
        exit(1);
    }

    jFile::removeDir(jApp::tempPath(), false, array('.svn', '.dummy'));
}
else {
    if ($command->applicationMustExist) {
        echo("Error: the given application doesn't exist (".JELIXS_APPTPL_PATH." )\n");
        exit(1);
    }
    include (JELIXS_INIT_PATH);
    jApp::initPaths(
        JELIXS_APPTPL_PATH,
        JELIXS_APPTPL_WWW_PATH,
        JELIXS_APPTPL_VAR_PATH,
        JELIXS_APPTPL_LOG_PATH,
        JELIXS_APPTPL_CONFIG_PATH,
        JELIXS_APPTPL_CMD_PATH
    );
    jApp::setTempBasePath(JELIXS_APPTPL_TEMP_PATH);
    jApp::setEnv('jelix-scripts');
}




if(function_exists('date_default_timezone_set')){
    date_default_timezone_set(JELIXS_INFO_DEFAULT_TIMEZONE);
}

if (DEBUG_MODE)
    set_error_handler('jlx_error_handler');

// ---------  retrieve options and parameters from the command line

list($options,$parameters) = jxs_getOptionsAndParams($argv, $command->allowed_options, $command->allowed_parameters);

// --------- launch the command now

$command->init($options,$parameters);

try {
    $command->run();
}
catch (Exception $e) {
    echo "Error: ".$e->getMessage()."\n";
    if (DEBUG_MODE) {
        echo $e->getFile(). "  line ".$e->getLine()."\n";
        foreach($e->getTrace() as $k=>$t){
            echo "\n\t$k\t".(isset($t['class'])?$t['class'].$t['type']:'').$t['function']."()\t";
            echo (isset($t['file'])?$t['file']:'[php]').' : '.(isset($t['line'])?$t['line']:'');
        }
        echo "\n";
    }
    exit(1);
}

exit(0);
