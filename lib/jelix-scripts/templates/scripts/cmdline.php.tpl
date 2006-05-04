<?php
/**
* @package  {$appname}
* @subpackage scripts
* @version  $Id$
* @author
* @contributor
* @copyright
*/

require_once ('{$rp_jelix}/init.php');

require_once ('{$rp_app}/application.init.php');

require_once (JELIX_LIB_CORE_PATH.'request/jCmdLineRequest.class.php');

$config_file = 'config.cmdline.ini.php';

$jelix = new jCoordinator($config_file);
$jelix->process(new jCmdLineRequest());

?>
