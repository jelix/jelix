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

$basepath = realpath(dirname(__FILE__).'/../../');

define ('JELIX_APP_PATH'        , $basepath."/$APPNAME/");
define ('JELIX_APP_TEMP_PATH'   , $basepath."/temp/$APPNAME/");
define ('JELIX_APP_WWW_PATH'    , $basepath."/$APPNAME/www/");
define ('JELIX_APP_VAR_PATH'    , $basepath."/$APPNAME/var/");
define ('JELIX_APP_LOG_PATH'    , $basepath."/$APPNAME/log/");
define ('JELIX_APP_CONFIG_PATH' , $basepath."/$APPNAME/var/config/");

/* example for linux installation

define ('JELIX_APPS_PATH',         "/usr/local/lib/jelixapp/$APPNAME/");
define ('JELIX_APPS_TEMP_PATH',    "/var/www/jelixapp/temp/$APPNAME/");
define ('JELIX_APPS_WWW_PATH',     "/var/www/jelixapp/www/$APPNAME/");
define ('JELIX_APPS_VAR_PATH',     "/var/www/jelixapp/var/$APPNAME/");
define ('JELIX_APPS_LOG_PATH',     JELIX_APPS_VAR_PATH.'log/');
define ('JELIX_APPS_CONFIG_PATH',  JELIX_APPS_VAR_PATH.'config/');
*/


define ('LIB_PATH',               realpath(dirname (__FILE__).'/../').'/');
define ('JELIX_PLUGINS_PATH',     LIB_PATH.'jelix-plugins/');
define ('JELIX_MODULE_PATH',      LIB_PATH.'jelix-modules/');
define ('JELIX_LIB_PATH',         LIB_PATH.'jelix/');

define('DO_CHMOD',false); // indique si lors de la création des fichiers, il faut faire un chmod
define('CHMOD_FILE_VALUE',0644);
define('CHMOD_DIR_VALUE',0755);
define('DO_CHOWN',false); // indique si lors de la création des fichiers, il faut faire un chown
define('CHOWN_USER','');   // indique le user qui deviendra le propriétaire d'un fichier crée par le script
define('CHOWN_GROUP','');   // indique le groupe qui deviendra le propriétaire d'un fichier crée par le script


?>