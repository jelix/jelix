<?php
/**
* @package   %%appname%%
* @subpackage 
* @author    %%default_creator_name%%
* @copyright %%default_copyright%%
* @link      %%default_website%%
* @licence   %%default_licence_url%% %%default_licence%%
*/

require ('%%rp_app%%application.init.php');
require (JELIX_LIB_CORE_PATH.'request/jJsonRpcRequest.class.php');

$config_file = '%%config_file%%';
$jelix = new jCoordinator($config_file);
$jelix->process(new jJsonRpcRequest());


