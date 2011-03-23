<?php

class testJSoapClient extends jSoapClient {

    static function testSetProfiles($profiles) {
        self::$_profiles = $profiles;
    }

    static function testGetProfiles() {
        return self::$_profiles;
    }
}


class soapclientTest extends PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass() {
        jelix_init_test_env();
    }

    function testDefaultProfile () {
        $defaultProfile = array('wsdl'=>'books.wsdl', 'option'=>'foo');
        $readedDefaultProfile = array('name'=>'default', 'wsdl'=>'books.wsdl', 'option'=>'foo');

        testJSoapClient::testSetProfiles(array(
            'default'=>  $defaultProfile
            )
        );

        $profile = testJSoapClient::getProfile();
        $this->assertEquals($readedDefaultProfile, $profile);

        $profile = testJSoapClient::getProfile('default');
        $this->assertEquals($readedDefaultProfile, $profile);

        $profile = testJSoapClient::getProfile('toto');
        $this->assertEquals($readedDefaultProfile, $profile);

        try {
            $profile = testJSoapClient::getProfile('toto', true);
            $this->fail();
        } catch(jException $e) {
            $this->assertEquals(553, $e->getCode(), "Exception: ".$e->getMessage());
        } catch (Exception $e) {
            $this->fail('bad expected exception');
        }
    }

    function testAliasDefaultProfile () {
        $defaultProfile = array('wsdl'=>'books.wsdl', 'option'=>'foo');
        $readedDefaultProfile = array('name'=>'server1', 'wsdl'=>'books.wsdl', 'option'=>'foo');

        $allProfiles = array(
            'default'=>'server1',
            'server1'=>  $defaultProfile
        );

        testJSoapClient::testSetProfiles($allProfiles);

        $profile = testJSoapClient::getProfile();
        $this->assertEquals($readedDefaultProfile, $profile);

        $profile = testJSoapClient::getProfile('default');
        $this->assertEquals($readedDefaultProfile, $profile);

        $profile = testJSoapClient::getProfile('server1');
        $this->assertEquals($readedDefaultProfile, $profile);

        $profile = testJSoapClient::getProfile('toto');
        $this->assertEquals($readedDefaultProfile, $profile);

        $this->assertEquals( array(
            'default'=>'server1',
            'server1'=>  $readedDefaultProfile
            ), testJSoapClient::testGetProfiles());

        try {
            $profile = testJSoapClient::getProfile('toto', true);
            $this->fail();
        } catch(jException $e) {
            $this->assertEquals(553, $e->getCode(), "Exception: ".$e->getMessage());
        } catch (Exception $e) {
            $this->fail('bad expected exception');
        }
    }

    function testAliasProfile () {
        $myProfile = array('wsdl'=>'books.wsdl', 'option'=>'foo');
        $readedProfile = array('name'=>'server1', 'wsdl'=>'books.wsdl', 'option'=>'foo');

        $allProfiles = array(
            'default'=>'server1',
            'myserver'=>'server1',
            'server1'=>  $myProfile
        );

        testJSoapClient::testSetProfiles($allProfiles);

        $profile = testJSoapClient::getProfile();
        $this->assertEquals($readedProfile, $profile);

        $profile = testJSoapClient::getProfile('default');
        $this->assertEquals($readedProfile, $profile);

        $profile = testJSoapClient::getProfile('server1');
        $this->assertEquals($readedProfile, $profile);

        $profile = testJSoapClient::getProfile('myserver');
        $this->assertEquals($readedProfile, $profile);

        $profile = testJSoapClient::getProfile('toto');
        $this->assertEquals($readedProfile, $profile);

        $this->assertEquals( array(
            'default'=>'server1',
            'myserver'=>'server1',
            'server1'=>  $readedProfile
            ), testJSoapClient::testGetProfiles());

        try {
            $profile = testJSoapClient::getProfile('toto', true);
            $this->fail();
        } catch(jException $e) {
            $this->assertEquals(553, $e->getCode(), "Exception: ".$e->getMessage());
        } catch (Exception $e) {
            $this->fail('bad expected exception');
        }
    }

    function testCall() {
        testJSoapClient::testSetProfiles(null);

        if (!file_exists(jApp::configPath('soapprofiles.ini.php'))) {
            $this->markTestSkipped('soapprofiles.ini.php does not exists. cannot test jSoapClient::get()');
            return;
        }

        $client = testJSoapClient::get();

        $result =  $client->hello('Sylvain');
        $this->assertEquals("Hello Sylvain", $result);

        $result =  $client->__soapCall('hello', array('Sylvain'));
        $this->assertEquals("Hello Sylvain", $result);
    }
}