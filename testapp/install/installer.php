<?php
/**
* @package  jelix
* @subpackage testapp
* @author   Laurent Jouanneau
* @contributor
* @copyright 2009-2010 Laurent Jouanneau
* @link     http://jelix.org
* @licence  http://www.gnu.org/licenses/gpl.html GNU General Public Licence, see LICENCE file
*/

require_once (dirname(__FILE__).'/../application-cli.init.php');
jApp::setEnv('install');

jAppManager::close();

$installer = new jInstaller(new textInstallReporter());

$installer->installApplication();

jAppManager::clearTemp();
jAppManager::open();
