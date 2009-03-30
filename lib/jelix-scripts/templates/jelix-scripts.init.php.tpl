<?php
/**
* @package   %%appname%%
* @subpackage 
* @author    %%default_creator_name%%
* @copyright %%default_copyright%%
* @link      %%default_website%%
* @license   %%default_license_url%% %%default_license%%
*/

require (dirname(__FILE__).'/%%rp_jelix%%init.php');

define ('JELIX_APP_PATH', dirname (__FILE__).DIRECTORY_SEPARATOR); // don't change

define ('JELIX_APP_VAR_PATH',     realpath(JELIX_APP_PATH.'%%rp_var%%').DIRECTORY_SEPARATOR);
define ('JELIX_APP_LOG_PATH',     realpath(JELIX_APP_PATH.'%%rp_log%%').DIRECTORY_SEPARATOR);
define ('JELIX_APP_CONFIG_PATH',  realpath(JELIX_APP_PATH.'%%rp_conf%%').DIRECTORY_SEPARATOR);
define ('JELIX_APP_WWW_PATH',     realpath(JELIX_APP_PATH.'%%rp_www%%').DIRECTORY_SEPARATOR);
define ('JELIX_APP_CMD_PATH',     realpath(JELIX_APP_PATH.'%%rp_cmd%%').DIRECTORY_SEPARATOR);

// the temp path for jelix-scripts
define ('JELIX_APP_TEMP_PATH',    realpath(JELIX_APP_PATH.'%%rp_temp%%').DIRECTORY_SEPARATOR);

// the temp path for cli scripts of the application
define ('JELIX_APP_TEMP_CLI_PATH',    realpath(JELIX_APP_PATH.'%%rp_temp_cli%%').DIRECTORY_SEPARATOR);

// the temp path for the web scripts of the application (the same value as JELIX_APP_TEMP_PATH in application.init.php)
define ('JELIX_APP_REAL_TEMP_PATH',    realpath(JELIX_APP_PATH.'%%rp_temp_app%%').DIRECTORY_SEPARATOR);
