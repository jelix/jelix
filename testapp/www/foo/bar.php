<?php
/**
* @package  jelix
* @subpackage testapp
* @author   Laurent Jouanneau
* @contributor
* @copyright 2006-2008 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence  http://www.gnu.org/licenses/gpl.html GNU General Public Licence, see LICENCE file
*/

require ('../../../lib/jelix-legacy/init.php');
require ('../../../testapp/application.init.php');
require (JELIX_LIB_CORE_PATH.'request/jClassicRequest.class.php');

jApp::loadConfig('index/config.ini.php');

$jelix = new jCoordinator();
$jelix->process(new jClassicRequest());

?>