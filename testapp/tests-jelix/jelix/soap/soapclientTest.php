<?php

use Jelix\Core\Profiles;

class soapclientTest extends \Jelix\UnitTests\UnitTestCase
{
    public static function setUpBeforeClass() : void {
        self::initJelixConfig();
    }

    function testCall() {

        try {
            $profile = Profiles::get("jsoapclient");
            if (!isset($profile['wsdl']) || $profile['wsdl'] == '') {
                $this->markTestSkipped('no wsdl specified in profile for jSoapClient. cannot test jSoapClient::get()');
                return;
            }
        }
        catch(Exception $e) {
            $this->markTestSkipped('no profile for jSoapClient. cannot test jSoapClient::get()');
            return;
        }

        $client = jSoapClient::get();

        $result =  $client->hello('Sylvain');
        $this->assertEquals("Hello Sylvain", $result);

        $result =  $client->__soapCall('hello', array('Sylvain'));
        $this->assertEquals("Hello Sylvain", $result);
    }

    /*function testRedirection() {
        try {
            $profile = Profiles::get("jsoapclient");
            if (!isset($profile['wsdl']) || $profile['wsdl'] == '') {
                $this->markTestSkipped('no wsdl specified in profile for jSoapClient. cannot test jSoapClient::get()');
                return;
            }
        }
        catch(Exception $e) {
            $this->markTestSkipped('no profile for jSoapClient. cannot test jSoapClient::get()');
            return;
        }
        $client = jSoapClient::get();

        $result =  $client->redirecttohello('Sylvain');
        $this->markTestIncomplete('not found yet a good implementation for redirection with soap request');
        $this->assertEquals("Hello Sylvain", $result);
    }*/
}