<?php
/**
* @package     jelix-scripts
* @author      Laurent Jouanneau
* @contributor Loic Mathaud
* @copyright   2005-2008 Laurent Jouanneau
* @link        http://jelix.org
* @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
*/

if (!defined('JELIXS_APPS_BASEPATH'))
    define('JELIXS_APPS_BASEPATH', GetAppsRepository('../../'));

if (!defined('JELIXS_APPTPL_PATH'))
    define ('JELIXS_APPTPL_PATH'        , JELIXS_APPS_BASEPATH."/$APPNAME/");
if (!defined('JELIXS_APPTPL_TEMP_PATH'))
    define ('JELIXS_APPTPL_TEMP_PATH'   , JELIXS_APPS_BASEPATH."/temp/$APPNAME/");
if (!defined('JELIXS_APPTPL_WWW_PATH'))
    define ('JELIXS_APPTPL_WWW_PATH'    , JELIXS_APPS_BASEPATH."/$APPNAME/www/");
if (!defined('JELIXS_APPTPL_VAR_PATH'))
    define ('JELIXS_APPTPL_VAR_PATH'    , JELIXS_APPS_BASEPATH."/$APPNAME/var/");
if (!defined('JELIXS_APPTPL_LOG_PATH'))
    define ('JELIXS_APPTPL_LOG_PATH'    , JELIXS_APPS_BASEPATH."/$APPNAME/var/log/");
if (!defined('JELIXS_APPTPL_CONFIG_PATH'))
    define ('JELIXS_APPTPL_CONFIG_PATH' , JELIXS_APPS_BASEPATH."/$APPNAME/var/config/");
if (!defined('JELIXS_APPTPL_CMD_PATH'))
    define ('JELIXS_APPTPL_CMD_PATH'    , JELIXS_APPS_BASEPATH."/$APPNAME/scripts/");
if (!defined('JELIXS_LIB_PATH'))
    define ('JELIXS_LIB_PATH'           , JELIXS_APPS_BASEPATH.'/lib/');
if (!defined('JELIXS_INIT_PATH'))
    define ('JELIXS_INIT_PATH'          , JELIXS_LIB_PATH.'jelix/init.php');

if (!defined('JELIXS_INFO_DEFAULT_IDSUFFIX'))
    define('JELIXS_INFO_DEFAULT_IDSUFFIX','@yourwebsite.undefined');
if (!defined('JELIXS_INFO_DEFAULT_WEBSITE'))
    define('JELIXS_INFO_DEFAULT_WEBSITE','http://www.yourwebsite.undefined');
if (!defined('JELIXS_INFO_DEFAULT_LICENSE'))
    define('JELIXS_INFO_DEFAULT_LICENSE','All right reserved');
if (!defined('JELIXS_INFO_DEFAULT_LICENSE_URL'))
    define('JELIXS_INFO_DEFAULT_LICENSE_URL','');
if (!defined('JELIXS_INFO_DEFAULT_CREATOR_NAME'))
    define('JELIXS_INFO_DEFAULT_CREATOR_NAME','yourname');
if (!defined('JELIXS_INFO_DEFAULT_CREATOR_EMAIL'))
    define('JELIXS_INFO_DEFAULT_CREATOR_EMAIL','youremail@yourwebsite.undefined');
if (!defined('JELIXS_INFO_DEFAULT_COPYRIGHT'))
    define('JELIXS_INFO_DEFAULT_COPYRIGHT','2010 yourname');
if (!defined('JELIXS_INFO_DEFAULT_TIMEZONE'))
    define('JELIXS_INFO_DEFAULT_TIMEZONE','Europe/Paris');
if (!defined('JELIXS_INFO_DEFAULT_LOCALE'))
    define('JELIXS_INFO_DEFAULT_LOCALE','en_EN');

if (!defined('DO_CHMOD'))
    define('DO_CHMOD',false);
if (!defined('CHMOD_FILE_VALUE'))
    define('CHMOD_FILE_VALUE',0644);
if (!defined('CHMOD_DIR_VALUE'))
    define('CHMOD_DIR_VALUE',0755);
if (!defined('DO_CHOWN'))
    define('DO_CHOWN',false);
if (!defined('CHOWN_USER'))
    define('CHOWN_USER','');
if (!defined('CHOWN_GROUP'))
    define('CHOWN_GROUP','');

if (!defined('DISPLAY_HELP_UTF_8'))
    define('DISPLAY_HELP_UTF_8', true);
if (!defined('MESSAGE_LANG'))
    define('MESSAGE_LANG','en');

if (!defined('DEBUG_MODE'))
    define('DEBUG_MODE',false);