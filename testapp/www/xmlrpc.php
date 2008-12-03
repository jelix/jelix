<?php
/**
* @package  jelix
* @subpackage testapp
* @author   Jouanneau Laurent
* @contributor
* @copyright 2005-2008 Jouanneau laurent
* @link        http://www.jelix.org
* @licence  http://www.gnu.org/licenses/gpl.html GNU General Public Licence, see LICENCE file
*/

require ('../application.init.php');
require (JELIX_LIB_CORE_PATH.'request/jXmlRpcRequest.class.php');

$config_file = 'xmlrpc/config.ini.php';

$jelix = new JCoordinator($config_file);
$jelix->process(new jXmlRpcRequest());

?>