<?php
/**
* @package     jelix-scripts
* @version     $Id$
* @author      Jouanneau Laurent
* @contributor
* @copyright   2005-2006 Jouanneau laurent
* @link        http://www.jelix.org
* @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
*/
error_reporting(E_ALL);

/**
 * récupération du nom de la commande et éventuellement du nom de l'application
 */

if($_SERVER['argc'] < 2){
   die("Error: command is missing\n");
}

$argv = $_SERVER['argv'];
array_shift($argv); // shift the script name
$commandName = array_shift($argv); // get the command name

if(preg_match('/^\-\-(\w+)$/',$commandName,$m)){
    $APPNAME=$m[1];
    if($_SERVER['argc'] < 3){
       die("Error: command is missing\n");
    }
    $commandName = array_shift($argv);
}else{
    if(!isset($_ENV['JELIX_APP_NAME'])||$_ENV['JELIX_APP_NAME'] == ''){
        die("Error: JELIX_APP_NAME environnement variable doesn't exists \n");
    }else{
        $APPNAME = $_ENV['JELIX_APP_NAME'];
    }
}

/**
 * recupération de la config
 */

if(!isset($_ENV['JELIX_CONFIG'])){
   //die("Error: JELIX_CONFIG environnement variable is not set \n\t(it should content the absolute path of the config file for the script)\n");
   $jelix_config=dirname(__FILE__).'/scripts.conf.php';

}elseif(!file_exists($_ENV['JELIX_CONFIG'])){
   die("Error: path given by the JELIX_CONFIG environnement variable doesn't exists (".$_ENV['JELIX_CONFIG']." )\n");

}else{
  $jelix_config = $_ENV['JELIX_CONFIG'];
}

require($jelix_config);


if(!file_exists(JELIX_LIB_PATH)){
   die("Error: path given by the JELIX_LIB_PATH constant doesn't exist (".JELIX_LIB_PATH." )\n");
}

if($commandName !='createapp'){
    if(!file_exists(JELIX_APP_PATH)){
        die("Error: path given by the JELIX_APP_PATH constant doesn't exist (".JELIX_APP_PATH." )\n");
    }
    if(!file_exists(JELIX_APP_TEMP_PATH)){
        die("Error: path given by the JELIX_APP_TEMP_PATH constant doesn't exist (".JELIX_APP_TEMP_PATH." )\n");
    }
    if(!file_exists(JELIX_APP_VAR_PATH)){
        die("Error: path given by the JELIX_APP_VAR_PATH constant doesn't exist (".JELIX_APP_VAR_PATH." )\n");
    }
    if(!file_exists(JELIX_APP_CONFIG_PATH)){
        die("Error: path given by the JELIX_APP_CONFIG_PATH constant doesn't exist (".JELIX_APP_CONFIG_PATH." )\n");
    }
}

define ('JELIX_LIB_CORE_PATH',    JELIX_LIB_PATH.'core/');
define ('JELIX_LIB_UTILS_PATH',   JELIX_LIB_PATH.'utils/');
define ('JELIX_LIB_AUTH_PATH',    JELIX_LIB_PATH.'auth/');
define ('JELIX_LIB_DB_PATH',      JELIX_LIB_PATH.'db/');
define ('JELIX_LIB_ACL_PATH',     JELIX_LIB_PATH.'acl/');
define ('JELIX_LIB_DAO_PATH',     JELIX_LIB_PATH.'dao/');
define ('JELIX_LIB_EVENTS_PATH',  JELIX_LIB_PATH.'events/');
define ('JELIX_LIB_REQUEST_PATH', JELIX_LIB_PATH.'core/request/');
define ('JELIX_LIB_RESPONSE_PATH',JELIX_LIB_PATH.'core/response/');

define ('JELIX_SCRIPT_PATH', dirname(__FILE__).'/');


include('includes/command.class.php');
include('includes/utils.lib.php');

/**
 * chargement de la commande
 */

$command = jxs_load_command($commandName);

list($options,$parameters) = jxs_getOptionsAndParams($argv,$command->allowed_options , $command->allowed_parameters);


//--------- launch the command now
$command->init($options,$parameters);

$command->run();

?>