<?php
/**
* @package   %%appname%%
* @subpackage 
* @author    %%default_creator_name%%
* @copyright %%default_copyright%%
* @link      %%default_website%%
* @license   %%default_license_url%% %%default_license%%
*/

define ('JELIX_APP_PATH', dirname (__FILE__).DIRECTORY_SEPARATOR); // don't change

require (JELIX_APP_PATH.'/%%rp_jelix%%init.php');

define ('JELIX_APP_VAR_PATH',     %%php_rp_var%%);
define ('JELIX_APP_LOG_PATH',     %%php_rp_log%%);
define ('JELIX_APP_CONFIG_PATH',  %%php_rp_conf%%);
define ('JELIX_APP_WWW_PATH',     %%php_rp_www%%);
define ('JELIX_APP_CMD_PATH',     %%php_rp_cmd%%);

// the temp path for jelix-scripts
define ('JELIX_APP_TEMP_PATH',    %%php_rp_temp%%);

// the temp path for cli scripts of the application
define ('JELIX_APP_TEMP_CLI_PATH',    %%php_rp_temp_cli%%);

// the temp path for the web scripts of the application (the same value as JELIX_APP_TEMP_PATH in application.init.php)
define ('JELIX_APP_REAL_TEMP_PATH',    %%php_rp_temp_app%%);
