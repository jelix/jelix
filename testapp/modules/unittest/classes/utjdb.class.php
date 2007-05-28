<?php
/**
* @package     testapp
* @subpackage  unittest module
* @author      Jouanneau Laurent
* @contributor
* @copyright   2007 Jouanneau laurent
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/
require_once(dirname(__FILE__).'/junittestcase.class.php');
/**
 * CAREFULL ! DON'T CHANGE THE ORDER OF METHODS
 */
class UTjDb extends jUnitTestCase {

    function testConnection(){
        $cnx = jDb::getConnection($this->dbProfil);
        $this->assertTrue($cnx != null, 'connection null !');
        if($this->needPDO)
            $this->assertTrue($cnx instanceof jDbPDOConnection, 'connection null !');
        else
            $this->assertTrue($cnx instanceof jDbConnection, 'connection null !');
    }

    function testEmptyATable(){
        $db = jDb::getConnection($this->dbProfil);
        $db->exec('DELETE FROM product_test');

        $rs = $db->query('SELECT count(*) as N FROM product_test');
        if($r=$rs->fetch()){
            $this->assertTrue($r->N == 0, "After a DELETE, product_test table should be empty !!");
        }else{
            $this->fail("After a DELETE, product_test table should be empty, but error when try to get record count");
        }
    }

    function testInsert(){
        $db = jDb::getConnection($this->dbProfil);
        $nb = $db->exec("INSERT INTO product_test( name, price) VALUES('camembert',2.31) ");
        $this->assertEqual($nb,1,'exec insert 1 should return 1');
        $nb = $db->exec("INSERT INTO product_test( name, price) VALUES('yaourt',0.76) ");
        $this->assertEqual($nb,1,'exec insert 2 should return 1');
        $nb = $db->exec("INSERT INTO product_test( name, price) VALUES('gloubi-boulga',4.9)");
        $this->assertEqual($nb,1,'exec insert 3 should return 1');
    }

    function testSelect(){
        $db = jDb::getConnection($this->dbProfil);
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

    function testSelectClass(){
        $db = jDb::getConnection($this->dbProfil);
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

}


class MyProductContainer {
    public $id;
    public $name;
    public $price;

}

?>