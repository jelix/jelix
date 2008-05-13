<?php
/**
* @package  %%appname%%
* @subpackage
* @author
* @contributor
* @copyright
* @link
* @licence  http://www.gnu.org/licenses/gpl.html GNU General Public Licence, see LICENCE file
*/

define ('JELIX_APP_PATH', dirname (__FILE__).DIRECTORY_SEPARATOR); // don't change

define ('JELIX_APP_TEMP_PATH',    realpath(JELIX_APP_PATH.'%%rp_temp%%').DIRECTORY_SEPARATOR);
define ('JELIX_APP_VAR_PATH',     realpath(JELIX_APP_PATH.'%%rp_var%%').DIRECTORY_SEPARATOR);
define ('JELIX_APP_LOG_PATH',     realpath(JELIX_APP_PATH.'%%rp_log%%').DIRECTORY_SEPARATOR);
define ('JELIX_APP_CONFIG_PATH',  realpath(JELIX_APP_PATH.'%%rp_conf%%').DIRECTORY_SEPARATOR);
define ('JELIX_APP_WWW_PATH',     realpath(JELIX_APP_PATH.'%%rp_www%%').DIRECTORY_SEPARATOR);
define ('JELIX_APP_CMD_PATH',     realpath(JELIX_APP_PATH.'%%rp_cmd%%').DIRECTORY_SEPARATOR);

