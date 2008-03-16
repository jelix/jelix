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

// related path given to GetAppsRepository should be related to the jelix.php script.
define('JELIXS_APPS_BASEPATH', GetAppsRepository('../../'));

define ('JELIXS_APPTPL_PATH'        , JELIXS_APPS_BASEPATH."/$APPNAME/");
define ('JELIXS_APPTPL_TEMP_PATH'   , JELIXS_APPS_BASEPATH."/temp/$APPNAME/");
define ('JELIXS_APPTPL_WWW_PATH'    , JELIXS_APPS_BASEPATH."/$APPNAME/www/");
define ('JELIXS_APPTPL_VAR_PATH'    , JELIXS_APPS_BASEPATH."/$APPNAME/var/");
define ('JELIXS_APPTPL_LOG_PATH'    , JELIXS_APPS_BASEPATH."/$APPNAME/var/log/");
define ('JELIXS_APPTPL_CONFIG_PATH' , JELIXS_APPS_BASEPATH."/$APPNAME/var/config/");
define ('JELIXS_APPTPL_CMD_PATH'    , JELIXS_APPS_BASEPATH."/$APPNAME/scripts/");
define ('JELIXS_LIB_PATH'          , JELIXS_APPS_BASEPATH.'/lib/');
define ('JELIXS_INIT_PATH'          , JELIXS_LIB_PATH.'jelix/init.php');

/* example for a linux package :

define('JELIXS_APPS_BASEPATH', '/var/www/jelixapp/');

define ('JELIXS_APPTPL_PATH'        , "/usr/local/lib/jelixapp/$APPNAME/");
define ('JELIXS_APPTPL_TEMP_PATH'   , "/var/www/jelixapp/temp/$APPNAME/");
define ('JELIXS_APPTPL_WWW_PATH'    , "/var/www/jelixapp/www/$APPNAME/");
define ('JELIXS_APPTPL_VAR_PATH'    , "/var/www/jelixapp/var/$APPNAME/");
define ('JELIXS_APPTPL_LOG_PATH'    , JELIXS_APPTPL_VAR_PATH."log/");
define ('JELIXS_APPTPL_CONFIG_PATH' , JELIXS_APPTPL_VAR_PATH."config/");
define ('JELIXS_INIT_PATH'          , '/usr/local/lib/jelix/init.php');
*/

define('JELIXS_APP_CONFIG_FILE'    , 'defaultconfig.ini.php');
define('DO_CHMOD',false); // indique si lors de la création des fichiers, il faut faire un chmod
define('CHMOD_FILE_VALUE',0644);
define('CHMOD_DIR_VALUE',0755);
define('DO_CHOWN',false); // indique si lors de la création des fichiers, il faut faire un chown
define('CHOWN_USER','');   // indique le user qui deviendra le propriétaire d'un fichier crée par le script
define('CHOWN_GROUP','');   // indique le groupe qui deviendra le propriétaire d'un fichier crée par le script

define('DISPLAY_HELP_UTF_8', true); // affiche l'aide en utf-8 (si votre console est en utf-8)
define('MESSAGE_LANG','fr');
?>
