<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Laurent Jouanneau
* @contributor
* @copyright   2007-2011 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

class jDb_profilesTest  extends jUnitTestCase
{
    public static function setUpBeforeClass() {
        self::initJelixConfig();
    }

    public function tearDown() {
        jProfiles::clear();
        parent::tearDown();
    }

    function testProfile() {
        $p = jProfiles::get('jdb', 'jelix_tests_mysql');
        $expected = array(
            'driver'=>"mysql",
            'database'=>"testapp_mysql",
            'host'=> "localhost_mysql",
            'user'=> "plop_mysql",
            'password'=> "futchball_mysql",
            'persistent'=> '1',
            'force_encoding'=>1,
            '_name'=>'jelix_tests_mysql',
        );

        $this->assertEquals($expected, $p);

        $p = jProfiles::get('jdb', 'forward');
        $expected= array(
            'driver'=>"mysql",
            'database'=>"jelix_tests_forward",
            'host'=> "localhost_forward",
            'user'=> "plop_forward",
            'password'=> "futchball_forward",
            'persistent'=> '1',
            'force_encoding'=>0,
            '_name'=>'jelix_tests_forward',
        );

        $this->assertEquals($expected, $p);

        $p = jProfiles::get('jdb', 'testapp');
        $this->assertEquals('testapp', $p['_name']);
        $p2 = jProfiles::get('jdb');
        $this->assertEquals('testapp', $p2['_name']);
        $this->assertEquals($p, $p2);
        $p = jProfiles::get('jdb', 'testapppdo');
        $this->assertEquals('testapppdo', $p['_name']);
    }

    function testVirtualProfile() {
        $profile = array(
            'driver'=>"mysql",
            'database'=>"virtual_mysql",
            'host'=> "localhostv_mysql",
            'user'=> "v_mysql",
            'password'=> "vir_mysql",
            'persistent'=> '1',
            'force_encoding'=>1
        );

        jProfiles::createVirtualProfile('jdb', 'foobar', $profile);

        $p = jProfiles::get('jdb', 'foobar');
        $profile['_name'] = 'foobar';

        $this->assertEquals($profile, $p);
    }

    function testCommonValues() {

        $profile = array(
            'trace'=> 1,
            'connection_timeout'=> 10,
            'exceptions'=> 1
        );

        jProfiles::createVirtualProfile('acme', '__common__', $profile);

        $profile = array(
            'wsdl'=> "http://example.com/wsdl1",
            'connection_timeout'=>25
        );

        jProfiles::createVirtualProfile('acme', 'foo', $profile);

        $p = jProfiles::get('acme', 'foo');
        $expected = array(
            'trace'=> 1,
            'exceptions'=> 1,
            'connection_timeout'=>25,
            'wsdl'=> "http://example.com/wsdl1",
            '_name'=>'foo',
        );

        $this->assertEquals($expected, $p);
    }

}
