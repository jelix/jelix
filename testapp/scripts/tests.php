<?php
/**
* @package  testapp
* @subpackage scripts
* @author       Laurent Jouanneau
* @contributor
* @copyright
*/


require_once ('../application.init.php');

require_once (JELIX_LIB_CORE_PATH.'jCmdlineCoordinator.class.php');

require_once (JELIX_LIB_CORE_PATH.'request/jCmdLineRequest.class.php');

jApp::loadConfig('cmdline/configtests.ini.php');

jApp::setCoord(new jCmdlineCoordinator());
jApp::coord()->process(new jCmdLineRequest());
