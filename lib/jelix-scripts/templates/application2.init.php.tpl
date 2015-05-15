<?php
/**
* @package   %%appname%%
* @subpackage
* @author    %%default_creator_name%%
* @copyright %%default_copyright%%
* @link      %%default_website%%
* @license   %%default_license_url%% %%default_license%%
*/
$vendorDir = %%php_rp_vendor%%;
require($vendorDir.'autoload.php');
require($vendorDir.'jelix_app_path.php');

jApp::initPaths(
    __DIR__.'/',
    %%php_rp_www%%,
    %%php_rp_var%%,
    %%php_rp_log%%,
    %%php_rp_conf%%,
    %%php_rp_cmd%%
);
jApp::setTempBasePath(%%php_rp_temp%%);

// if you use composer, you can declares these path in the composer.json
// file instead of declaring them here...
jApp::declareModulesDir(array(
                        __DIR__.'/modules/'
                    ));
jApp::declarePluginsDir(array(
                        __DIR__.'/plugins'
                    ));
