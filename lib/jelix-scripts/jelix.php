<?php
/**
* @package     jelix-scripts
* @version     $Id$
* @author      Jouanneau Laurent
* @contributor Loic Mathaud
* @copyright   2005-2006 Jouanneau laurent
* @link        http://www.jelix.org
* @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
*/
error_reporting(E_ALL);

/**
 * récupération du nom de la commande et éventuellement du nom de l'application
 */

if($_SERVER['argc'] < 2){
   die("Error: command is missing. See '".$_SERVER['argv'][0]." help'.\n");
}

$argv = $_SERVER['argv'];
array_shift($argv); // shift the script name
$commandName = array_shift($argv); // get the command name

if(preg_match('/^\-\-([\w\-\.]+)$/',$commandName,$m)){
    $APPNAME=$m[1];
    if($_SERVER['argc'] < 3){
       die("Error: command is missing. See '".$_SERVER['argv'][0]." help'.\n");
    }
    $commandName = array_shift($argv);
}else{

    if(!isset($_SERVER['JELIX_APP_NAME'])||$_SERVER['JELIX_APP_NAME'] == ''){
        if($commandName != 'help'){
            die("Error: JELIX_APP_NAME environnement variable doesn't exists \n");
        }else{
            $APPNAME='';
        }
    }else{
        $APPNAME = $_SERVER['JELIX_APP_NAME'];
    }

}

function GetAppsRepository($relatedPath) {
    $path = realpath(dirname(__FILE__).'/'.$relatedPath);
    $last = substr($path, -1,1);
    if($last == '\\' || $last == '/')
        $path = substr($path, 0,-1);
    return $path;
}

/**
 * recupération de la config
 */

if(!isset($_SERVER['JELIX_CONFIG'])){

   $jelix_config=dirname(__FILE__).'/scripts.conf.php';

}elseif(!file_exists($_SERVER['JELIX_CONFIG'])){

   die("Error: path given by the JELIX_CONFIG environnement variable doesn't exists (".$_SERVER['JELIX_CONFIG']." )\n");

}else{
  $jelix_config = $_SERVER['JELIX_CONFIG'];
}

require($jelix_config);

// include all jelix libraries and constants : needed by some commands.
include (JELIXS_INIT_PATH);

define ('JELIX_SCRIPT_PATH', dirname(__FILE__).'/');



if(file_exists(JELIXS_APPTPL_PATH.'application.init.php')){
   include (JELIXS_APPTPL_PATH.'application.init.php');
}else{
   if($commandName !='createapp' && $commandName !='help'){
     die("Error: the given application doesn't exists (".JELIXS_APPTPL_PATH." )\n");
   }
   define ('JELIX_APP_PATH',         JELIXS_APPTPL_PATH );
   define ('JELIX_APP_TEMP_PATH',    JELIXS_APPTPL_TEMP_PATH);
   define ('JELIX_APP_VAR_PATH',     JELIXS_APPTPL_VAR_PATH);
   define ('JELIX_APP_LOG_PATH',     JELIXS_APPTPL_LOG_PATH);
   define ('JELIX_APP_CONFIG_PATH',  JELIXS_APPTPL_CONFIG_PATH);
   define ('JELIX_APP_WWW_PATH',     JELIXS_APPTPL_WWW_PATH);
   define ('JELIX_APP_CMD_PATH',     JELIXS_APPTPL_CMD_PATH);
}

include('includes/command.class.php');
include('includes/utils.lib.php');

/**
 * chargement de la commande
 */

$command = jxs_load_command($commandName);

list($options,$parameters) = jxs_getOptionsAndParams($argv,$command->allowed_options , $command->allowed_parameters);


//--------- launch the command now
$command->init($options,$parameters);

try {
    $command->run();
}catch(Exception $e) {
    echo "Error: ".$e->getMessage(),"\n";
    exit(1);
}

exit(0);

