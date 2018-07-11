<?php
/**
* @package   %%appname%%
* @author    %%default_creator_name%%
* @copyright %%default_copyright%%
* @link      %%default_website%%
* @license   %%default_license_url%% %%default_license%%
*/

require_once (__DIR__.'/../application.init.php');

\Jelix\Core\App::setEnv('install');

\Jelix\Core\AppManager::close();


// launch the low-level migration
$migrator = new \Jelix\Installer\Migration(new \Jelix\Installer\Reporter\Console('notice', 'Low-level migration'));
$migrator->migrate();

// we can now launch the installer/updater
$installer = new \Jelix\Installer\Installer(new \Jelix\Installer\Reporter\Console());
if (!$installer->installApplication()) {
    exit(1);
}

try {
    \Jelix\Core\AppManager::clearTemp();
}
catch(Exception $e) {
    echo "WARNING: temporary files cannot be deleted because of this error: ".$e->getMessage().".\nWARNING: Delete temp files by hand immediately!\n";
    exit(1);
}

\Jelix\Core\AppManager::open();

exit(0);

