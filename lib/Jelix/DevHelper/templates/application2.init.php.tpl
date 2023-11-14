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

\Jelix\Core\App::initPaths(
    __DIR__.'/',
    %%php_rp_www%%,
    %%php_rp_var%%,
    %%php_rp_log%%,
    %%php_rp_conf%%
);
\Jelix\Core\App::setTempBasePath(%%php_rp_temp%%);

require($vendorDir.'jelix_app_path.php');

// Declares here paths of directories containings plugins and modules,
// that are not already declared into composer.json files of Composer packages
\Jelix\Core\App::declareModulesDir(array(
    __DIR__.'/modules/'
));
\Jelix\Core\App::declarePluginsDir(array(
    __DIR__.'/plugins'
));

