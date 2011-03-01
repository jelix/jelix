<?php
/**
* @package   %%appname%%
* @subpackage 
* @author    %%default_creator_name%%
* @copyright %%default_copyright%%
* @link      %%default_website%%
* @license   %%default_license_url%% %%default_license%%
*/

$appPath = dirname (__FILE__).'/';
require ($appPath.'%%rp_jelix%%/init.php');

jApp::initPaths(
    $appPath,
    %%php_rp_www%%,
    %%php_rp_var%%,
    %%php_rp_log%%,
    %%php_rp_conf%%,
    %%php_rp_cmd%%
);

// the temp path for jelix-scripts
jApp::setTempBasePath(%%php_rp_temp%%);

// the temp path for cli scripts of the application
define ('JELIX_APP_TEMP_CLI_PATH',    %%php_rp_temp_cli%%);

// the temp path for the web scripts of the application (the same value as JELIX_APP_TEMP_PATH in application.init.php)
define ('JELIX_APP_REAL_TEMP_PATH',    %%php_rp_temp_app%%);
