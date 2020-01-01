<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @version     1
* @author      Sylvain de Vathaire
* @contributor Laurent Jouanneau
* @copyright   2008 Sylvain de Vathaire, 2008 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

/**
* Tests of soapCtrl web services 
*/
class soapcontrollerTest extends \Jelix\UnitTests\UnitTestCase {

    public function setUp() : void {
        self::initClassicRequest(TESTAPP_URL.'index.php');
        jApp::pushCurrentModule('jelix_tests');
        parent::setUp();
    }
    function tearDown() : void {
        jApp::popCurrentModule();
    }

    /**
     * Test with the soap extension
     */
    function testSoap() {

        ini_set('soap.wsdl_cache_enabled', 0);

        // Load the WSDL
        $serverUri = jUrl::getRootUrlRessourceValue('soap');
        if ($serverUri === null) {
            $serverUri = "http://".$_SERVER['HTTP_HOST'];
        }
        $wsdlURI = $serverUri.jUrl::get('jsoap~WSDL:wsdl', array('service'=>'testapp~soap'));
        $client = new SoapClient($wsdlURI, array('trace' => 1, 'soap_version'  => SOAP_1_1));

        $result = $client->__soapCall('getServerDate', array());
        $this->assertEquals(date('Y-m-d\TH:i:s O'),$result);

        $result =  $client->__soapCall('hello', array('Sylvain'));
        $this->assertEquals("Hello Sylvain", $result);

        $result =  $client->__soapCall('concatString', array('Hi ! ', 'Sylvain', 'How are you ?'));
        $this->assertEquals('Hi ! SylvainHow are you ?', $result);

        $result =  $client->__soapCall('concatArray', array(array('Hi ! ', 'Sylvain', 'How are you ?')));
        $this->assertEquals('Hi !  Sylvain How are you ?', $result);

        $result =  $client->__soapCall('returnAssociativeArray', array());
        $this->assertEquals(array (
            'arg1' => 'Hi ! ',
            'arg2' => 'Sylvain',
            'arg3' => 'How are you ?',
            ), $result);


        $result =  $client->__soapCall('returnAssociativeArrayOfObjects', array());
        $struct='<array>
    <object key="arg1">
        <string property="name" value="De Vathaire"/>
        <string property="firstName" value="Sylvain"/>
        <string property="city" value="Paris"/>
    </object>
    <object key="arg2">
        <string property="name" value="De Vathaire"/>
        <string property="firstName" value="Sylvain"/>
        <string property="city" value="Paris"/>
    </object>
    <object key="arg3">
        <string property="name" value="De Vathaire"/>
        <string property="firstName" value="Sylvain"/>
        <string property="city" value="Paris"/>
    </object>
</array>';
        $this->assertComplexIdenticalStr($result, $struct);

        //$this->assertEquals(array('arg1'=>'Hi ! ', 'arg2'=>'Sylvain', 'arg3'=>'How are you ?'), $result);

        $result =  $client->__soapCall('concatAssociativeArray', array(array('arg1'=>'Hi ! ', 'arg2'=>'Sylvain', 'arg3'=>'How are you ?')));
        $this->assertEquals('Hi !  Sylvain How are you ?', $result);

        $result =  $client->__soapCall('returnObject', array());
        $struct='<object>
            <string property="name" value="De Vathaire"/>
            <string property="firstName" value="Sylvain"/>
            <string property="city" value="Paris"/>
        </object>';
        $this->assertComplexIdenticalStr($result, $struct);

        $result =  $client->__soapCall('receiveObject', array($result));
        $struct='<object>
            <string property="name" value="Name updated"/>
            <string property="firstName" value="Sylvain"/>
            <string property="city" value="Paris"/>
        </object>';
        $this->assertComplexIdenticalStr($result, $struct);

        $result =  $client->__soapCall('returnObjects', array());
        $struct='<array>
    <object key="0">
        <string property="name" value="De Vathaire"/>
        <string property="firstName" value="Sylvain"/>
        <string property="city" value="Paris"/>
    </object>
    <object key="1">
        <string property="name" value="De Vathaire"/>
        <string property="firstName" value="Sylvain"/>
        <string property="city" value="Paris"/>
    </object>
    <object key="2">
        <string property="name" value="De Vathaire"/>
        <string property="firstName" value="Sylvain"/>
        <string property="city" value="Paris"/>
    </object>
    <object key="3">
        <string property="name" value="De Vathaire"/>
        <string property="firstName" value="Sylvain"/>
        <string property="city" value="Paris"/>
    </object>
</array>';
        $this->assertComplexIdenticalStr($result, $struct);

        $result =  $client->__soapCall('returnObjectBis', array());
        $struct='
        <object>
            <string property="msg" value="hello" />

            <object property="test">
                <string property="name" value="De Vathaire"/>
                <string property="firstName" value="Sylvain"/>
                <string property="city" value="Paris"/>
            </object>
        </object>';
        $this->assertComplexIdenticalStr($result, $struct);

        $result =  $client->__soapCall('returnCircularReference', array());
        $struct='
        <array>
            <object key="0">
                <string property="msg" value="object1" />
                <object property="test">
                    <string property="msg" value="object2" />
                    <object property="test">
                        <string property="msg" value="object1" />
                    </object>
                </object>
            </object>
            <object key="1">
                <string property="msg" value="object2" />
                <object property="test">
                    <string property="msg" value="object1" />
                    <object property="test">
                        <string property="msg" value="object2" />
                    </object>
                </object>
            </object>
        </array>';
        $this->assertComplexIdenticalStr($result, $struct);
    }
}
