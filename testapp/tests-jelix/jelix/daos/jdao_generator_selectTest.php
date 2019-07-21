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


require_once(__DIR__.'/daotests.lib.php');

class jdao_generator_selectTest extends jUnitTestCase {

    protected $_selector;
    protected $_tools;

    function setUp() {
        $this->_selector = new fakejSelectorDao("foo", "bar", "mysqli", "mysql");
        $this->_tools= new jDbMysqlTools(null);
    }

    function tearDown() {
        $this->_selector = null;
        $this->_tools = null;
    }

    function testBuildSelectClause(){
        $doc ='<?xml version="1.0"?>
<dao xmlns="http://jelix.org/ns/dao/1.0">
   <datasources>
      <primarytable name="product_test" primarykey="id" />
   </datasources>
   <record>
      <property name="id"   fieldname="id" datatype="autoincrement"/>
      <property name="name" fieldname="name" datatype="string"  required="true"/>
      <property name="price" fieldname="price" datatype="float"/>
   </record>
</dao>';
        $parser = new jDaoParser ($this->_selector);
        $parser->parse(simplexml_load_string($doc), $this->_tools);
        $generator= new testMysqlDaoGenerator($this->_selector, $this->_tools, $parser);
        $result = $generator->GetSelectClause();
        $this->assertEquals('SELECT `product_test`.`id`, `product_test`.`name`, `product_test`.`price`',$result);
        list($from, $where) = $generator->GetFromClause();
        $this->assertEquals(' FROM `\'.$this->_conn->prefixTable(\'product_test\').\'` AS `product_test`',$from);
        $this->assertEquals('',$where);

        $doc ='<?xml version="1.0"?>
<dao xmlns="http://jelix.org/ns/dao/1.0">
   <datasources>
      <primarytable name="p" realname="product_test" primarykey="id" />
   </datasources>
   <record>
      <property name="id"   fieldname="id" datatype="autoincrement"/>
      <property name="name" fieldname="name" datatype="string"  required="true"/>
      <property name="price" fieldname="price" datatype="float"/>
   </record>
</dao>';
        $parser = new jDaoParser ($this->_selector);
        $parser->parse(simplexml_load_string($doc), $this->_tools);

        $generator= new testMysqlDaoGenerator($this->_selector, $this->_tools, $parser);
        $result = $generator->GetSelectClause();
        $this->assertEquals('SELECT `p`.`id`, `p`.`name`, `p`.`price`',$result);
        list($from, $where) = $generator->GetFromClause();
        $this->assertEquals(' FROM `\'.$this->_conn->prefixTable(\'product_test\').\'` AS `p`',$from);
        $this->assertEquals('',$where);

        $doc ='<?xml version="1.0"?>
<dao xmlns="http://jelix.org/ns/dao/1.0">
   <datasources>
      <primarytable name="product_test" primarykey="id" />
      <foreigntable name="category" primarykey="cat_id" onforeignkey="cat_id" />
   </datasources>
   <record>
      <property name="id"   fieldname="id" datatype="autoincrement"/>
      <property name="name" fieldname="name" datatype="string"  required="true"/>
      <property name="price" fieldname="price" datatype="float"/>
      <property name="cat_id" datatype="integer" table="category" />
      <property name="category" fieldname="name" table="category" datatype="string" />
   </record>
</dao>';
        $parser = new jDaoParser ($this->_selector);
        $parser->parse(simplexml_load_string($doc), $this->_tools);
        $generator= new testMysqlDaoGenerator($this->_selector, $this->_tools, $parser);
        $result = $generator->GetSelectClause();
        $this->assertEquals('SELECT `product_test`.`id`, `product_test`.`name`, `product_test`.`price`, `category`.`cat_id`, `category`.`name` as `category`',$result);
        list($from, $where) = $generator->GetFromClause();
        $this->assertEquals(' FROM `\'.$this->_conn->prefixTable(\'product_test\').\'` AS `product_test`, `\'.$this->_conn->prefixTable(\'category\').\'` AS `category`',$from);
        $this->assertEquals(' WHERE  `product_test`.`cat_id`=`category`.`cat_id`',$where);

        $doc ='<?xml version="1.0"?>
<dao xmlns="http://jelix.org/ns/dao/1.0">
   <datasources>
      <primarytable name="p" realname="product_test" primarykey="id" />
      <foreigntable name="c" realname="category" primarykey="cat_id" onforeignkey="cat_id" />
   </datasources>
   <record>
      <property name="id"   fieldname="id" datatype="autoincrement"/>
      <property name="name" fieldname="name" datatype="string"  required="true"/>
      <property name="price" fieldname="price" datatype="float"/>
      <property name="cat_id" datatype="integer" table="c" />
      <property name="category" fieldname="name" table="c" datatype="string" />
   </record>
</dao>';
        $parser = new jDaoParser ($this->_selector);
        $parser->parse(simplexml_load_string($doc), $this->_tools);
        $generator= new testMysqlDaoGenerator($this->_selector, $this->_tools, $parser);
        $result = $generator->GetSelectClause();
        $this->assertEquals('SELECT `p`.`id`, `p`.`name`, `p`.`price`, `c`.`cat_id`, `c`.`name` as `category`',$result);
        list($from, $where) = $generator->GetFromClause();
        $this->assertEquals(' FROM `\'.$this->_conn->prefixTable(\'product_test\').\'` AS `p`, `\'.$this->_conn->prefixTable(\'category\').\'` AS `c`',$from);
        $this->assertEquals(' WHERE  `p`.`cat_id`=`c`.`cat_id`',$where);

        $doc ='<?xml version="1.0"?>
<dao xmlns="http://jelix.org/ns/dao/1.0">
   <datasources>
      <primarytable name="p" realname="product_test" primarykey="id" />
      <optionalforeigntable name="c" realname="category" primarykey="cat_id" onforeignkey="cat_id" />
   </datasources>
   <record>
      <property name="id"   fieldname="id" datatype="autoincrement"/>
      <property name="name" fieldname="name" datatype="string"  required="true"/>
      <property name="price" fieldname="price" datatype="float"/>
      <property name="cat_id" datatype="integer" table="c" />
      <property name="category" fieldname="name" table="c" datatype="string" />
   </record>
</dao>';
        $parser = new jDaoParser ($this->_selector);
        $parser->parse(simplexml_load_string($doc), $this->_tools);
        $generator= new testMysqlDaoGenerator($this->_selector, $this->_tools, $parser);
        $result = $generator->GetSelectClause();
        $this->assertEquals('SELECT `p`.`id`, `p`.`name`, `p`.`price`, `c`.`cat_id`, `c`.`name` as `category`',$result);
        list($from, $where) = $generator->GetFromClause();
        $this->assertEquals(' FROM `\'.$this->_conn->prefixTable(\'product_test\').\'` AS `p` LEFT JOIN `\'.$this->_conn->prefixTable(\'category\').\'` AS `c` ON ( `p`.`cat_id`=`c`.`cat_id`)',$from);
        $this->assertEquals('',$where);

        $doc ='<?xml version="1.0"?>
<dao xmlns="http://jelix.org/ns/dao/1.0">
   <datasources>
      <primarytable name="p" realname="product_test" primarykey="id" />
      <optionalforeigntable name="c" realname="category" primarykey="cat_id" onforeignkey="cat_id" />
      <foreigntable name="c2" realname="category" primarykey="cat_id" onforeignkey="cat_id2" />
   </datasources>
   <record>
      <property name="id"   fieldname="id" datatype="autoincrement"/>
      <property name="name" fieldname="name" datatype="string"  required="true"/>
      <property name="price" fieldname="price" datatype="float"/>
      <property name="cat_id" datatype="integer" table="c" />
      <property name="category" fieldname="name" table="c" datatype="string" />
      <property name="cat_id2" datatype="integer" table="c2" />
      <property name="category2" fieldname="name" table="c2" datatype="string" />
   </record>
</dao>';
        $parser = new jDaoParser ($this->_selector);
        $parser->parse(simplexml_load_string($doc), $this->_tools);
        $generator= new testMysqlDaoGenerator($this->_selector, $this->_tools, $parser);
        $result = $generator->GetSelectClause();
        $this->assertEquals('SELECT `p`.`id`, `p`.`name`, `p`.`price`, `c`.`cat_id`, `c`.`name` as `category`, `c2`.`cat_id2`, `c2`.`name` as `category2`',$result);
        list($from, $where) = $generator->GetFromClause();
        $this->assertEquals(' FROM `\'.$this->_conn->prefixTable(\'product_test\').\'` AS `p` LEFT JOIN `\'.$this->_conn->prefixTable(\'category\').\'` AS `c` ON ( `p`.`cat_id`=`c`.`cat_id`), `\'.$this->_conn->prefixTable(\'category\').\'` AS `c2`',$from);
        $this->assertEquals(' WHERE  `p`.`cat_id2`=`c2`.`cat_id`',$where);
    }


