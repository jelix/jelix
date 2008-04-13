<?php
/**
* @package       jelix
* @subpackage    testapp
* @author        Sylvain de Vathaire
* @contributor   Jouanneau Laurent
* @copyright     2008 Sylvain de Vathaire
* @copyright     2008 Jouanneau laurent
* @link          http://www.jelix.org
* @licence       http://www.gnu.org/licenses/gpl.html GNU General Public Licence, see LICENCE file
*/
require_once ('../../lib/jelix/init.php');
require_once ('../../testapp/application.init.php');

require_once (JELIX_LIB_CORE_PATH.'jSoapCoordinator.class.php');

require_once (JELIX_LIB_CORE_PATH.'request/jSoapRequest.class.php');

ini_set("soap.wsdl_cache_enabled", "0"); // disabling PHP's WSDL cache


$config_file = 'soap/config.ini.php';
$jelix = new JSoapCoordinator($config_file);
$jelix->request = new JSoapRequest();
$jelix->request->initService();
$jelix->processSoap();
