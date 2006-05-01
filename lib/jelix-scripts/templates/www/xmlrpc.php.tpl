<?php
/**
* @package  {$appname}
* @subpackage www
* @version  $Id$
* @author
* @contributor
* @copyright
*/

require_once ('{$rp_jelix}init.php');

require_once ('{$rp_app}application.init.php');

$config_file = 'config.xmlrpc.ini.php';

require_once (JELIX_LIB_CORE_PATH.'request/jXmlRpcRequest.class.php');

$jelix = new JCoordinator($config_file);
$jelix->process(new jXmlRpcRequest());

?>