<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Jouanneau Laurent
* @contributor
* @copyright   2007 Jouanneau laurent
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

/**
 * CAREFULL ! DON'T CHANGE THE ORDER OF METHODS
 */
class UTjDb extends jUnitTestCase {

    function testConnection(){
        $cnx = jDb::getConnection($this->dbProfile);
        $this->assertTrue($cnx != null, 'connection null !');
        if($this->needPDO)
            $this->assertTrue($cnx instanceof jDbPDOConnection, 'connection null !');
        else
            $this->assertTrue($cnx instanceof jDbConnection, 'connection null !');
    }

    function testEmptyATable(){
        $db = jDb::getConnection($this->dbProfile);
        $db->exec('DELETE FROM product_test');

        $rs = $db->query('SELECT count(*) as N FROM product_test');
        if($r=$rs->fetch()){
            $this->assertTrue($r->N == 0, "After a DELETE, product_test table should be empty !!");
        }else{
            $this->fail("After a DELETE, product_test table should be empty, but error when try to get record count");
        }
    }

    function testInsert(){
        $db = jDb::getConnection($this->dbProfile);
        $nb = $db->exec("INSERT INTO product_test( name, price) VALUES('camembert',2.31) ");
        $this->assertEqual($nb,1,'exec insert 1 should return 1');
        $nb = $db->exec("INSERT INTO product_test( name, price) VALUES('yaourt',0.76) ");
        $this->assertEqual($nb,1,'exec insert 2 should return 1');
        $nb = $db->exec("INSERT INTO product_test( name, price) VALUES('gloubi-boulga',4.9)");
        $this->assertEqual($nb,1,'exec insert 3 should return 1');
    }

    function testSelect(){
        $db = jDb::getConnection($this->dbProfile);
        $resultSet = $db->query('SELECT id,name,price FROM product_test');
        $this->assertNotNull($resultSet, 'a query return null !');
        if($this->needPDO)
            $this->assertTrue($resultSet instanceof jDbPDOResultSet, 'resultset is not a jDbPDOResultSet');
        else
            $this->assertTrue($resultSet instanceof jDbResultSet, 'resultset is not a jDbResultSet');

        $list = array();
        //foreach($resultSet as $res){
        while($res = $resultSet->fetch()){
            $list[] = $res;
        }
        $this->assertEqual(count($list), 3, 'query return bad number of results ('.count($list).')');

        $structure = '<array>
    <object>
        <string property="name" value="camembert" />
        <string property="price" value="2.31" />
    </object>
    <object>
        <string property="name" value="yaourt" />
        <string property="price" value="0.76" />
    </object>
    <object>
        <string property="name" value="gloubi-boulga" />
        <string property="price" value="4.9" />
    </object>
</array>';
        $this->assertComplexIdenticalStr($list, $structure, 'bad results');
    }

    function _callbackTest($record, $rs) {
        $record->name.='_suffix';
        $record->price+=10;
    }

    function testSelectWithModifier(){
        $db = jDb::getConnection($this->dbProfile);
        $resultSet = $db->query('SELECT id, name,price FROM product_test');
        $this->assertNotNull($resultSet, 'a query return null !');
        if($this->needPDO)
            $this->assertTrue($resultSet instanceof jDbPDOResultSet, 'resultset is not a jDbPDOResultSet');
        else
            $this->assertTrue($resultSet instanceof jDbResultSet, 'resultset is not a jDbResultSet');

        $resultSet->addModifier(array($this, '_callbackTest'));

        $list = array();
        //foreach($resultSet as $res){
        while($res = $resultSet->fetch()){
            $list[] = $res;
        }
        $this->assertEqual(count($list), 3, 'query return bad number of results ('.count($list).')');

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

    function testFetchClass(){
        $db = jDb::getConnection($this->dbProfile);
        $resultSet = $db->query('SELECT id,name,price FROM product_test');
        $this->assertNotNull($resultSet, 'a query return null !');
        if($this->needPDO)
            $this->assertTrue($resultSet instanceof jDbPDOResultSet, 'resultset is not a jDbPDOResultSet');
        else
            $this->assertTrue($resultSet instanceof jDbResultSet, 'resultset is not a jDbResultSet');

        $resultSet->setFetchMode(8, 'MyProductContainer');

        $list = array();
        //foreach($resultSet as $res){
        while($res = $resultSet->fetch()){
            $list[] = $res;
        }
        $this->assertEqual(count($list), 3, 'query return bad number of results ('.count($list).')');

        $structure = '<array>
    <object class="MyProductContainer">
        <string property="name" value="camembert" />
        <string property="price" value="2.31" />
    </object>
    <object class="MyProductContainer">
        <string property="name" value="yaourt" />
        <string property="price" value="0.76" />
    </object>
    <object class="MyProductContainer">
        <string property="name" value="gloubi-boulga" />
        <string property="price" value="4.9" />
    </object>
</array>';
        $this->assertComplexIdenticalStr($list, $structure, 'bad results');
    }

    function testFetchInto(){
        $db = jDb::getConnection($this->dbProfile);
        $resultSet = $db->query('SELECT id,name,price FROM product_test');
        $this->assertNotNull($resultSet, 'a query return null !');
        if($this->needPDO)
            $this->assertTrue($resultSet instanceof jDbPDOResultSet, 'resultset is not a jDbPDOResultSet');
        else
            $this->assertTrue($resultSet instanceof jDbResultSet, 'resultset is not a jDbResultSet');

        $obj = new MyProductContainer();
        $t = $obj->token = time();
        $resultSet->setFetchMode(jDbConnection::FETCH_INTO, $obj);

        $res = $resultSet->fetch();
        $structure = '<object class="MyProductContainer">
        <string property="name" value="camembert" />
        <string property="price" value="2.31" />
        <integer property="token" value="'.$t.'" />
    </object>';
        $this->assertComplexIdenticalStr($res, $structure, 'bad result');

        $res = $resultSet->fetch();
        $structure = '<object class="MyProductContainer">
        <string property="name" value="yaourt" />
        <string property="price" value="0.76" />
        <integer property="token" value="'.$t.'" />
    </object>';
        $this->assertComplexIdenticalStr($res, $structure, 'bad result');

        $res = $resultSet->fetch();
        $structure = '<object class="MyProductContainer">
        <string property="name" value="gloubi-boulga" />
        <string property="price" value="4.9" />
        <integer property="token" value="'.$t.'" />
    </object>';
        $this->assertComplexIdenticalStr($res, $structure, 'bad result');
        $this->assertEqual($resultSet->fetch(), false);
    }

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

?>