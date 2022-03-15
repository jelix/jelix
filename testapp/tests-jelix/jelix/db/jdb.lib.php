<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Laurent Jouanneau
* @contributor
* @copyright   2007-2012 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

/**
 * CAREFULL ! DON'T CHANGE THE ORDER OF METHODS
 */
abstract class jDb_queryBase extends jUnitTestCase {

    static protected $productNameType = 'string';
    static protected $productPriceType = 'string';

    function setUp() : void
    {
        parent::setUp();
        static::$productNameType = 'string';
        static::$productPriceType = 'string';
    }


    function testConnection(){
        $cnx = jDb::getConnection($this->dbProfile);
        $this->assertNotNull($cnx, 'connection null !');
        if($this->needPDO)
            $this->assertTrue($cnx instanceof jDbPDOConnection, 'connection null !');
        else
            $this->assertTrue($cnx instanceof jDbConnection, 'connection null !');
    }

    /**
     * @depends testConnection
     */
    function testEmptyATable(){
        $db = jDb::getConnection($this->dbProfile);
        $db->exec('DELETE FROM product_test');

        $rs = $db->query('SELECT count(*) as N FROM product_test');
        if($r=$rs->fetch()){
            $this->assertEquals(0, $r->N, "After a DELETE, product_test table should be empty !!");
        }else{
            $this->fail("After a DELETE, product_test table should be empty, but error when try to get record count");
        }
    }

    /**
     * @depends testEmptyATable
     */
    function testInsert(){
        $db = jDb::getConnection($this->dbProfile);
        $nb = $db->exec("INSERT INTO product_test( name, price) VALUES('camembert',2.31) ");
        $this->assertEquals(1, $nb, 'exec insert 1 should return 1');
        $nb = $db->exec("INSERT INTO product_test( name, price) VALUES('yaourt',0.76) ");
        $this->assertEquals(1, $nb, 'exec insert 2 should return 1');
        $nb = $db->exec("INSERT INTO product_test( name, price) VALUES('gloubi-boulga',4.9)");
        $this->assertEquals(1, $nb, 'exec insert 3 should return 1');
    }

    /**
     * @depends testInsert
     */
    function testSelect(){
        $db = jDb::getConnection($this->dbProfile);
        $resultSet = $db->query('SELECT id,name,price FROM product_test');
        $this->assertNotNull($resultSet, 'a query return null !');
        if ($this->needPDO) {
            if (version_compare(phpversion(), "8.0") < 0) {
                $this->assertTrue($resultSet instanceof jDbPDOResultSet7, 'resultset is not a jDbPDOResultSet');
            }
            else {
                $this->assertTrue($resultSet instanceof jDbPDOResultSet, 'resultset is not a jDbPDOResultSet');
            }
        }
        else {
            $this->assertTrue($resultSet instanceof jDbResultSet, 'resultset is not a jDbResultSet');
        }

        $list = array();
        //foreach($resultSet as $res){
        while($res = $resultSet->fetch()){
            $list[] = $res;
        }
        $this->assertEquals(3, count($list), 'query return bad number of results ('.count($list).')');

        $structure = '<array>
    <object>
        <'.static::$productNameType.' property="name" value="camembert" />
        <'.static::$productPriceType.' property="price" value="2.31" />
    </object>
    <object>
        <'.static::$productNameType.' property="name" value="yaourt" />
        <'.static::$productPriceType.' property="price" value="0.76" />
    </object>
    <object>
        <'.static::$productNameType.' property="name" value="gloubi-boulga" />
        <'.static::$productPriceType.' property="price" value="4.9" />
    </object>
</array>';
        $this->assertComplexIdenticalStr($list, $structure, 'bad results');

        $res = $resultSet->fetch();
        $this->assertTrue($res === false || $res === null);
    }

    function _callbackTest($record, $rs) {
        $record->name.='_suffix';
        $record->price+=10;
    }

    /**
     * @depends testSelect
     */
    function testSelectWithModifier(){
        $db = jDb::getConnection($this->dbProfile);
        $resultSet = $db->query('SELECT id, name,price FROM product_test');
        $this->assertNotNull($resultSet, 'a query return null !');
        if ($this->needPDO) {
            if (version_compare(phpversion(), "8.0") < 0) {
                $this->assertTrue($resultSet instanceof jDbPDOResultSet7, 'resultset is not a jDbPDOResultSet');
            }
            else {
                $this->assertTrue($resultSet instanceof jDbPDOResultSet, 'resultset is not a jDbPDOResultSet');
            }
        }
        else {
            $this->assertTrue($resultSet instanceof jDbResultSet, 'resultset is not a jDbResultSet');
        }

        $resultSet->addModifier(array($this, '_callbackTest'));

        $list = array();
        //foreach($resultSet as $res){
        while($res = $resultSet->fetch()){
            $list[] = $res;
        }
        $this->assertEquals(3, count($list), 'query return bad number of results ('.count($list).')');

        $structure = '<array>
    <object>
        <string property="name" value="camembert_suffix" />
        <float property="price" value="12.31" />
    </object>
    <object>
        <string property="name" value="yaourt_suffix" />
        <float property="price" value="10.76" />
    </object>
    <object>
        <string property="name" value="gloubi-boulga_suffix" />
        <float property="price" value="14.9" />
    </object>
</array>';
        $this->assertComplexIdenticalStr($list, $structure, 'bad results');
    }

