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

require_once(JELIX_LIB_DAO_PATH.'jDaoCompiler.class.php');

class testDaoGenerator extends jDaoGenerator {


    function GetPreparePHPValue($value, $fieldType, $checknull=true){
        return $this->_preparePHPValue($value, $fieldType, $checknull);
    }

    function GetPreparePHPExpr($expr, $fieldType, $checknull=true, $forCondition=''){
        return $this->_preparePHPExpr($expr, $fieldType, $checknull, $forCondition);
    }
    function GetEncloseName($name){
        return $this->_encloseName($name);
    }
}

class UTDao_generator extends jUnitTestCase {
    protected function getSimpleGenerator(){
        $doc ='<?xml version="1.0"?>
<dao xmlns="http://jelix.org/ns/dao/1.0">
   <datasources>
      <primarytable name="product_test" primarykey="id" />
   </datasources>
   <record>
      <property name="id"   fieldname="id" datatype="autoincrement" required="true" />
      <property name="name" fieldname="name" datatype="string"  required="true"/>
      <property name="price" fieldname="price" datatype="float"/>
   </record>
</dao>';
        $parser = new jDaoParser ();
        $parser->parse(simplexml_load_string($doc));
        return new testDaoGenerator('cDao_foo_Jx_bar_Jx_mysql', 'cDaoRecord_foo_Jx_bar_Jx_mysql', $parser);
    }


    function setUp() {
        jDaoCompiler::$daoId ='';
        jDaoCompiler::$daoPath = '';
        jDaoCompiler::$dbType='mysql';
    }

    function testEncloseName(){
        $doc ='<?xml version="1.0"?>
<dao xmlns="http://jelix.org/ns/dao/1.0">
   <datasources>
      <primarytable name="product_test" primarykey="id" />
   </datasources>
   <record>
      <property name="id"   fieldname="id" datatype="autoincrement" required="true" />
      <property name="name" fieldname="name" datatype="string"  required="true"/>
      <property name="price" fieldname="price" datatype="float"/>
   </record>
</dao>';
        $parser = new jDaoParser ();
        $parser->parse(simplexml_load_string($doc));

        jDaoCompiler::$dbType='mysql';
        $generator= new testDaoGenerator('cDao_foo_Jx_bar_Jx_mysql', 'cDaoRecord_foo_Jx_bar_Jx_mysql', $parser);
        $result = $generator->GetEncloseName('foo');
        $this->assertEqualOrDiff('`foo`',$result);

        jDaoCompiler::$dbType='postgresql';
        $generator= new testDaoGenerator('cDao_foo_Jx_bar_Jx_mysql', 'cDaoRecord_foo_Jx_bar_Jx_mysql', $parser);
        $result = $generator->GetEncloseName('foo');
        $this->assertEqualOrDiff('"foo"',$result);

        jDaoCompiler::$dbType='oracle';
        $generator= new testDaoGenerator('cDao_foo_Jx_bar_Jx_mysql', 'cDaoRecord_foo_Jx_bar_Jx_mysql', $parser);
        $result = $generator->GetEncloseName('foo');
        $this->assertEqualOrDiff('foo',$result);

        jDaoCompiler::$dbType='oci8';
        $generator= new testDaoGenerator('cDao_foo_Jx_bar_Jx_mysql', 'cDaoRecord_foo_Jx_bar_Jx_mysql', $parser);
        $result = $generator->GetEncloseName('foo');
        $this->assertEqualOrDiff('foo',$result);

        jDaoCompiler::$dbType='sqlite';
        $generator= new testDaoGenerator('cDao_foo_Jx_bar_Jx_mysql', 'cDaoRecord_foo_Jx_bar_Jx_mysql', $parser);
        $result = $generator->GetEncloseName('foo');
        $this->assertEqualOrDiff('foo',$result);
    }

