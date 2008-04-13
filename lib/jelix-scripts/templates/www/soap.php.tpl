<?php
/**
* @package       %%$appname%%
* @subpackage    www
* @author
* @contributor
* @copyright
*/

require_once ('%%rp_jelix%%init.php');
require_once ('%%rp_app%%application.init.php');

require_once (JELIX_LIB_CORE_PATH.'jSoapCoordinator.class.php');
require_once (JELIX_LIB_CORE_PATH.'request/jSoapRequest.class.php');

ini_set("soap.wsdl_cache_enabled", "0"); // disabling PHP's WSDL cache

$config_file = 'soap/config.ini.php';
$jelix = new JSoapCoordinator($config_file);
$jelix->request = new JSoapRequest();
$jelix->request->initService();
$jelix->processSoap();
