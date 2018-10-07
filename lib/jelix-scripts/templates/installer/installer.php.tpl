<?php
/**
* @package   %%appname%%
* @author    %%default_creator_name%%
* @copyright %%default_copyright%%
* @link      %%default_website%%
* @license   %%default_license_url%% %%default_license%%
*/

require_once (__DIR__.'/../application.init.php');
jApp::setEnv('install');

jAppManager::close();

// we launch the installer/updater
$installer = new Jelix\Installer\Installer(new \Jelix\Installer\Reporter\SimpleConsole());
if (!$installer->installApplication()) {
    exit(1);
}

try {
    jAppManager::clearTemp();    
}
catch(Exception $e) {
    echo "WARNING: temporary files cannot be deleted because of this error: ".$e->getMessage().".\nWARNING: Delete temp files by hand immediately!\n";
    exit(1);
}
jAppManager::open();

exit(0);