    function testPreparePHPExpr(){
        $generator=$this->getSimpleGenerator();

        // with no checknull
        $result = $generator->GetPreparePHPExpr('$foo', 'int',false);
        $this->assertEqualOrDiff('intval($foo)',$result);
        $result = $generator->GetPreparePHPExpr('$foo', 'integer',false);
        $this->assertEqualOrDiff('intval($foo)',$result);
        $result = $generator->GetPreparePHPExpr('$foo', 'autoincrement',false);
        $this->assertEqualOrDiff('intval($foo)',$result);
        $result = $generator->GetPreparePHPExpr('$foo', 'string',false);
        $this->assertEqualOrDiff('$this->_conn->quote($foo)',$result);
        $result = $generator->GetPreparePHPExpr('$foo', 'double',false);
        $this->assertEqualOrDiff('doubleval($foo)',$result);
        $result = $generator->GetPreparePHPExpr('$foo', 'float',false);
        $this->assertEqualOrDiff('doubleval($foo)',$result);
        $result = $generator->GetPreparePHPExpr('$foo', 'numeric',false);
        $this->assertEqualOrDiff('(is_numeric ($foo) ? $foo : intval($foo))',$result);
        $result = $generator->GetPreparePHPExpr('$foo', 'bigautoincrement',false);
        $this->assertEqualOrDiff('(is_numeric ($foo) ? $foo : intval($foo))',$result);

        // with checknull 
        $result = $generator->GetPreparePHPExpr('$foo', 'integer',true);
        $this->assertEqualOrDiff('($foo === null ? \'NULL\' : intval($foo))',$result);
        $result = $generator->GetPreparePHPExpr('$foo', 'autoincrement',true);
        $this->assertEqualOrDiff('($foo === null ? \'NULL\' : intval($foo))',$result);
        $result = $generator->GetPreparePHPExpr('$foo', 'string',true);
        $this->assertEqualOrDiff('($foo === null ? \'NULL\' : $this->_conn->quote($foo))',$result);
        $result = $generator->GetPreparePHPExpr('$foo', 'double',true);
        $this->assertEqualOrDiff('($foo === null ? \'NULL\' : doubleval($foo))',$result);
        $result = $generator->GetPreparePHPExpr('$foo', 'float',true);
        $this->assertEqualOrDiff('($foo === null ? \'NULL\' : doubleval($foo))',$result);
        $result = $generator->GetPreparePHPExpr('$foo', 'numeric',true);
        $this->assertEqualOrDiff('($foo === null ? \'NULL\' : (is_numeric ($foo) ? $foo : intval($foo)))',$result);
        $result = $generator->GetPreparePHPExpr('$foo', 'bigautoincrement',true);
        $this->assertEqualOrDiff('($foo === null ? \'NULL\' : (is_numeric ($foo) ? $foo : intval($foo)))',$result);

        // with checknull and operator =
        $result = $generator->GetPreparePHPExpr('$foo', 'integer',true,'=');
        $this->assertEqualOrDiff('($foo === null ? \'IS NULL\' : \'=\'.intval($foo))',$result);
        $result = $generator->GetPreparePHPExpr('$foo', 'autoincrement',true,'=');
        $this->assertEqualOrDiff('($foo === null ? \'IS NULL\' : \'=\'.intval($foo))',$result);
        $result = $generator->GetPreparePHPExpr('$foo', 'string',true,'=');
        $this->assertEqualOrDiff('($foo === null ? \'IS NULL\' : \'=\'.$this->_conn->quote($foo))',$result);
        $result = $generator->GetPreparePHPExpr('$foo', 'double',true,'=');
        $this->assertEqualOrDiff('($foo === null ? \'IS NULL\' : \'=\'.doubleval($foo))',$result);
        $result = $generator->GetPreparePHPExpr('$foo', 'float',true,'=');
        $this->assertEqualOrDiff('($foo === null ? \'IS NULL\' : \'=\'.doubleval($foo))',$result);
        $result = $generator->GetPreparePHPExpr('$foo', 'numeric',true,'=');
        $this->assertEqualOrDiff('($foo === null ? \'IS NULL\' : \'=\'.(is_numeric ($foo) ? $foo : intval($foo)))',$result);
        $result = $generator->GetPreparePHPExpr('$foo', 'bigautoincrement',true,'=');
        $this->assertEqualOrDiff('($foo === null ? \'IS NULL\' : \'=\'.(is_numeric ($foo) ? $foo : intval($foo)))',$result);

        // with checknull and operator <>
        $result = $generator->GetPreparePHPExpr('$foo', 'integer',true,'<>');
        $this->assertEqualOrDiff('($foo === null ? \'IS NOT NULL\' : \'<>\'.intval($foo))',$result);
        $result = $generator->GetPreparePHPExpr('$foo', 'autoincrement',true,'<>');
        $this->assertEqualOrDiff('($foo === null ? \'IS NOT NULL\' : \'<>\'.intval($foo))',$result);
        $result = $generator->GetPreparePHPExpr('$foo', 'string',true,'<>');
        $this->assertEqualOrDiff('($foo === null ? \'IS NOT NULL\' : \'<>\'.$this->_conn->quote($foo))',$result);
        $result = $generator->GetPreparePHPExpr('$foo', 'double',true,'<>');
        $this->assertEqualOrDiff('($foo === null ? \'IS NOT NULL\' : \'<>\'.doubleval($foo))',$result);
        $result = $generator->GetPreparePHPExpr('$foo', 'float',true,'<>');
        $this->assertEqualOrDiff('($foo === null ? \'IS NOT NULL\' : \'<>\'.doubleval($foo))',$result);
        $result = $generator->GetPreparePHPExpr('$foo', 'numeric',true,'<>');
        $this->assertEqualOrDiff('($foo === null ? \'IS NOT NULL\' : \'<>\'.(is_numeric ($foo) ? $foo : intval($foo)))',$result);
        $result = $generator->GetPreparePHPExpr('$foo', 'bigautoincrement',true,'<>');
        $this->assertEqualOrDiff('($foo === null ? \'IS NOT NULL\' : \'<>\'.(is_numeric ($foo) ? $foo : intval($foo)))',$result);

        // with checknull and other operator <=
        $result = $generator->GetPreparePHPExpr('$foo', 'integer',true,'<=');
        $this->assertEqualOrDiff('\'<=\'.intval($foo)',$result);
        $result = $generator->GetPreparePHPExpr('$foo', 'autoincrement',true,'<=');
        $this->assertEqualOrDiff('\'<=\'.intval($foo)',$result);
        $result = $generator->GetPreparePHPExpr('$foo', 'string',true,'<=');
        $this->assertEqualOrDiff('\'<=\'.$this->_conn->quote($foo)',$result);
        $result = $generator->GetPreparePHPExpr('$foo', 'double',true,'<=');
        $this->assertEqualOrDiff('\'<=\'.doubleval($foo)',$result);
        $result = $generator->GetPreparePHPExpr('$foo', 'float',true,'<=');
        $this->assertEqualOrDiff('\'<=\'.doubleval($foo)',$result);
        $result = $generator->GetPreparePHPExpr('$foo', 'numeric',true,'<=');
        $this->assertEqualOrDiff('\'<=\'.(is_numeric ($foo) ? $foo : intval($foo))',$result);
        $result = $generator->GetPreparePHPExpr('$foo', 'bigautoincrement',true,'<=');
        $this->assertEqualOrDiff('\'<=\'.(is_numeric ($foo) ? $foo : intval($foo))',$result);
    }

