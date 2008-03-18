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

require_once(JELIX_LIB_PATH.'dao/jDaoCompiler.class.php');

require_once(JELIX_LIB_PATH.'plugins/db/mysql/mysql.daobuilder.php');


class testSelectMysqlDaoGenerator extends mysqlDaoBuilder {

    function GetSelectClause ($distinct=false){
        return $this->_getSelectClause ($distinct);
    }

    function GetFromClause(){
        return $this->_getFromClause();
    }

}

class UTDao_generator_select extends jUnitTestCase {

    function setUp() {
        jDaoCompiler::$daoId ='';
        jDaoCompiler::$daoPath = '';
        jDaoCompiler::$dbType='mysql';
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
        $parser = new jDaoParser ();
        $parser->parse(simplexml_load_string($doc));
        $generator= new testSelectMysqlDaoGenerator('cDao_foo_Jx_bar_Jx_mysql', 'cDaoRecord_foo_Jx_bar_Jx_mysql', $parser);
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
        $parser = new jDaoParser ();
        $parser->parse(simplexml_load_string($doc));

        $generator= new testSelectMysqlDaoGenerator('cDao_foo_Jx_bar_Jx_mysql', 'cDaoRecord_foo_Jx_bar_Jx_mysql', $parser);
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
        $parser = new jDaoParser ();
        $parser->parse(simplexml_load_string($doc));

        $generator= new testSelectMysqlDaoGenerator('cDao_foo_Jx_bar_Jx_mysql', 'cDaoRecord_foo_Jx_bar_Jx_mysql', $parser);
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
        $parser = new jDaoParser ();
        $parser->parse(simplexml_load_string($doc));

        $generator= new testSelectMysqlDaoGenerator('cDao_foo_Jx_bar_Jx_mysql', 'cDaoRecord_foo_Jx_bar_Jx_mysql', $parser);
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
        $parser = new jDaoParser ();
        $parser->parse(simplexml_load_string($doc));

        $generator= new testSelectMysqlDaoGenerator('cDao_foo_Jx_bar_Jx_mysql', 'cDaoRecord_foo_Jx_bar_Jx_mysql', $parser);
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
        $parser = new jDaoParser ();
        $parser->parse(simplexml_load_string($doc));

        $generator= new testSelectMysqlDaoGenerator('cDao_foo_Jx_bar_Jx_mysql', 'cDaoRecord_foo_Jx_bar_Jx_mysql', $parser);
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
        $parser = new jDaoParser ();
        $parser->parse(simplexml_load_string($doc));

        $generator= new testSelectMysqlDaoGenerator('cDao_foo_Jx_bar_Jx_mysql', 'cDaoRecord_foo_Jx_bar_Jx_mysql', $parser);
        $result = $generator->GetSelectClause();
        $this->assertEqualOrDiff('SELECT `product_test`.`id`, CONCAT(name,\\\' \\\',price) as `name`, `product_test`.`price`',$result);

    }
}
?>