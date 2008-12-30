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

require_once (JELIX_LIB_CORE_PATH.'jCmdlineCoordinator.class.php');

require_once (JELIX_LIB_CORE_PATH.'request/jCmdLineRequest.class.php');

$config_file = 'cmdline/config.ini.php';

$jelix = new jCmdlineCoordinator($config_file);
$jelix->process(new jCmdLineRequest());