    /**
     * @depends testSelectWithModifier
     */
    function testFetchClass(){
        $db = jDb::getConnection($this->dbProfile);
        $resultSet = $db->query('SELECT id,name,price FROM product_test');
        $this->assertNotNull($resultSet, 'a query return null !');
        if ($this->needPDO) {
            if (version_compare(phpversion(), "8.0") < 0) {
                $this->assertTrue($resultSet instanceof jDbPDOResultSet7, 'resultset is not a jDbPDOResultSet');
            }
            else {
                $this->assertTrue($resultSet instanceof jDbPDOResultSet, 'resultset is not a jDbPDOResultSet');
            }
        }
        else {
            $this->assertTrue($resultSet instanceof jDbResultSet, 'resultset is not a jDbResultSet');
        }

        $resultSet->setFetchMode(8, 'MyProductContainer');

        $list = array();
        //foreach($resultSet as $res){
        while($res = $resultSet->fetch()){
            $list[] = $res;
        }
        $this->assertEquals(3, count($list), 'query return bad number of results ('.count($list).')');

        $structure = '<array>
    <object class="MyProductContainer">
        <'.static::$productNameType.' property="name" value="camembert" />
        <'.static::$productPriceType.' property="price" value="2.31" />
    </object>
    <object class="MyProductContainer">
        <'.static::$productNameType.' property="name" value="yaourt" />
        <'.static::$productPriceType.' property="price" value="0.76" />
    </object>
    <object class="MyProductContainer">
        <'.static::$productNameType.' property="name" value="gloubi-boulga" />
        <'.static::$productPriceType.' property="price" value="4.9" />
    </object>
</array>';
        $this->assertComplexIdenticalStr($list, $structure, 'bad results');
    }

    /**
     * @depends testFetchClass
     */
    function testFetchInto(){
        $db = jDb::getConnection($this->dbProfile);
        $resultSet = $db->query('SELECT id,name,price FROM product_test');
        $this->assertNotNull($resultSet, 'a query return null !');
        if ($this->needPDO) {
            if (version_compare(phpversion(), "8.0") < 0) {
                $this->assertTrue($resultSet instanceof jDbPDOResultSet7, 'resultset is not a jDbPDOResultSet');
            }
            else {
                $this->assertTrue($resultSet instanceof jDbPDOResultSet, 'resultset is not a jDbPDOResultSet');
            }
        }
        else {
            $this->assertTrue($resultSet instanceof jDbResultSet, 'resultset is not a jDbResultSet');
        }

        $obj = new MyProductContainer();
        $t = $obj->token = time();
        $resultSet->setFetchMode(jDbConnection::FETCH_INTO, $obj);

        $res = $resultSet->fetch();
        $structure = '<object class="MyProductContainer">
        <'.static::$productNameType.' property="name" value="camembert" />
        <'.static::$productPriceType.' property="price" value="2.31" />
        <integer property="token" value="'.$t.'" />
    </object>';
        $this->assertComplexIdenticalStr($res, $structure, 'bad result');

        $res = $resultSet->fetch();
        $structure = '<object class="MyProductContainer">
        <'.static::$productNameType.' property="name" value="yaourt" />
        <'.static::$productPriceType.' property="price" value="0.76" />
        <integer property="token" value="'.$t.'" />
    </object>';
        $this->assertComplexIdenticalStr($res, $structure, 'bad result');

        $res = $resultSet->fetch();
        $structure = '<object class="MyProductContainer">
        <'.static::$productNameType.' property="name" value="gloubi-boulga" />
        <'.static::$productPriceType.' property="price" value="4.9" />
        <integer property="token" value="'.$t.'" />
    </object>';
        $this->assertComplexIdenticalStr($res, $structure, 'bad result');
        $this->assertFalse(!!$resultSet->fetch());
    }

    /**
     * @depends testFetchInto
     */
    function testTools(){

        $tools = jDb::getConnection($this->dbProfile)->tools();
        $fields = $tools->getFieldList('products');
        $structure = '<array>
    <object key="id" class="jDbFieldProperties">
        <string property="type" value="int" />
        <string property="name" value="id" />
        <boolean property="notNull" value="true" />
        <boolean property="primary" value="true" />
        <boolean property="autoIncrement" value="true" />
        <boolean property="hasDefault" value="false" />
        <null property="default" />
        <integer property="length" value="0" />
    </object>
    <object key="name" class="jDbFieldProperties">
        <string property="type" value="varchar" />
        <string property="name" value="name" />
        <boolean property="notNull" value="true" />
        <boolean property="primary" value="false" />
        <boolean property="autoIncrement" value="false" />
        <boolean property="hasDefault" value="false" />
        <string property="default" value="" />
        <integer property="length" value="150" />
    </object>
    <object key="price" class="jDbFieldProperties">
        <string property="type" value="float" />
        <string property="name" value="price" />
        <boolean property="notNull" value="false" />
        <boolean property="primary" value="false" />
        <boolean property="autoIncrement" value="false" />
        <boolean property="hasDefault" value="true" />
        <string property="default" value="0" />
        <integer property="length" value="0" />
    </object>
    <object key="promo" class="jDbFieldProperties">
        <string property="type" value="tinyint" />
        <string property="name" value="promo" />
        <boolean property="notNull" value="true" />
        <boolean property="primary" value="false" />
        <boolean property="autoIncrement" value="false" />
        <boolean property="hasDefault" value="false" />
        <string property="default" value="" />
        <integer property="length" value="0" />
    </object>
</array>';
        $this->assertComplexIdenticalStr($fields, $structure, 'bad results');
    }


}


class MyProductContainer {
    public $id;
    public $name;
    public $price;

    public $token;
}
