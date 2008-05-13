<?php
/**
* @package  %%appname%%
* @subpackage scripts
* @author
* @contributor
* @copyright
*/

require_once ('%%rp_jelix%%init.php');

require_once ('%%rp_app%%application.init.php');

require_once (JELIX_LIB_CORE_PATH.'jCmdlineCoordinator.class.php');

require_once (JELIX_LIB_CORE_PATH.'request/jCmdLineRequest.class.php');

$config_file = 'cmdline/config.ini.php';

$jelix = new jCmdlineCoordinator($config_file);
$jelix->process(new jCmdLineRequest());

