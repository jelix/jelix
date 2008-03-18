<?php
/**
* @package  %%appname%%
* @subpackage www
* @author
* @contributor
* @copyright
*/

require ('%%rp_jelix%%init.php');
require ('%%rp_app%%application.init.php');
require (JELIX_LIB_CORE_PATH.'request/jXmlRpcRequest.class.php');

$config_file = 'xmlrpc/config.ini.php';
$jelix = new JCoordinator($config_file);
$jelix->process(new jXmlRpcRequest());

?>