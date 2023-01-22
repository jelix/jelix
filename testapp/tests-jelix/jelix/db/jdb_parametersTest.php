<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Laurent Jouanneau
* @contributor
* @copyright   2015 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/


class jDb_parametersTest  extends \Jelix\UnitTests\UnitTestCase
{
    public function setUp() : void {
        self::initJelixConfig();
    }

    public function tearDown() : void {
        parent::tearDown();
    }

    public function testEmptyProfile() {

        $profile = array();
        try {
            $param = new jDbParameters($profile);
            $this->fail("no exception occured");
        }
        catch(Exception $e) {
            $this->assertEquals("jDb profile: driver is missing", $e->getMessage());
        }
    }

    public function testNormalizeMysqli() {

        $profile = array(
            "driver"=>"mysqli",
            "host"=>"localhost",
            "database"=>"jelix"
            );
        $param = new jDbParameters($profile);
        $result = $param->getParameters();
        $this->assertEquals("mysql", $result['dbtype']);
        $this->assertEquals("mysqli", $result['phpext']);
        $this->assertEquals("mysqli", $result['driver']);
        $this->assertEquals("mysql", $result['pdodriver']);
        $this->assertEquals("pdo_mysql", $result['pdoext']);
        $this->assertFalse($result['usepdo']);
    }

    public function testNormalizeMysqliUsePdo() {

        $profile = array(
            "driver"=>"mysqli",
            "host"=>"localhost",
            "database"=>"jelix",
            "usepdo"=>true,
            "foo"=>"aaa",
            "bar"=>"bbb"
            );
        $param = new jDbParameters($profile);
        $result = $param->getParameters();
        $this->assertEquals("mysql", $result['dbtype']);
        $this->assertEquals("mysqli", $result['phpext']);
        $this->assertEquals("mysqli", $result['driver']);
        $this->assertEquals("mysql", $result['pdodriver']);
        $this->assertEquals("pdo_mysql", $result['pdoext']);
        $this->assertEquals("mysql:host=localhost;dbname=jelix", $result['dsn']);
        $this->assertEquals("", $result['pdooptions']);
        $this->assertTrue($result['usepdo']);
    }

    public function testNormalizeMysqlUsePdo() {

        $profile = array(
            "driver"=>"mysql",
            "host"=>"localhost",
            "database"=>"jelix",
            "usepdo"=>true
            );
        $param = new jDbParameters($profile);
        $result = $param->getParameters();
        $this->assertEquals("mysql", $result['dbtype']);
        $this->assertEquals("mysql", $result['phpext']);
        $this->assertEquals("mysql", $result['driver']);
        $this->assertEquals("mysql", $result['pdodriver']);
        $this->assertEquals("pdo_mysql", $result['pdoext']);
        $this->assertEquals("mysql:host=localhost;dbname=jelix", $result['dsn']);
        $this->assertEquals("", $result['pdooptions']);
        $this->assertTrue($result['usepdo']);
    }

    public function testNormalizeMysqliUsePdoFail() {

        $profile = array(
            "driver"=>"mysqli",
            "database"=>"jelix",
            "usepdo"=>true
            );
        try {
            $param = new jDbParameters($profile);
            $this->fail("no exception occured");
        }
        catch(Exception $e) {
            $this->assertEquals("Parameter host is required for pdo driver mysql", $e->getMessage());
        }
    }

    public function testNormalizePDOMysqli() {

        $profile = array(
            "driver"=>"pdo",
            "dsn"=>"mysql:host=localhost;dbname=jelix",
        );
        $param = new jDbParameters($profile);
        $result = $param->getParameters();
        $this->assertEquals("mysql", $result['dbtype']);
        $this->assertEquals("mysqli", $result['phpext']);
        $this->assertEquals("mysqli", $result['driver']);
        $this->assertEquals("mysql", $result['pdodriver']);
        $this->assertEquals("pdo_mysql", $result['pdoext']);
        $this->assertEquals("mysql:host=localhost;dbname=jelix", $result['dsn']);
        $this->assertEquals("", $result['pdooptions']);
        $this->assertTrue($result['usepdo']);
    }
    public function testNormalizePDOMysql() {

        $profile = array(
            "driver"=>"pdo",
            "dsn"=>"mysql:host=localhost;dbname=jelix",
        );
        $param = new jDbParameters($profile);
        $result = $param->getParameters();
        $this->assertEquals("mysql", $result['dbtype']);
        $this->assertEquals("mysqli", $result['phpext']);
        $this->assertEquals("mysqli", $result['driver']);
        $this->assertEquals("mysql", $result['pdodriver']);
        $this->assertEquals("pdo_mysql", $result['pdoext']);
        $this->assertEquals("mysql:host=localhost;dbname=jelix", $result['dsn']);
        $this->assertEquals("", $result['pdooptions']);
        $this->assertTrue($result['usepdo']);
    }
    public function testCheckExtensionSqlite() {
        $profile = array(
            "driver"=>"sqlite3",
            "database"=>"/jelix"
            );
        $param = new jDbParameters($profile);
        $this->assertTrue($param->isExtensionActivated());

        $profile = array(
            "driver"=>"sqlite3",
            "database"=>"/jelix",
            "usepdo"=>true,
            );
        $param = new jDbParameters($profile);
        $this->assertTrue($param->isExtensionActivated());
    }

    public function testCheckExtensionMysql() {
        $profile = array(
            "driver"=>"mysqli",
            "host"=>"localhost",
            "database"=>"jelix"
            );
        $param = new jDbParameters($profile);
        $this->assertTrue($param->isExtensionActivated());

        $profile = array(
            "driver"=>"mysqli",
            "host"=>"localhost",
            "database"=>"jelix",
            "usepdo"=>true
            );
        $param = new jDbParameters($profile);
        $this->assertTrue($param->isExtensionActivated());
    }

    public function testCheckExtensionOciFail() {
        $profile = array(
            "driver"=>"oci",
            "host"=>"localhost",
            "database"=>"jelix"
            );
        $param = new jDbParameters($profile);
        $this->assertFalse($param->isExtensionActivated());

        $profile = array(
            "driver"=>"oci",
            "host"=>"localhost",
            "database"=>"jelix",
            "usepdo"=>true
            );
        $param = new jDbParameters($profile);
        $this->assertFalse($param->isExtensionActivated());
    }

}