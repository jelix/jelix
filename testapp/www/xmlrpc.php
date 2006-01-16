<?php
/**
* @package  jelix
* @subpackage testapp
* @version  $Id$
* @author   Jouanneau Laurent
* @contributor
* @copyright 2005-2006 Jouanneau laurent
* @link        http://www.jelix.org
* @licence  http://www.gnu.org/licenses/gpl.html GNU General Public Licence, see LICENCE file
*/

require_once ('../../lib/jelix/init.php');
//require_once ('/usr/lib/jelix/1.0/jelix/init.php');

require_once ('../../testapp/application.init.php');
//require_once ('/usr/share/jelix/testapp/application.init.php');

$config_file = 'config.xmlrpc.ini.php';

require_once (JELIX_LIB_CORE_PATH.'request/jXmlRpcRequest.class.php');

$jelix = new JCoordinator($config_file);
$jelix->process(new jXmlRpcRequest());

?>