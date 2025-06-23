<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Laurent Jouanneau
* @contributor
* @copyright   2007-2025 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/
use Jelix\Core\Profiles;

class jDb_profilesTest  extends \Jelix\UnitTests\UnitTestCase
{
    public function setUp() : void  {
        self::initJelixConfig();
    }

    public function tearDown() : void  {
        Profiles::clear();
        parent::tearDown();
    }

    function testProfile() {
        $p = Profiles::get('jdb', 'jelix_tests_mysql');
        $expected = array(
            'driver'=>"mysqli",
            'database'=>"testapp_mysql",
            'host'=> "localhost_mysql",
            'user'=> "plop_mysql",
            'password'=> "futchball_mysql",
            'persistent'=> true,
            'force_encoding'=>true,
            '_name'=>'jelix_tests_mysql',
            'usepdo' => false,
            'dbtype' => 'mysql',
            'phpext' => 'mysqli',
            'pdoext' => 'pdo_mysql',
            'pdodriver' => 'mysql',
            'table_prefix' => '',
            'pdooptions' => '',
            'charset' => 'UTF-8',
            'filePathParser' => '\Jelix\Services\Database\DbProfilePlugin::parseSqlitePath'
        );

        $this->assertEquals($expected, $p);

        $p = Profiles::get('jdb', 'forward');
        $expected= array(
            'driver'=>"mysqli",
            'database'=>"jelix_tests_forward",
            'host'=> "localhost_forward",
            'user'=> "plop_forward",
            'password'=> "futchball_forward",
            'persistent'=> true,
            'force_encoding'=>false,
            '_name'=>'jelix_tests_forward',
            'usepdo' => false,
            'dbtype' => 'mysql',
            'phpext' => 'mysqli',
            'pdoext' => 'pdo_mysql',
            'pdodriver' => 'mysql',
            'table_prefix' => '',
            'pdooptions' => '',
            'charset' => 'UTF-8',
            'filePathParser' => '\Jelix\Services\Database\DbProfilePlugin::parseSqlitePath'
        );

        $this->assertEquals($expected, $p);

        $p = Profiles::get('jdb', 'testapp');
        $this->assertEquals('testapp', $p['_name']);
        $p2 = Profiles::get('jdb');
        $this->assertEquals('testapp', $p2['_name']);
        $this->assertEquals($p, $p2);
        $p = Profiles::get('jdb', 'testapppdo');
        $this->assertEquals('testapppdo', $p['_name']);
    }

    function testVirtualProfile() {
        $profile = array(
            'driver'=>'mysql',
            'database'=>"virtual_mysql",
            'host'=> "localhostv_mysql",
            'user'=> "v_mysql",
            'password'=> "vir_mysql",
            'persistent'=> '1',
            'force_encoding'=>1,
            'charset' => 'UTF-8',
            'filePathParser' => '\Jelix\Services\Database\DbProfilePlugin::parseSqlitePath'
        );

        Profiles::createVirtualProfile('jdb', 'foobar', $profile);

        $p = Profiles::get('jdb', 'foobar');
        $profile['_name'] = 'foobar';
        $profile['usepdo'] = false;
        $profile['dbtype'] = 'mysql';
        $profile['phpext'] = 'mysql';
        $profile['pdoext'] = 'pdo_mysql';
        $profile['pdodriver'] = 'mysql';
        $profile['persistent'] = true;
        $profile['force_encoding'] = true;
        $profile['table_prefix'] = '';
        $profile['pdooptions'] = '';

        $this->assertEquals($profile, $p);
    }

    function testCommonValues() {

        $profile = array(
            'trace'=> 1,
            'connection_timeout'=> 10,
            'exceptions'=> 1
        );

        Profiles::createVirtualProfile('acme', '__common__', $profile);

        $profile = array(
            'wsdl'=> "http://example.com/wsdl1",
            'connection_timeout'=>25
        );

        Profiles::createVirtualProfile('acme', 'foo', $profile);

        $p = Profiles::get('acme', 'foo');
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
