<?php
/**
* @package     testapp
* @subpackage  testapp module
* @version     1
* @author      Sylvain de Vathaire
* @contributor
* @copyright   2008 Sylvain de Vathaire
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

/**
* Tests of soapCtrl web services 
*/
class clientSoapCtrl extends jController {

    var $module = 'testapp';

    var $controller = 'soap';

    /**  
     * Test with the soap extension
     * @return string Server date
     */
    function soapExtension() {

        ini_set('soap.wsdl_cache_enabled', 0);
        $rep = $this->getResponse('html');
        $rep->title = 'Client utilisant l\'extension soap pour faire appel au serveur soap Jelix';
        $rep->body->assign('page_title','Client utilisant l\'extension soap pour faire appel au serveur soap Jelix');

        $tpl = new jTpl();
        $tpl->assign('liste', array()); 


        // Load the WSDL
        try {
            $serverUri = jUrl::getRootUrlRessourceValue('soap');
            if ($serverUri === null) {
                $serverUri = "http://".$_SERVER['HTTP_HOST'];
            }
            $wsdlURI = $serverUri.jUrl::get('jWSDL~WSDL:wsdl', array('service'=>'testapp~soap'));
            $client = new SoapClient($wsdlURI, array('trace' => 1, 'soap_version'  => SOAP_1_1));
        } catch (SoapFault $fault) {
            throw new Exception($fault->getMessage());
        }

        try {
            $result = $client->__soapCall('getServerDate', array());
            $tpl->assign("getServerDate", $result);

            $result =  $client->__soapCall('hello', array('Sylvain'));
            $tpl->assign("hello", $result);

            $result =  $client->__soapCall('concatString', array('Hi ! ', 'Sylvain', 'How are you ?'));
            $tpl->assign("concatString", $result);

            $result =  $client->__soapCall('concatArray', array(array('Hi ! ', 'Sylvain', 'How are you ?')));
            $tpl->assign("concatArray", $result);

            $result =  $client->__soapCall('returnAssociativeArray', array());
            $tpl->assign("returnAssociativeArray", $result);

            $result =  $client->__soapCall('returnAssociativeArrayOfObjects', array());
            $tpl->assign("returnAssociativeArrayOfObjects", $result);

            $result =  $client->__soapCall('concatAssociativeArray', array(array('arg1'=>'Hi ! ', 'arg2'=>'Sylvain', 'arg3'=>'How are you ?')));
            $tpl->assign("concatAssociativeArray", $result);

            $result =  $client->__soapCall('returnObject', array());
            $tpl->assign("returnObject", $result);

            $result =  $client->__soapCall('receiveObject', array($result));
            $tpl->assign("receiveObject", $result);

            $result =  $client->__soapCall('returnObjects', array());
            $tpl->assign("returnObjects", $result);

            $result =  $client->__soapCall('returnObjectBis', array());
            $tpl->assign("returnObjectBis", $result);

            $result =  $client->__soapCall('returnCircularReference', array());
            $tpl->assign("returnCircularReference", $result);

        } catch (SoapFault $fault) {
            print_r($fault);
            throw new Exception($fault->getMessage());
        }

        $rep->body->assign('MAIN',$tpl->fetch('soap'));
        return $rep;
    }
}
