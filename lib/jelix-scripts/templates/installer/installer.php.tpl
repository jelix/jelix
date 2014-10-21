<?php
/**
* @package   %%appname%%
* @author    %%default_creator_name%%
* @copyright %%default_copyright%%
* @link      %%default_website%%
* @license   %%default_license_url%% %%default_license%%
*/

require_once (__DIR__.'/../application.init.php');

Jelix\Core\App::setEnv('install');

$installer = new \Jelix\Installer\Installer(new \Jelix\Installer\Reporter\Console());

$installer->installApplication();

try {
    \Jelix\Core\AppManager::clearTemp();
}
catch(Exception $e) {
    echo "WARNING: temporary files cannot be deleted because of this error: ".$e->getMessage().".\nWARNING: Delete temp files by hand immediately!\n";
}