    function testPreparePHPValue(){
        $generator=$this->getSimpleGenerator();

        // with no checknull
        $result = $generator->GetPreparePHPValue('5', 'int',false);
        $this->assertEqualOrDiff(5,$result);
        $result = $generator->GetPreparePHPValue('5', 'integer',false);
        $this->assertEqualOrDiff(5,$result);
        $result = $generator->GetPreparePHPValue('5', 'autoincrement',false);
        $this->assertEqualOrDiff(5,$result);
        $result = $generator->GetPreparePHPValue('$foo', 'string',false);
        $this->assertEqualOrDiff('\\\'$foo\\\'',$result);
        $result = $generator->GetPreparePHPValue('$f\'oo', 'string',false);
        $this->assertEqualOrDiff('\'.$this->_conn->quote(\'$f\\\'oo\').\'',$result);
        $result = $generator->GetPreparePHPValue('5.63', 'double',false);
        $this->assertEqualOrDiff(5.63,$result);
        $result = $generator->GetPreparePHPValue('5.63', 'float',false);
        $this->assertEqualOrDiff(5.63,$result);
        $result = $generator->GetPreparePHPValue('565465465463', 'numeric',false);
        $this->assertEqualOrDiff('565465465463',$result);
        $result = $generator->GetPreparePHPValue('565469876543139798641315465463', 'numeric',false);
        $this->assertEqualOrDiff('565469876543139798641315465463',$result);
        $result = $generator->GetPreparePHPValue('565469876543139798641315465463', 'bigautoincrement',false);
        $this->assertEqualOrDiff('565469876543139798641315465463',$result);

        // with checknull 
        $result = $generator->GetPreparePHPValue('5', 'integer',true);
        $this->assertEqualOrDiff(5,$result);
        $result = $generator->GetPreparePHPValue('5', 'autoincrement',true);
        $this->assertEqualOrDiff(5,$result);
        $result = $generator->GetPreparePHPValue('$foo', 'string',true);
        $this->assertEqualOrDiff('\\\'$foo\\\'',$result);
        $result = $generator->GetPreparePHPValue('5.28', 'double',true);
        $this->assertEqualOrDiff(5.28,$result);
        $result = $generator->GetPreparePHPValue('5.26', 'float',true);
        $this->assertEqualOrDiff(5.26,$result);
        $result = $generator->GetPreparePHPValue('565469876543139798641315465463', 'numeric',true);
        $this->assertEqualOrDiff('565469876543139798641315465463',$result);
        $result = $generator->GetPreparePHPValue('565469876543139798641315465463', 'bigautoincrement',true);
        $this->assertEqualOrDiff('565469876543139798641315465463',$result);

    }





}


?>