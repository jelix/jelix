<?php
/**
* @package  testapp
* @subpackage scripts
* @author       Laurent Jouanneau
* @contributor
* @copyright
*/

require_once ('../application-cli.init.php');

require_once (JELIX_LIB_CORE_PATH.'jCmdlineCoordinator.class.php');

require_once (JELIX_LIB_CORE_PATH.'request/jCmdLineRequest.class.php');

$config_file = 'cmdline/config.ini.php';

$jelix = new jCmdlineCoordinator($config_file);
$jelix->process(new jCmdLineRequest());

?>
