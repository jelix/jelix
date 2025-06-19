<?php
/**
* @package   %%appname%%
* @subpackage 
* @author    %%default_creator_name%%
* @copyright %%default_copyright%%
* @link      %%default_website%%
* @license   %%default_license_url%% %%default_license%%
*/
use Jelix\Core\App;
use Jelix\Routing\Router;

require ('%%rp_app%%application.init.php');
require (JELIX_LIB_CORE_PATH.'request/jJsonRpcRequest.class.php');

\Jelix\Core\AppManager::errorIfAppClosed();

App::loadConfig('%%config_file%%');

App::setRouter(new Router());
App::router()->process(new jJsonRpcRequest());




