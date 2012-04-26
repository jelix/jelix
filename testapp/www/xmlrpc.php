<?php
/**
* @package  jelix
* @subpackage testapp
* @author   Laurent Jouanneau
* @contributor
* @copyright 2005-2012 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence  http://www.gnu.org/licenses/gpl.html GNU General Public Licence, see LICENCE file
*/

require ('../application.init.php');
require (JELIX_LIB_CORE_PATH.'request/jXmlRpcRequest.class.php');

checkAppOpened();

jApp::loadConfig('xmlrpc/config.ini.php');

jApp::setCoord(new jCoordinator());
jApp::coord()->process(new jXmlRpcRequest());