<?php
/**
* @author   Laurent Jouanneau
* @copyright 2015 Laurent Jouanneau
* @link     http://jelix.org
* @licence  http://www.gnu.org/licenses/gpl.html GNU General Public Licence, see LICENCE file
*/
require_once (__DIR__.'/../application.init.php');

jApp::setEnv('installadmin');

$installer = new \Jelix\Installer\Installer(new \Jelix\Installer\Reporter\SimpleConsole());

$installer->installApplication();

try {
    jAppManager::clearTemp();    
}
catch(Exception $e) {
    echo "WARNING: temporary files cannot be deleted because of this error: ".$e->getMessage().".\nWARNING: Delete temp files by hand immediately!\n";
}
