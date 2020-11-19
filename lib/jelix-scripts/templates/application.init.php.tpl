<?php
/**
* @package   %%appname%%
* @subpackage
* @author    %%default_creator_name%%
* @copyright %%default_copyright%%
* @link      %%default_website%%
* @license   %%default_license_url%% %%default_license%%
*/
require (%%php_rp_jelix%%.'init.php');

jApp::initPaths(
    __DIR__.'/',
    %%php_rp_www%%,
    %%php_rp_var%%,
    %%php_rp_log%%,
    %%php_rp_conf%%
);
jApp::setTempBasePath(%%php_rp_temp%%);

jApp::declareModulesDir(array(
                        LIB_PATH.'/jelix-modules/',
                        LIB_PATH.'/jelix-admin-modules/',
                        __DIR__.'/modules/'
                    ));
jApp::declarePluginsDir(array(
                        LIB_PATH.'/jelix-plugins/',
                        __DIR__.'/plugins'
                    ));
