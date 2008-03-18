<?php
/**
* @package     jelix
* @subpackage  testapp
* @author      Jouanneau Laurent
* @contributor
* @copyright   2006-2008 Jouanneau laurent
* @link        http://www.jelix.org
* @licence     http://www.gnu.org/licenses/gpl.html GNU General Public Licence, see LICENCE file
*/

require ('../../lib/jelix/init.php');
require ('../../testapp/application.init.php');
require (JELIX_LIB_CORE_PATH.'request/jClassicRequest.class.php');

$config_file = 'index/config.ini.php';

$jelix = new jCoordinator($config_file);
$jelix->process(new jClassicRequest());

?>