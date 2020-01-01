<?php

class testjProfilesCompiler extends jProfilesCompiler {
    function __construct($sources) {
        $this->sources = $sources;
    }
}



class testJProfiles extends jProfiles {

    static function testSetProfiles($profiles) {
        $compil = new testjProfilesCompiler($profiles);
        self::$_profiles = $compil->compile();
    }

    static function testGetProfiles() {
        return self::$_profiles;
    }
}


class profilesTest extends \Jelix\UnitTests\UnitTestCase
{
    public static function setUpBeforeClass() : void {
        self::initJelixConfig();
    }

    function testDefaultProfile () {
        $defaultProfile = array('wsdl'=>'books.wsdl', 'option'=>'foo');
        $readedDefaultProfile = array('_name'=>'default', 'wsdl'=>'books.wsdl', 'option'=>'foo');

        testJProfiles::testSetProfiles(array(
            'foo'=>array(),
            'foo:default'=>  $defaultProfile
            )
        );

        $profile = testJProfiles::get('foo');
        $this->assertEquals($readedDefaultProfile, $profile);

        $profile = testJProfiles::get('foo','default');
        $this->assertEquals($readedDefaultProfile, $profile);

        $profile = testJProfiles::get('foo','toto');
        $this->assertEquals($readedDefaultProfile, $profile);

        try {
            $profile = testJProfiles::get('foo','toto', true);
            $this->fail();
        } catch(jException $e) {
            $this->assertEquals(500, $e->getCode(), "Exception: ".$e->getMessage());
        } catch (Exception $e) {
            $this->fail('bad expected exception');
        }
    }

    function testAliasDefaultProfile () {
        $defaultProfile = array('wsdl'=>'books.wsdl', 'option'=>'foo');
        $readedDefaultProfile = array('wsdl'=>'books.wsdl', 'option'=>'foo','_name'=>'server1');

        $allProfiles = array(
            'foo'=>array('default'=>'server1'),
            'foo:server1'=>  $defaultProfile
        );

        testJProfiles::testSetProfiles($allProfiles);

        $profile = testJProfiles::get('foo');
        $this->assertEquals($readedDefaultProfile, $profile);

        $profile = testJProfiles::get('foo','default');
        $this->assertEquals($readedDefaultProfile, $profile);

        $profile = testJProfiles::get('foo','server1');
        $this->assertEquals($readedDefaultProfile, $profile);

        $profile = testJProfiles::get('foo','toto');
        $this->assertEquals($readedDefaultProfile, $profile);

        $this->assertEquals( array(
            'foo:server1'=> $readedDefaultProfile,
            'foo:default'=> $readedDefaultProfile
            ), testJProfiles::testGetProfiles());

        try {
            $profile = testJProfiles::get('foo','toto', true);
            $this->fail();
        } catch(jException $e) {
            $this->assertEquals(500, $e->getCode(), "Exception: ".$e->getMessage());
        } catch (Exception $e) {
            $this->fail('bad expected exception');
        }
    }

    function testAliasProfile () {
        $myProfile = array('wsdl'=>'books.wsdl', 'option'=>'foo');
        $readedProfile = array('wsdl'=>'books.wsdl', 'option'=>'foo', '_name'=>'server1');

        $allProfiles = array(
            'foo'=>array('default'=>'server1',
                'myserver'=>'server1'),
            'foo:server1'=>  $myProfile
        );

        testJProfiles::testSetProfiles($allProfiles);

        $profile = testJProfiles::get('foo');
        $this->assertEquals($readedProfile, $profile);

        $profile = testJProfiles::get('foo','default');
        $this->assertEquals($readedProfile, $profile);

        $profile = testJProfiles::get('foo','server1');
        $this->assertEquals($readedProfile, $profile);

        $profile = testJProfiles::get('foo','myserver');
        $this->assertEquals($readedProfile, $profile);

        $profile = testJProfiles::get('foo','toto');
        $this->assertEquals($readedProfile, $profile);

        $this->assertEquals( array(
            'foo:server1'=>  $readedProfile,
            'foo:default'=>  $readedProfile,
            'foo:myserver'=>  $readedProfile,
            ), testJProfiles::testGetProfiles());

        try {
            $profile = testJProfiles::get('foo','toto', true);
            $this->fail();
        } catch(jException $e) {
            $this->assertEquals(500, $e->getCode(), "Exception: ".$e->getMessage());
        } catch (Exception $e) {
            $this->fail('bad expected exception');
        }
    }

    function testVirtualProfile() {
        $myProfile = array('wsdl'=>'books.wsdl', 'option'=>'foo');
        $readedProfile = array('wsdl'=>'books.wsdl', 'option'=>'foo', '_name'=>'server1');

        testJProfiles::createVirtualProfile('foo', 'myalias', 'server1');
        $this->assertEquals( array(
            'foo:server1'=>  $readedProfile,
            'foo:default'=>  $readedProfile,
            'foo:myserver'=>  $readedProfile,
            'foo:myalias'=>  $readedProfile,
            ), testJProfiles::testGetProfiles());

        testJProfiles::createVirtualProfile('foo', 'new', array('bla'=>'ok'));
        $this->assertEquals( array(
            'foo:server1'=>  $readedProfile,
            'foo:default'=>  $readedProfile,
            'foo:myserver'=>  $readedProfile,
            'foo:myalias'=>  $readedProfile,
            'foo:new'=>array('bla'=>'ok', '_name'=>'new')
            ), testJProfiles::testGetProfiles());
    }

    function testPool() {
        $this->assertNull(testJProfiles::getFromPool('foo', 'bar'));

        testJProfiles::storeInPool('foo', 'bar', 'a value');
        $this->assertEquals('a value', testJProfiles::getFromPool('foo', 'bar'));

        testJProfiles::clear();
        $this->assertNull(testJProfiles::getFromPool('foo', 'bar'));

    }

    function testGetStorePool() {
        testJProfiles::clear();
        try {
            $this->assertEquals('result:array:foo',
                            testJProfiles::getOrStoreInPool('foo', 'new', array($this, '_getObj')));
            $this->fail();
        } catch(Exception $e) {
            $this->assertEquals('Unknown profile "new" for "foo"', $e->getMessage());
        }
        testJProfiles::createVirtualProfile('foo', 'new', array('bla'=>'ok'));
        $this->assertEquals('result:array:new',
                            testJProfiles::getOrStoreInPool('foo', 'new', array($this, '_getObj')));

        $this->assertEquals('result:array:new', testJProfiles::getFromPool('foo', 'new'));
    }

    public function _getObj($profile){
        $value = 'result:';
        if (is_array($profile))
            $value.='array:';
        if (isset($profile['_name']))
            $value.= $profile['_name'];
        return $value;
    }


}