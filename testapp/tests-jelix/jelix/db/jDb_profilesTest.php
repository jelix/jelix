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

class jDb_profile  extends PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass() {
        jelix_init_test_env();
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
            'name'=>'jelix_tests_mysql',
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
            'name'=>'jelix_tests_forward',
        );

        $this->assertEquals($expected, $p);

        $p = jProfiles::get('jdb', 'testapp');
        $this->assertEquals('testapp', $p['name']);
        $p2 = jProfiles::get('jdb');
        $this->assertEquals('testapp', $p2['name']);
        $this->assertEquals($p, $p2);
        $p = jProfiles::get('jdb', 'testapppdo');
        $this->assertEquals('testapppdo', $p['name']);
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

        $this->assertEqual($profile, $p);
    }
}
