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
require (JELIX_LIB_CORE_PATH.'request/jJsonRpcRequest.class.php');

$config_file = 'jsonrpc/config.ini.php';
$jelix = new jCoordinator($config_file);
$jelix->process(new jJsonRpcRequest());

?>
