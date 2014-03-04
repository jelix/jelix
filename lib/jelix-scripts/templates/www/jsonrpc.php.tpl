<?php
/**
* @package   %%appname%%
* @subpackage 
* @author    %%default_creator_name%%
* @copyright %%default_copyright%%
* @link      %%default_website%%
* @license   %%default_license_url%% %%default_license%%
*/

require ('%%rp_app%%application.init.php');
require (JELIX_LIB_CORE_PATH.'request/jJsonRpcRequest.class.php');

checkAppOpened();

\Jelix\Core\App::loadConfig('%%config_file%%');

\Jelix\Core\App::setCoord(new jCoordinator());
\Jelix\Core\App::coord()->process(new jJsonRpcRequest());




