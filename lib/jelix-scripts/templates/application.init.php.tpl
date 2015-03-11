<?php
/**
* @package   %%appname%%
* @subpackage
* @author    %%default_creator_name%%
* @copyright %%default_copyright%%
* @link      %%default_website%%
* @license   %%default_license_url%% %%default_license%%
*/


require (__DIR__.'/vendor/autoload.php');

\Jelix\Core\App::initPaths(
    __DIR__.'/',
    %%php_rp_www%%,
    %%php_rp_var%%,
    %%php_rp_log%%,
    %%php_rp_conf%%,
    %%php_rp_cmd%%
);
\Jelix\Core\App::setTempBasePath(%%php_rp_temp%%);