    function testSelectPattern(){

        $doc ='<?xml version="1.0"?>
<dao xmlns="http://jelix.org/ns/dao/1.0">
   <datasources>
      <primarytable name="product_test" primarykey="id" />
   </datasources>
   <record>
      <property name="id"   fieldname="id" datatype="autoincrement"/>
      <property name="name" fieldname="name" datatype="string"  required="true" selectpattern="%s"/>
      <property name="price" fieldname="price" datatype="float"/>
   </record>
</dao>';
        $parser = new jDaoParser ($this->_selector);
        $parser->parse(simplexml_load_string($doc), $this->_tools);

        $generator= new testMysqlDaoGenerator($this->_selector, $this->_tools, $parser);
        $result = $generator->GetSelectClause();
        $this->assertEquals('SELECT `product_test`.`id`, `product_test`.`name`, `product_test`.`price`',$result);

        $doc ='<?xml version="1.0"?>
<dao xmlns="http://jelix.org/ns/dao/1.0">
   <datasources>
      <primarytable name="product_test" primarykey="id" />
   </datasources>
   <record>
      <property name="id"   fieldname="id" datatype="autoincrement"/>
      <property name="name" fieldname="name" datatype="string"  required="true" selectpattern="TOUPPER(%s)"/>
      <property name="price" fieldname="price" datatype="float"/>
   </record>
</dao>';
        $parser = new jDaoParser ($this->_selector);
        $parser->parse(simplexml_load_string($doc), $this->_tools);

        $generator= new testMysqlDaoGenerator($this->_selector, $this->_tools, $parser);
        $result = $generator->GetSelectClause();
        $this->assertEquals('SELECT `product_test`.`id`, TOUPPER(`product_test`.`name`) as `name`, `product_test`.`price`',$result);

        $doc ='<?xml version="1.0"?>
<dao xmlns="http://jelix.org/ns/dao/1.0">
   <datasources>
      <primarytable name="p" realname="product_test" primarykey="id" />
   </datasources>
   <record>
      <property name="id"   fieldname="id" datatype="autoincrement"/>
      <property name="name" fieldname="name" datatype="string"  required="true" selectpattern="TOUPPER(%s)"/>
      <property name="price" fieldname="price" datatype="float"/>
   </record>
</dao>';
        $parser = new jDaoParser ($this->_selector);
        $parser->parse(simplexml_load_string($doc), $this->_tools);

        $generator= new testMysqlDaoGenerator($this->_selector, $this->_tools, $parser);
        $result = $generator->GetSelectClause();
        $this->assertEquals('SELECT `p`.`id`, TOUPPER(`p`.`name`) as `name`, `p`.`price`',$result);


        $doc ='<?xml version="1.0"?>
<dao xmlns="http://jelix.org/ns/dao/1.0">
   <datasources>
      <primarytable name="product_test" primarykey="id" />
   </datasources>
   <record>
      <property name="id"   fieldname="id" datatype="autoincrement"/>
      <property name="name" fieldname="name" datatype="string"  required="true" selectpattern="TOUPPER(name)"/>
      <property name="price" fieldname="price" datatype="float"/>
   </record>
</dao>';
        $parser = new jDaoParser ($this->_selector);
        $parser->parse(simplexml_load_string($doc), $this->_tools);

        $generator= new testMysqlDaoGenerator($this->_selector, $this->_tools, $parser);
        $result = $generator->GetSelectClause();
        $this->assertEquals('SELECT `product_test`.`id`, TOUPPER(name) as `name`, `product_test`.`price`',$result);


        $doc ='<?xml version="1.0"?>
<dao xmlns="http://jelix.org/ns/dao/1.0">
   <datasources>
      <primarytable name="product_test" primarykey="id" />
   </datasources>
   <record>
      <property name="id"   fieldname="id" datatype="autoincrement"/>
      <property name="name" fieldname="name" datatype="string"  required="true" selectpattern="CONCAT(name,\' \',price)"/>
      <property name="price" fieldname="price" datatype="float"/>
   </record>
</dao>';
        $parser = new jDaoParser ($this->_selector);
        $parser->parse(simplexml_load_string($doc), $this->_tools);

        $generator= new testMysqlDaoGenerator($this->_selector, $this->_tools, $parser);
        $result = $generator->GetSelectClause();
        $this->assertEquals('SELECT `product_test`.`id`, CONCAT(name,\\\' \\\',price) as `name`, `product_test`.`price`',$result);

    }


