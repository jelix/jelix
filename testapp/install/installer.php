<?php
/**
* @package  jelix
* @subpackage testapp
* @author   Laurent Jouanneau
* @contributor Rahal Aboulfeth
* @copyright 2009-2010 Laurent Jouanneau, 2011 Rahal Aboulfeth
* @link     http://jelix.org
* @licence  http://www.gnu.org/licenses/gpl.html GNU General Public Licence, see LICENCE file
*/

require_once (__DIR__.'/../application.init.php');
jApp::setEnv('install');

jAppManager::close();

$installer = new jInstaller(new textInstallReporter());

$installer->installApplication();
try {
    jAppManager::clearTemp();    
}
catch(Exception $e) {
    echo "WARNING: temporary files cannot be deleted because of this error: ".$e->getMessage().".\nWARNING: Delete temp files by hand immediately!\n";
}
jAppManager::open();
