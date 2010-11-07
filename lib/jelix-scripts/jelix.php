<?php
/**
* @package     jelix-scripts
* @author      Laurent Jouanneau
* @contributor Loic Mathaud
* @copyright   2005-2010 Laurent Jouanneau
* @link        http://jelix.org
* @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
*/

error_reporting(E_ALL);
define ('JELIX_SCRIPT_PATH', dirname(__FILE__).'/');

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
        $jelix_config = JELIX_SCRIPT_PATH.'my.'.$APPNAME.'.conf.php';
        if (!file_exists($jelix_config)) {
            $jelix_config = JELIX_SCRIPT_PATH.'my.default.conf.php';
        }
    }
    else {
        $jelix_config = JELIX_SCRIPT_PATH.'my.default.conf.php';
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

require(JELIX_SCRIPT_PATH.'default.conf.php');

if (file_exists(JELIXS_APPTPL_PATH) && file_exists(JELIXS_APPTPL_PATH.'application.init.php')) {

    if(!file_exists(JELIXS_APPTPL_PATH.'jelix-scripts.init.php')){
        echo "Error: jelix-scripts.init.php doesn't exist in your application\n";
        echo "       You must create this file which should be similar to application.init.php\n";
        echo "       but with a different temp directory.\n";
        echo "       it should also declare a constant JELIX_APP_REAL_TEMP_PATH which should contain\n";
        echo "       the path to the temp directory indicated in application.init.php.\n";
        exit(1);
    }

    include (JELIXS_APPTPL_PATH.'jelix-scripts.init.php');

    if(!class_exists('jCoordinator', false)) // for old application.init.php which doesn't include init.php
        include (JELIXS_INIT_PATH);

    // we always clean the temp directory. But first, let's check some values (see ticket #840)...
    if (!defined('JELIX_APP_TEMP_PATH')) {
        echo "Error: JELIX_APP_TEMP_PATH is not defined in the jelix-scripts.init.php\n";
        exit(1);
    }

    if (JELIX_APP_TEMP_PATH == DIRECTORY_SEPARATOR || JELIX_APP_TEMP_PATH == '' || JELIX_APP_TEMP_PATH == '/') {
        echo "Error: bad path in JELIX_APP_TEMP_PATH, it is equals to '".JELIX_APP_TEMP_PATH."' !!\n";
        echo "       Jelix cannot clear the content of the temp directory.\n";
        echo "       Correct the path in JELIX_APP_TEMP_PATH or create the directory you\n";
        echo "       indicated into JELIX_APP_TEMP_PATH.\n";
        exit(1);
    }

    jFile::removeDir(JELIX_APP_TEMP_PATH, false);

}
else {
    if ($command->applicationMustExist) {
        echo("Error: the given application doesn't exist (".JELIXS_APPTPL_PATH." )\n");
        exit(1);
    }
    include (JELIXS_INIT_PATH);
    define ('JELIX_APP_PATH',         JELIXS_APPTPL_PATH );
    define ('JELIX_APP_REAL_TEMP_PATH',    JELIXS_APPTPL_TEMP_PATH);
    define ('JELIX_APP_CLI_TEMP_PATH',    substr(JELIXS_APPTPL_TEMP_PATH,0,-1).'-cli/');
    define ('JELIX_APP_TEMP_PATH',    substr(JELIXS_APPTPL_TEMP_PATH,0,-1).'-jelix-scripts/');
    define ('JELIX_APP_VAR_PATH',     JELIXS_APPTPL_VAR_PATH);
    define ('JELIX_APP_LOG_PATH',     JELIXS_APPTPL_LOG_PATH);
    define ('JELIX_APP_CONFIG_PATH',  JELIXS_APPTPL_CONFIG_PATH);
    define ('JELIX_APP_WWW_PATH',     JELIXS_APPTPL_WWW_PATH);
    define ('JELIX_APP_CMD_PATH',     JELIXS_APPTPL_CMD_PATH);
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
    echo "Error: ".$e->getMessage(),"\n";
    exit(1);
}

exit(0);
