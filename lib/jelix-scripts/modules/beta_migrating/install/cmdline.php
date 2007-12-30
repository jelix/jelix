<?php
/**
* @package  {$appname}
* @subpackage scripts
* @author
* @contributor
* @copyright
*/

require_once ('../../lib/jelix/init.php');

require_once ('../application.init.php');

require_once (JELIX_LIB_CORE_PATH.'request/jCmdLineRequest.class.php');

$config_file = 'cmdline/config.ini.php';

$jelix = new jCoordinator($config_file);
$jelix->process(new jCmdLineRequest());

?>