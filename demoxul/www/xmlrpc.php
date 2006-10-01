<?php
/**
* @package  demo
* @subpackage www
* @version  $Id$
* @author
* @contributor
* @copyright
*/

require_once ('../../lib/jelix//init.php');

require_once ('.././application.init.php');

$config_file = 'xmlrpc/config.ini.php';

require_once (JELIX_LIB_CORE_PATH.'request/jXmlRpcRequest.class.php');

$jelix = new JCoordinator($config_file);
$jelix->process(new jXmlRpcRequest());

?>