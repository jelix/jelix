<?php
/**
* @author   Laurent Jouanneau
* @copyright 2015 Laurent Jouanneau
* @link     http://jelix.org
* @licence  http://www.gnu.org/licenses/gpl.html GNU General Public Licence, see LICENCE file
*/
require_once (__DIR__.'/../application.init.php');

jApp::setEnv('installadmin');

// launch the low-level migration
$migrator = new \Jelix\Installer\Migration(new \Jelix\Installer\Reporter\SimpleConsole('notice', 'Low-level migration'));
$migrator->migrate();

$installer = new \Jelix\Installer\Installer(new \Jelix\Installer\Reporter\SimpleConsole());

$installer->installApplication();

try {
    jAppManager::clearTemp();    
}
catch(Exception $e) {
    echo "WARNING: temporary files cannot be deleted because of this error: ".$e->getMessage().".\nWARNING: Delete temp files by hand immediately!\n";
}
