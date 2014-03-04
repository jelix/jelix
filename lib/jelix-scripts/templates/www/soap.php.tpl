<?php
/**
* @package   %%appname%%
* @subpackage 
* @author    %%default_creator_name%%
* @copyright %%default_copyright%%
* @link      %%default_website%%
* @license   %%default_license_url%% %%default_license%%
*/

require_once ('%%rp_app%%application.init.php');

checkAppOpened();

\Jelix\Core\App::loadConfig('%%config_file%%');
ini_set("soap.wsdl_cache_enabled", "0"); // disabling PHP's WSDL cache

jClasses::inc('jsoap~jSoapCoordinator');
jClasses::inc('jsoap~jSoapRequest');

$jelix = new jSoapCoordinator();
\Jelix\Core\App::setCoord($jelix);
$jelix->request = new jSoapRequest();
$jelix->request->initService();
$jelix->processSoap();
