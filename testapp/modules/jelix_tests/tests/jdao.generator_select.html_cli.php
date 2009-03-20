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


require_once(dirname(__FILE__).'/daotests.lib.php');

class UTDao_generator_select extends jUnitTestCase {

    protected $_selector;
    protected $_tools;
    
    function setUp() {
        $this->_selector = new fakejSelectorDao('foo','bar','mysql');
        $this->_tools= new mysqlDbTools(null);
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
        $this->assertEqualOrDiff('SELECT `product_test`.`id`, `product_test`.`name`, `product_test`.`price`',$result);

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
        $this->assertEqualOrDiff('SELECT `p`.`id`, `p`.`name`, `p`.`price`',$result);
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
        $this->assertEqualOrDiff('SELECT `product_test`.`id`, `product_test`.`name`, `product_test`.`price`',$result);

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
        $this->assertEqualOrDiff('SELECT `product_test`.`id`, TOUPPER(`product_test`.`name`) as `name`, `product_test`.`price`',$result);

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
        $this->assertEqualOrDiff('SELECT `p`.`id`, TOUPPER(`p`.`name`) as `name`, `p`.`price`',$result);


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
        $this->assertEqualOrDiff('SELECT `product_test`.`id`, TOUPPER(name) as `name`, `product_test`.`price`',$result);


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
        $this->assertEqualOrDiff('SELECT `product_test`.`id`, CONCAT(name,\\\' \\\',price) as `name`, `product_test`.`price`',$result);

    }
}
?>