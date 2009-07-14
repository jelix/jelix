<?php
/**
* @package  jelix
* @subpackage testapp
* @author   Laurent Jouanneau
* @contributor
* @copyright 2009 Laurent Jouanneau
* @link     http://jelix.org
* @licence  http://www.gnu.org/licenses/gpl.html GNU General Public Licence, see LICENCE file
*/

require_once ('../application-cli.init.php');

$installer = new jInstaller(new textInstallReporter());

$installer->installModules(array('testapp', 'junittests', 'jWSDL'));