    function testBuildSelectCountClause(){

        $doc ='<?xml version="1.0"?>
<dao xmlns="http://jelix.org/ns/dao/1.0">
   <datasources>
      <primarytable name="product_test" primarykey="id" />
   </datasources>
   <record>
      <property name="id"   fieldname="id" datatype="autoincrement"/>
      <property name="name" fieldname="name" datatype="string"  required="true"/>
      <property name="price" fieldname="price" datatype="float"/>
   </record>
   <factory>
    <method name="method1" type="count">
    </method>
    <method name="method2" type="count">
        <conditions>
           <eq property="price" value="1" />
        </conditions>
    </method>
    </factory>
</dao>';
        $parser = new jDaoParser ($this->_selector);
        $parser->parse(simplexml_load_string($doc), $this->_tools);
        $generator= new testMysqlDaoGenerator($this->_selector, $this->_tools, $parser);

        $methods = $parser->getMethods();
        $result = $generator->GetBuildCountUserQuery($methods['method1']);
        $this->assertEquals('    $__query = \'SELECT COUNT(*) as c \'.$this->_fromClause.$this->_whereClause;',$result);

        $result = $generator->GetBuildCountUserQuery($methods['method2']);
        $this->assertEquals('    $__query = \'SELECT COUNT(*) as c \'.$this->_fromClause.$this->_whereClause;
$__query .=\' WHERE  `product_test`.`price` = 1\';',$result);

        $doc ='<?xml version="1.0"?>
<dao xmlns="http://jelix.org/ns/dao/1.0">
   <datasources>
      <primarytable name="p" realname="product_test" primarykey="id" />
      <foreigntable name="c" realname="category" primarykey="cat_id" onforeignkey="cat_id" />
   </datasources>
   <record>
      <property name="id"   fieldname="id" datatype="autoincrement"/>
      <property name="name" fieldname="name" datatype="string"  required="true"/>
      <property name="price" fieldname="price" datatype="float"/>
      <property name="cat_id" datatype="integer" table="c" />
      <property name="category" fieldname="name" table="c" datatype="string" />
   </record>
   <factory>
    <method name="method1" type="count">
    </method>
    <method name="method2" type="count">
        <conditions>
           <eq property="cat_id" value="1" />
        </conditions>
    </method>
    </factory>
</dao>';
        $parser = new jDaoParser ($this->_selector);
        $parser->parse(simplexml_load_string($doc), $this->_tools);
        $generator= new testMysqlDaoGenerator($this->_selector, $this->_tools, $parser);
        $methods = $parser->getMethods();
        $result = $generator->GetBuildCountUserQuery($methods['method1']);
        $this->assertEquals('    $__query = \'SELECT COUNT(*) as c \'.$this->_fromClause.$this->_whereClause;',$result);

        $result = $generator->GetBuildCountUserQuery($methods['method2']);
        $this->assertEquals('    $__query = \'SELECT COUNT(*) as c \'.$this->_fromClause.$this->_whereClause;
$__query .=\' WHERE  `c`.`cat_id` = 1\';',$result);
    }
}
?>
