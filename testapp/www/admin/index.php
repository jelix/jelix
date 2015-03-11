<?php
/**
* @package   adminapp
* @subpackage 
* @author    your name
* @copyright 2015 a name
* @link      http://www.yourwebsite.undefined
* @license    All rights reserved
*/

require ('../../adminapp/application.init.php');
require (JELIX_LIB_CORE_PATH.'request/jClassicRequest.class.php');

checkAppOpened();

jApp::loadConfig('index/config.ini.php');

jApp::setCoord(new jCoordinator());
jApp::coord()->process(new jClassicRequest());



