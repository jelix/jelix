<?php
/**
* @package       jelix
* @subpackage    testapp
* @author        Sylvain de Vathaire
* @contributor   Laurent Jouanneau
* @copyright     2008 Sylvain de Vathaire
* @copyright     2008-2012 Laurent Jouanneau
* @link          http://www.jelix.org
* @licence       http://www.gnu.org/licenses/gpl.html GNU General Public Licence, see LICENCE file
*/
require ('../application.init.php');

checkAppOpened();

ini_set("soap.wsdl_cache_enabled", "0"); // disabling PHP's WSDL cache

jApp::loadConfig('soap/config.ini.php');

$jelix = new jSoapCoordinator();
jApp::setCoord($jelix);
$jelix->request = new jSoapRequest();
$jelix->request->initService();
$jelix->processSoap();
