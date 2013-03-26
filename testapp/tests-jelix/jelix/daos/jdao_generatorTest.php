<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Laurent Jouanneau
* @contributor
* @copyright   2007 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

require_once(__DIR__.'/daotests.lib.php');


class jdao_generatorTest extends jUnitTestCase {

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
        $parser = new jDaoParser ($this->_selector);
        $parser->parse(simplexml_load_string($doc), $this->_tools);
        return new testMysqlDaoGenerator($this->_selector, $this->_tools, $parser);
    }

    protected $_selector;
    protected $_tools;

    function setUp() {
        $this->_selector = new fakejSelectorDao("foo", "bar", "mysql");
        $this->_tools= new mysqlDbTools(null);
    }

    function tearDown() {
        $this->_selector = null;
        $this->_tools = null;
    }

    protected $_generator;
    protected function _getProp($type, $expr, $checknull, $op='') {
        $u = $this->_tools->getTypeInfo($type);
        $prop = new testDaoProperty();
        $prop->datatype = $type;
        $prop->unifiedType = $u[1];
        $prop->autoIncrement = $u[6];
        return $this->_generator->GetPreparePHPExpr($expr, $prop, $checknull,$op);
    }

    function testPreparePHPExpr(){
        $this->_generator = $this->getSimpleGenerator();

        // with no checknull

        $this->assertEquals('intval($foo)',$this->_getProp('int','$foo', false));
        $this->assertEquals('intval($foo)',$this->_getProp('integer','$foo', false));
        $this->assertEquals('intval($foo)',$this->_getProp('autoincrement','$foo', false));
        $this->assertEquals('$this->_conn->quote($foo)',$this->_getProp('string','$foo', false));
        $this->assertEquals('jDb::floatToStr($foo)',$this->_getProp('double','$foo', false));
        $this->assertEquals('jDb::floatToStr($foo)',$this->_getProp('float','$foo', false));
        $this->assertEquals('jDb::floatToStr($foo)',$this->_getProp('numeric','$foo', false));
        $this->assertEquals('jDb::floatToStr($foo)',$this->_getProp('bigautoincrement','$foo', false));

        // with checknull 
        $this->assertEquals('($foo === null ? \'NULL\' : intval($foo))',$this->_getProp('integer','$foo', true));
        $this->assertEquals('($foo === null ? \'NULL\' : intval($foo))',$this->_getProp('autoincrement','$foo', true));
        $this->assertEquals('($foo === null ? \'NULL\' : $this->_conn->quote2($foo,false))',$this->_getProp('string','$foo', true));
        $this->assertEquals('($foo === null ? \'NULL\' : jDb::floatToStr($foo))',$this->_getProp('double','$foo', true));
        $this->assertEquals('($foo === null ? \'NULL\' : jDb::floatToStr($foo))',$this->_getProp('float','$foo', true));
        $this->assertEquals('($foo === null ? \'NULL\' : jDb::floatToStr($foo))',$this->_getProp('numeric','$foo', true));
        $this->assertEquals('($foo === null ? \'NULL\' : jDb::floatToStr($foo))',$this->_getProp('bigautoincrement','$foo', true));

        // with checknull and operator =
        $this->assertEquals('($foo === null ? \'IS NULL\' : \' = \'.intval($foo))',$this->_getProp('integer','$foo', true,'='));
        $this->assertEquals('($foo === null ? \'IS NULL\' : \' = \'.intval($foo))',$this->_getProp('autoincrement','$foo', true,'='));
        $this->assertEquals('($foo === null ? \'IS NULL\' : \' = \'.$this->_conn->quote2($foo,false))',$this->_getProp('string','$foo', true,'='));
        $this->assertEquals('($foo === null ? \'IS NULL\' : \' = \'.jDb::floatToStr($foo))',$this->_getProp('double','$foo', true,'='));
        $this->assertEquals('($foo === null ? \'IS NULL\' : \' = \'.jDb::floatToStr($foo))',$this->_getProp('float','$foo', true,'='));
        $this->assertEquals('($foo === null ? \'IS NULL\' : \' = \'.jDb::floatToStr($foo))',$this->_getProp('numeric','$foo', true,'='));
        $this->assertEquals('($foo === null ? \'IS NULL\' : \' = \'.jDb::floatToStr($foo))',$this->_getProp('bigautoincrement','$foo', true,'='));

        // with checknull with default value and operator =
        /*$prop->defaultValue=34;
        $prop->datatype='integer';
        $result = $generator->GetPreparePHPExpr('$foo', $prop, true,'=');
        $this->assertEquals('($foo === null ? \'IS NULL\' : ($foo === \'\'?\'=\'.34:\'=\'.intval($foo)))',$result);
        $prop->datatype='autoincrement';
        $result = $generator->GetPreparePHPExpr('$foo', $prop, true,'=');
        $this->assertEquals('\'=\'.intval($foo)',$result);
        $prop->datatype='string';
        $result = $generator->GetPreparePHPExpr('$foo', $prop, true,'=');
        $this->assertEquals('($foo === null ? \'IS NULL\' : \'=\'.$this->_conn->quote2($foo,false))',$result);
        $prop->defaultValue=34.6;
        $prop->datatype='double';
        $result = $generator->GetPreparePHPExpr('$foo', $prop, true,'=');
        $this->assertEquals('($foo === null ? \'IS NULL\' : ($foo === \'\'?\'=\'.34.6:\'=\'.doubleval($foo)))',$result);
        $prop->defaultValue=34.6;
        $prop->datatype='float';
        $result = $generator->GetPreparePHPExpr('$foo', $prop, true,'=');
        $this->assertEquals('($foo === null ? \'IS NULL\' : ($foo === \'\'?\'=\'.34.6:\'=\'.doubleval($foo)))',$result);
        $prop->datatype='numeric';
        $result = $generator->GetPreparePHPExpr('$foo', $prop, true,'=');
        $this->assertEquals('($foo === null ? \'IS NULL\' : \'=\'.(is_numeric ($foo) ? $foo : intval($foo)))',$result);
        $prop->datatype='bigautoincrement';
        $result = $generator->GetPreparePHPExpr('$foo', $prop, true,'=');
        $this->assertEquals('\'=\'.(is_numeric ($foo) ? $foo : intval($foo))',$result);
        $prop->defaultValue = null;*/

        // with checknull and operator <>
        $result = $this->_getProp('integer','$foo', true,'<>');
        $this->assertEquals('($foo === null ? \'IS NOT NULL\' : \' <> \'.intval($foo))',$result);
        $result = $this->_getProp('autoincrement','$foo', true,'<>');
        $this->assertEquals('($foo === null ? \'IS NOT NULL\' : \' <> \'.intval($foo))',$result);
        $result = $this->_getProp('string','$foo', true,'<>');
        $this->assertEquals('($foo === null ? \'IS NOT NULL\' : \' <> \'.$this->_conn->quote2($foo,false))',$result);
        $result = $this->_getProp('double','$foo', true,'<>');
        $this->assertEquals('($foo === null ? \'IS NOT NULL\' : \' <> \'.jDb::floatToStr($foo))',$result);
        $result = $this->_getProp('float','$foo', true,'<>');
        $this->assertEquals('($foo === null ? \'IS NOT NULL\' : \' <> \'.jDb::floatToStr($foo))',$result);
        $result = $this->_getProp('numeric','$foo', true,'<>');
        $this->assertEquals('($foo === null ? \'IS NOT NULL\' : \' <> \'.jDb::floatToStr($foo))',$result);
        $result = $this->_getProp('bigautoincrement','$foo', true,'<>');
        $this->assertEquals('($foo === null ? \'IS NOT NULL\' : \' <> \'.jDb::floatToStr($foo))',$result);

        // with checknull and other operator <=
        $result = $this->_getProp('integer','$foo', true,'<=');
        $this->assertEquals('\' <= \'.intval($foo)',$result);
        $result = $this->_getProp('autoincrement','$foo', true,'<=');
        $this->assertEquals('\' <= \'.intval($foo)',$result);
        $result = $this->_getProp('string','$foo', true,'<=');
        $this->assertEquals('\' <= \'.$this->_conn->quote($foo)',$result);
        $result = $this->_getProp('double','$foo', true,'<=');
        $this->assertEquals('\' <= \'.jDb::floatToStr($foo)',$result);
        $result = $this->_getProp('float','$foo', true,'<=');
        $this->assertEquals('\' <= \'.jDb::floatToStr($foo)',$result);
        $result = $this->_getProp('numeric','$foo', true,'<=');
        $this->assertEquals('\' <= \'.jDb::floatToStr($foo)',$result);
        $result = $this->_getProp('bigautoincrement','$foo', true,'<=');
        $this->assertEquals('\' <= \'.jDb::floatToStr($foo)',$result);

        // with checknull and other operator LIKE
        $result = $this->_getProp('integer','$foo', true,'LIKE');
        $this->assertEquals('\' LIKE \'.$this->_conn->quote($foo)',$result);
        $result = $this->_getProp('autoincrement','$foo', true,'LIKE');
        $this->assertEquals('\' LIKE \'.$this->_conn->quote($foo)',$result);
        $result = $this->_getProp('string','$foo', true,'LIKE');
        $this->assertEquals('\' LIKE \'.$this->_conn->quote($foo)',$result);
        $result = $this->_getProp('double','$foo', true,'LIKE');
        $this->assertEquals('\' LIKE \'.$this->_conn->quote($foo)',$result);
        $result = $this->_getProp('float','$foo', true,'LIKE');
        $this->assertEquals('\' LIKE \'.$this->_conn->quote($foo)',$result);
        $result = $this->_getProp('numeric','$foo', true,'LIKE');
        $this->assertEquals('\' LIKE \'.$this->_conn->quote($foo)',$result);
        $result = $this->_getProp('bigautoincrement','$foo', true,'LIKE');
        $this->assertEquals('\' LIKE \'.$this->_conn->quote($foo)',$result);
    }
 
    function testBuildSQLCondition(){
        $doc ='<?xml version="1.0" encoding="UTF-8"?>
<dao xmlns="http://jelix.org/ns/dao/1.0">
    <datasources>
        <primarytable name="grp" realname="jacl_group" primarykey="id_aclgrp" />
    </datasources>
    <record>
      <property name="id_aclgrp" fieldname="id_aclgrp" datatype="autoincrement" required="yes"/>
      <property name="parent_id" required="false" datatype="int" />
      <property name="name" fieldname="name" datatype="string" required="yes"/>
      <property name="grouptype" fieldname="grouptype" datatype="int" required="yes"/>
      <property name="ownerlogin" fieldname="ownerlogin" datatype="string" />
    </record>
    <factory>
        <method name="method1" type="select">
            <conditions>
               <eq property="grouptype" value="1" />
            </conditions>
        </method>

        <method name="method2" type="select">
           <conditions>
              <neq property="grouptype" value="2" />
           </conditions>
           <order>
               <orderitem property="name" way="asc" />
           </order>
        </method>

        <method name="method3" type="select">
           <parameter name="login" />
           <conditions>
              <eq property="grouptype" value="2" />
              <eq property="ownerlogin" expr="$login" />
           </conditions>
        </method>

        <method name="method4" type="select">
           <parameter name="parent" />
           <parameter name="group" />
           <conditions>
              <eq property="grouptype" expr="$group" />
              <eq property="parent_id" expr="$parent" />
           </conditions>
        </method>
         <method name="method5" type="select">
           <parameter name="parent" />
           <parameter name="group" />
           <conditions>
              <eq property="grouptype" expr="$group" />
              <conditions logic="or">
                <eq property="parent_id" expr="$parent" />
                <eq property="id_aclgrp" expr="$parent" />
              </conditions>
           </conditions>
        </method>
        <method name="method6" type="select">
           <conditions>
              <in property="grouptype" value="1,2,3" />
              <isnull property="parent_id" />
           </conditions>
        </method>
        <method name="method7" type="select">
           <parameter name="parent" />
           <parameter name="group" />
           <conditions>
              <in property="grouptype" expr="$group" />
              <lt property="parent_id" expr="$parent" />
           </conditions>
        </method>
        <method name="method8" type="select">
           <parameter name="login" />
           <conditions>
              <eq property="grouptype" value="2" />
              <eq property="ownerlogin" expr="TOUPPER($login)" />
           </conditions>
        </method>
        <method name="method9" type="select">
           <parameter name="login" />
           <conditions>
              <eq property="grouptype" value="2" />
              <eq property="name" expr="TOUPPER($login)" />
           </conditions>
        </method>
        <method name="method10" type="select">
           <parameter name="login" />
           <conditions>
              <like property="grouptype" value="2%" />
              <like property="name" value="a%" />
              <like property="ownerlogin" expr="$login" />
           </conditions>
        </method>
        <method name="method11" type="select">
           <conditions>
              <neq property="name" value="toto" />
           </conditions>
        </method>
        <method name="method12" type="select">
           <parameter name="login" />
           <conditions>
              <like property="ownerlogin" expr="concat($login,\'%\')" />
           </conditions>
        </method>
        <method name="method14" type="select">
           <conditions>
              <in property="name" value="\'foo\',\'bar\',\'baz\'" />
           </conditions>
        </method>
        <method name="method15" type="select">
           <parameter name="login" />
           <conditions>
              <eq property="grouptype" value="2" />
              <eq property="name" pattern="TOUPPER(%s)" expr="TOUPPER($login)" />
           </conditions>
        </method>
    </factory>
</dao>';
        $parser = new jDaoParser ($this->_selector);
        $parser->parse(simplexml_load_string($doc), $this->_tools);
        $generator = new testMysqlDaoGenerator($this->_selector, $this->_tools, $parser);

        $methods = $parser->getMethods();

        $where = $generator->BuildSQLCondition ($methods['method1']->getConditions()->condition, $parser->getProperties(),
                                                $methods['method1']->getParameters(), false);
        $this->assertEquals(' `grouptype` = 1',$where);

        $where = $generator->BuildSQLCondition ($methods['method2']->getConditions()->condition, $parser->getProperties(),
                                                $methods['method2']->getParameters(), false);
        $this->assertEquals(' `grouptype` <> 2',$where);

        $where = $generator->BuildSQLCondition ($methods['method3']->getConditions()->condition, $parser->getProperties(),
                                                $methods['method3']->getParameters(), false);
        $this->assertEquals(' `grouptype` = 2 AND `ownerlogin` \'.($login === null ? \'IS NULL\' : \' = \'.$this->_conn->quote2($login,false)).\'',$where);

        $where = $generator->BuildSQLCondition ($methods['method4']->getConditions()->condition, $parser->getProperties(),
                                                $methods['method4']->getParameters(), false);
        $this->assertEquals(' `grouptype` \'.\' = \'.intval($group).\' AND `parent_id` \'.($parent === null ? \'IS NULL\' : \' = \'.intval($parent)).\'',$where);

        $where = $generator->BuildSQLCondition ($methods['method5']->getConditions()->condition, $parser->getProperties(),
                                                $methods['method5']->getParameters(), false);
        $this->assertEquals(' `grouptype` \'.\' = \'.intval($group).\' AND ( `parent_id` \'.($parent === null ? \'IS NULL\' : \' = \'.intval($parent)).\' OR `id_aclgrp` \'.\' = \'.intval($parent).\')',$where);

        $where = $generator->BuildSQLCondition ($methods['method6']->getConditions()->condition, $parser->getProperties(),
                                                $methods['method6']->getParameters(), false);
        $this->assertEquals(' `grouptype` IN (1,2,3) AND `parent_id` IS NULL ',$where);

        $where = $generator->BuildSQLCondition ($methods['method7']->getConditions()->condition, $parser->getProperties(),
                                                $methods['method7']->getParameters(), false);
        $this->assertEquals(' `grouptype` IN (\'.implode(\',\', array_map( function($__e){return intval($__e);}, $group)).\') AND `parent_id` \'.\' < \'.intval($parent).\'',$where);

        $where = $generator->BuildSQLCondition ($methods['method8']->getConditions()->condition, $parser->getProperties(),
                                                $methods['method8']->getParameters(), false);
        $this->assertEquals(' `grouptype` = 2 AND `ownerlogin` = TOUPPER(\'.($login === null ? \'NULL\' : $this->_conn->quote2($login,false)).\')',$where);

        $where = $generator->BuildSQLCondition ($methods['method9']->getConditions()->condition, $parser->getProperties(),
                                                $methods['method9']->getParameters(), false);
        $this->assertEquals(' `grouptype` = 2 AND `name` = TOUPPER(\'.$this->_conn->quote($login).\')',$where);

        $where = $generator->BuildSQLCondition ($methods['method10']->getConditions()->condition, $parser->getProperties(),
                                                $methods['method10']->getParameters(), false);
        $this->assertEquals(' `grouptype` LIKE \\\'2%\\\' AND `name` LIKE \\\'a%\\\' AND `ownerlogin` \'.\' LIKE \'.$this->_conn->quote($login).\'',$where);

        $where = $generator->BuildSQLCondition ($methods['method11']->getConditions()->condition, $parser->getProperties(),
                                                $methods['method11']->getParameters(), false);
        $this->assertEquals(' `name` <> \\\'toto\\\'',$where);

        $where = $generator->BuildSQLCondition ($methods['method12']->getConditions()->condition, $parser->getProperties(),
                                                $methods['method12']->getParameters(), false);
        $this->assertEquals(' `ownerlogin` LIKE concat(\'.($login === null ? \'NULL\' : $this->_conn->quote2($login,false)).\',\\\'%\\\')',$where);

        // with prefix
        $where = $generator->BuildSQLCondition ($methods['method1']->getConditions()->condition, $parser->getProperties(),
                                                $methods['method1']->getParameters(), true);
        $this->assertEquals(' `grp`.`grouptype` = 1',$where);

        $where = $generator->BuildSQLCondition ($methods['method2']->getConditions()->condition, $parser->getProperties(),
                                                $methods['method2']->getParameters(), true);
        $this->assertEquals(' `grp`.`grouptype` <> 2',$where);

        $where = $generator->BuildSQLCondition ($methods['method3']->getConditions()->condition, $parser->getProperties(),
                                                $methods['method3']->getParameters(), true);
        $this->assertEquals(' `grp`.`grouptype` = 2 AND `grp`.`ownerlogin` \'.($login === null ? \'IS NULL\' : \' = \'.$this->_conn->quote2($login,false)).\'',$where);

        $where = $generator->BuildSQLCondition ($methods['method4']->getConditions()->condition, $parser->getProperties(),
                                                $methods['method4']->getParameters(), true);
        $this->assertEquals(' `grp`.`grouptype` \'.\' = \'.intval($group).\' AND `grp`.`parent_id` \'.($parent === null ? \'IS NULL\' : \' = \'.intval($parent)).\'',$where);

        $where = $generator->BuildSQLCondition ($methods['method5']->getConditions()->condition, $parser->getProperties(),
                                                $methods['method5']->getParameters(), true);
        $this->assertEquals(' `grp`.`grouptype` \'.\' = \'.intval($group).\' AND ( `grp`.`parent_id` \'.($parent === null ? \'IS NULL\' : \' = \'.intval($parent)).\' OR `grp`.`id_aclgrp` \'.\' = \'.intval($parent).\')',$where);

        $where = $generator->BuildSQLCondition ($methods['method6']->getConditions()->condition, $parser->getProperties(),
                                                $methods['method6']->getParameters(), true);
        $this->assertEquals(' `grp`.`grouptype` IN (1,2,3) AND `grp`.`parent_id` IS NULL ',$where);

        $where = $generator->BuildSQLCondition ($methods['method7']->getConditions()->condition, $parser->getProperties(),
                                                $methods['method7']->getParameters(), true);
        $this->assertEquals(' `grp`.`grouptype` IN (\'.implode(\',\', array_map( function($__e){return intval($__e);}, $group)).\') AND `grp`.`parent_id` \'.\' < \'.intval($parent).\'',$where);

        $where = $generator->BuildSQLCondition ($methods['method8']->getConditions()->condition, $parser->getProperties(),
                                                $methods['method8']->getParameters(), true);
        $this->assertEquals(' `grp`.`grouptype` = 2 AND `grp`.`ownerlogin` = TOUPPER(\'.($login === null ? \'NULL\' : $this->_conn->quote2($login,false)).\')',$where);

        $where = $generator->BuildSQLCondition ($methods['method9']->getConditions()->condition, $parser->getProperties(),
                                                $methods['method9']->getParameters(), true);
        $this->assertEquals(' `grp`.`grouptype` = 2 AND `grp`.`name` = TOUPPER(\'.$this->_conn->quote($login).\')',$where);

        $where = $generator->BuildSQLCondition ($methods['method11']->getConditions()->condition, $parser->getProperties(),
                                                $methods['method11']->getParameters(), true);
        $this->assertEquals(' `grp`.`name` <> \\\'toto\\\'',$where);

        $where = $generator->BuildSQLCondition ($methods['method14']->getConditions()->condition, $parser->getProperties(),
                                                $methods['method14']->getParameters(), true);
        $this->assertEquals(' `grp`.`name` IN (\\\'foo\\\',\\\'bar\\\',\\\'baz\\\')',$where);

        $where = $generator->BuildSQLCondition ($methods['method15']->getConditions()->condition, $parser->getProperties(),
                                                $methods['method15']->getParameters(), true);
        $this->assertEquals(' `grp`.`grouptype` = 2 AND TOUPPER(`grp`.`name`) = TOUPPER(\'.$this->_conn->quote($login).\')',$where);
    }

    function testBuildSQLConditionWithPattern(){
        $doc ='<?xml version="1.0" encoding="UTF-8"?>
<dao xmlns="http://jelix.org/ns/dao/1.0">
    <datasources>
        <primarytable name="grp" realname="jacl_group" primarykey="id_aclgrp" />
    </datasources>
    <record>
      <property name="id_aclgrp" fieldname="id_aclgrp" datatype="autoincrement" required="yes"/>
      <property name="parent_id" required="false" datatype="int" />
      <property name="name" fieldname="name" datatype="string" required="yes" selectpattern="TOUPPER(%s)"/>
      <property name="grouptype" fieldname="grouptype" datatype="int" required="yes"/>
      <property name="ownerlogin" fieldname="ownerlogin" datatype="string" />
    </record>
    <factory>
        <method name="method1" type="select">
            <conditions>
               <eq property="name" value="toto" />
            </conditions>
        </method>

        <method name="method2" type="select">
           <conditions>
              <neq property="name" value="toto" />
           </conditions>
           <order>
               <orderitem property="name" way="asc" />
           </order>
        </method>

        <method name="method3" type="select">
           <parameter name="login" />
           <conditions>
              <eq property="grouptype" value="2" />
              <eq property="name" expr="$login" />
           </conditions>
        </method>

        <method name="method9" type="select">
           <parameter name="login" />
           <conditions>
              <eq property="grouptype" value="2" />
              <eq property="name" expr="TOUPPER($login)" />
              <like property="grouptype" expr="$login" />
           </conditions>
        </method>

        <method name="method15" type="select">
           <parameter name="login" />
           <conditions>
              <eq property="grouptype" value="2" />
              <eq property="name" pattern="TOUPPER(%s)" expr="TOUPPER($login)" />
              <like property="grouptype" expr="$login" />
           </conditions>
        </method>
    </factory>
</dao>';
        $parser = new jDaoParser ($this->_selector);
        $parser->parse(simplexml_load_string($doc), $this->_tools);
        $generator = new testMysqlDaoGenerator($this->_selector, $this->_tools, $parser);

        $methods=$parser->getMethods();

        $where = $generator->BuildSQLCondition ($methods['method1']->getConditions()->condition, $parser->getProperties(),
                                                $methods['method1']->getParameters(), false);
        $this->assertEquals(' `name` = \\\'toto\\\'',$where);

        $where = $generator->BuildSQLCondition ($methods['method2']->getConditions()->condition, $parser->getProperties(),
                                                $methods['method2']->getParameters(), false);
        $this->assertEquals(' `name` <> \\\'toto\\\'',$where);

        $where = $generator->BuildSQLCondition ($methods['method3']->getConditions()->condition, $parser->getProperties(),
                                                $methods['method3']->getParameters(), false);
        $this->assertEquals(' `grouptype` = 2 AND `name` \'.\' = \'.$this->_conn->quote($login).\'',$where);

        // with prefix
        $where = $generator->BuildSQLCondition ($methods['method1']->getConditions()->condition, $parser->getProperties(),
                                                $methods['method1']->getParameters(), true);
        $this->assertEquals(' `grp`.`name` = \\\'toto\\\'',$where);

        $where = $generator->BuildSQLCondition ($methods['method2']->getConditions()->condition, $parser->getProperties(),
                                                $methods['method2']->getParameters(), true);
        $this->assertEquals(' `grp`.`name` <> \\\'toto\\\'',$where);

        $where = $generator->BuildSQLCondition ($methods['method3']->getConditions()->condition, $parser->getProperties(),
                                                $methods['method3']->getParameters(), true);
        $this->assertEquals(' `grp`.`grouptype` = 2 AND `grp`.`name` \'.\' = \'.$this->_conn->quote($login).\'',$where);

        $where = $generator->BuildSQLCondition ($methods['method9']->getConditions()->condition, $parser->getProperties(),
                                                $methods['method9']->getParameters(), false);
        $this->assertEquals(' `grouptype` = 2 AND `name` = TOUPPER(\'.$this->_conn->quote($login).\') AND `grouptype` \'.\' LIKE \'.$this->_conn->quote($login).\'',$where);

        $where = $generator->BuildSQLCondition ($methods['method15']->getConditions()->condition, $parser->getProperties(),
                                                $methods['method15']->getParameters(), false);
        $this->assertEquals(' `grouptype` = 2 AND TOUPPER(`name`) = TOUPPER(\'.$this->_conn->quote($login).\') AND `grouptype` \'.\' LIKE \'.$this->_conn->quote($login).\'',$where);

    }



    function testBuildSimpleCondition(){
        $doc ='<?xml version="1.0" encoding="UTF-8"?>
<dao xmlns="http://jelix.org/ns/dao/1.0">
    <datasources>
        <primarytable name="grp" realname="jacl_group" primarykey="id_aclgrp" />
    </datasources>
    <record>
      <property name="id_aclgrp" fieldname="id_aclgrp" datatype="autoincrement" required="yes"/>
      <property name="parent_id" required="false" datatype="int" />
      <property name="name" fieldname="name" datatype="string" required="yes"/>
      <property name="grouptype" fieldname="grouptype" datatype="int" required="yes"/>
      <property name="ownerlogin" fieldname="ownerlogin" datatype="string" />
    </record>
</dao>';
        $parser = new jDaoParser ($this->_selector);
        $parser->parse(simplexml_load_string($doc), $this->_tools);
        $generator = new testMysqlDaoGenerator($this->_selector, $this->_tools, $parser);

        $pkFields=$generator->GetPkFields();
        $this->assertEquals(1, count($pkFields));
        $this->assertTrue(isset($pkFields['id_aclgrp']));

        $where = $generator->BuildSimpleConditions2 ($pkFields);
        $this->assertEquals(' `grp`.`id_aclgrp`\'.\' = \'.intval($id_aclgrp).\'',$where);

        $where = $generator->BuildSimpleConditions2 ($pkFields, 'record->');
        $this->assertEquals(' `grp`.`id_aclgrp`\'.\' = \'.intval($record->id_aclgrp).\'',$where);

        $where = $generator->BuildSimpleConditions2 ($pkFields, 'record->', false);
        $this->assertEquals(' `id_aclgrp`\'.\' = \'.intval($record->id_aclgrp).\'',$where);
    }


 function testBuildConditions(){
        $doc ='<?xml version="1.0" encoding="UTF-8"?>
<dao xmlns="http://jelix.org/ns/dao/1.0">
    <datasources>
        <primarytable name="grp" realname="jacl_group" primarykey="id_aclgrp" />
    </datasources>
    <record>
      <property name="id_aclgrp" fieldname="id_aclgrp" datatype="autoincrement" required="yes"/>
      <property name="parent_id" required="false" datatype="int" />
      <property name="name" fieldname="name" datatype="string" required="yes"/>
      <property name="grouptype" fieldname="grouptype" datatype="int" required="yes"/>
      <property name="ownerlogin" fieldname="ownerlogin" datatype="string" />
    </record>
    <factory>
        <method name="method1" type="select">
            <conditions>
               <eq property="grouptype" value="1" />
            </conditions>
        </method>
        <method name="method2" type="select" groupby="id_aclgrp,parent_id,name">
           <conditions>
              <neq property="grouptype" value="2" />
           </conditions>
           <order>
               <orderitem property="name" way="asc" />
           </order>
        </method>
        <method name="method3" type="select">
           <order>
               <orderitem property="name" way="asc" />
           </order>
        </method>
        <method name="method4" type="select" groupby="id_aclgrp,parent_id,name">
           <order>
               <orderitem property="name" way="asc" />
           </order>
        </method>
    </factory>
</dao>';
        $parser = new jDaoParser ($this->_selector);
        $parser->parse(simplexml_load_string($doc), $this->_tools);
        $generator = new testMysqlDaoGenerator($this->_selector, $this->_tools, $parser);

        $methods = $parser->getMethods();

        $this->assertNotNull($methods['method1']->getConditions());
        $sql = $generator->BuildConditions2 ($methods['method1']->getConditions(), $parser->getProperties(),
                                                $methods['method1']->getParameters(), false,  $methods['method1']->getGroupBy());
        $this->assertEquals(' `grouptype` = 1', $sql);

        $this->assertNotNull($methods['method2']->getConditions());
        $sql = $generator->BuildConditions2 ($methods['method2']->getConditions(), $parser->getProperties(),
                                                $methods['method2']->getParameters(), false, $methods['method2']->getGroupBy());
        $this->assertEquals(' `grouptype` <> 2 GROUP BY `id_aclgrp`, `parent_id`, `name` ORDER BY `name` asc', $sql);

        $this->assertNotNull($methods['method3']->getConditions());
        $sql = $generator->BuildConditions2 ($methods['method3']->getConditions(), $parser->getProperties(),
                                                $methods['method3']->getParameters(), false, $methods['method3']->getGroupBy());
        $this->assertEquals(' 1=1  ORDER BY `name` asc',$sql);

        $this->assertNotNull($methods['method4']->getConditions());
        $sql = $generator->BuildConditions2 ($methods['method4']->getConditions(), $parser->getProperties(),
                                                $methods['method4']->getParameters(), false, $methods['method4']->getGroupBy());
        $this->assertEquals(' 1=1  GROUP BY `id_aclgrp`, `parent_id`, `name` ORDER BY `name` asc', $sql);

        $sql = $generator->BuildConditions2 ($methods['method1']->getConditions(), $parser->getProperties(),
                                                $methods['method1']->getParameters(), true,  $methods['method1']->getGroupBy());
        $this->assertEquals(' `grp`.`grouptype` = 1', $sql);

        $sql = $generator->BuildConditions2 ($methods['method2']->getConditions(), $parser->getProperties(),
                                                $methods['method2']->getParameters(), true, $methods['method2']->getGroupBy());
        $this->assertEquals(' `grp`.`grouptype` <> 2 GROUP BY `grp`.`id_aclgrp`, `grp`.`parent_id`, `grp`.`name` ORDER BY `grp`.`name` asc',$sql);

        $sql = $generator->BuildConditions2 ($methods['method3']->getConditions(), $parser->getProperties(),
                                                $methods['method3']->getParameters(), true, $methods['method3']->getGroupBy());
        $this->assertEquals(' 1=1  ORDER BY `grp`.`name` asc',$sql);

        $sql = $generator->BuildConditions2 ($methods['method4']->getConditions(), $parser->getProperties(),
                                                $methods['method4']->getParameters(), true, $methods['method4']->getGroupBy());
        $this->assertEquals(' 1=1  GROUP BY `grp`.`id_aclgrp`, `grp`.`parent_id`, `grp`.`name` ORDER BY `grp`.`name` asc',$sql);

    }

 function testBuildConditionsNoAlias(){
        $doc ='<?xml version="1.0" encoding="UTF-8"?>
<dao xmlns="http://jelix.org/ns/dao/1.0">
    <datasources>
        <primarytable name="jacl_group" primarykey="id_aclgrp" />
    </datasources>
    <record>
      <property name="id_aclgrp" fieldname="id_aclgrp" datatype="autoincrement" required="yes"/>
      <property name="parent_id" required="false" datatype="int" />
      <property name="name" fieldname="name" datatype="string" required="yes"/>
      <property name="grouptype" fieldname="grouptype" datatype="int" required="yes"/>
      <property name="ownerlogin" fieldname="ownerlogin" datatype="string" />
    </record>
    <factory>
        <method name="method1" type="select">
            <conditions>
               <eq property="grouptype" value="1" />
            </conditions>
        </method>
        <method name="method2" type="select" groupby="id_aclgrp,parent_id,name">
           <conditions>
              <neq property="grouptype" value="2" />
           </conditions>
           <order>
               <orderitem property="name" way="asc" />
           </order>
        </method>
        <method name="method3" type="select">
           <order>
               <orderitem property="name" way="asc" />
           </order>
        </method>
        <method name="method4" type="select" groupby="id_aclgrp,parent_id,name">
           <order>
               <orderitem property="name" way="asc" />
           </order>
        </method>
    </factory>
</dao>';
        $parser = new jDaoParser ($this->_selector);
        $parser->parse(simplexml_load_string($doc), $this->_tools);
        $generator = new testMysqlDaoGenerator($this->_selector, $this->_tools, $parser);

        $methods = $parser->getMethods();

        $this->assertNotNull($methods['method1']->getConditions());
        $sql = $generator->BuildConditions2 ($methods['method1']->getConditions(), $parser->getProperties(),
                                                $methods['method1']->getParameters(), false,  $methods['method1']->getGroupBy());
        $this->assertEquals(' `grouptype` = 1', $sql);

        $this->assertNotNull($methods['method2']->getConditions());
        $sql = $generator->BuildConditions2 ($methods['method2']->getConditions(), $parser->getProperties(),
                                                $methods['method2']->getParameters(), false, $methods['method2']->getGroupBy());
        $this->assertEquals(' `grouptype` <> 2 GROUP BY `id_aclgrp`, `parent_id`, `name` ORDER BY `name` asc', $sql);

        $this->assertNotNull($methods['method3']->getConditions());
        $sql = $generator->BuildConditions2 ($methods['method3']->getConditions(), $parser->getProperties(),
                                                $methods['method3']->getParameters(), false, $methods['method3']->getGroupBy());
        $this->assertEquals(' 1=1  ORDER BY `name` asc',$sql);

        $this->assertNotNull($methods['method4']->getConditions());
        $sql = $generator->BuildConditions2 ($methods['method4']->getConditions(), $parser->getProperties(),
                                                $methods['method4']->getParameters(), false, $methods['method4']->getGroupBy());
        $this->assertEquals(' 1=1  GROUP BY `id_aclgrp`, `parent_id`, `name` ORDER BY `name` asc', $sql);

        $sql = $generator->BuildConditions2 ($methods['method1']->getConditions(), $parser->getProperties(),
                                                $methods['method1']->getParameters(), true,  $methods['method1']->getGroupBy());
        $this->assertEquals(' `jacl_group`.`grouptype` = 1', $sql);

        $sql = $generator->BuildConditions2 ($methods['method2']->getConditions(), $parser->getProperties(),
                                                $methods['method2']->getParameters(), true, $methods['method2']->getGroupBy());
        $this->assertEquals(' `jacl_group`.`grouptype` <> 2 GROUP BY `jacl_group`.`id_aclgrp`, `jacl_group`.`parent_id`, `jacl_group`.`name` ORDER BY `jacl_group`.`name` asc',$sql);

        $sql = $generator->BuildConditions2 ($methods['method3']->getConditions(), $parser->getProperties(),
                                                $methods['method3']->getParameters(), true, $methods['method3']->getGroupBy());
        $this->assertEquals(' 1=1  ORDER BY `jacl_group`.`name` asc',$sql);

        $sql = $generator->BuildConditions2 ($methods['method4']->getConditions(), $parser->getProperties(),
                                                $methods['method4']->getParameters(), true, $methods['method4']->getGroupBy());
        $this->assertEquals(' 1=1  GROUP BY `jacl_group`.`id_aclgrp`, `jacl_group`.`parent_id`, `jacl_group`.`name` ORDER BY `jacl_group`.`name` asc',$sql);

    }


    function testInsertQuery() {
        
        $doc ='<?xml version="1.0"?>
<dao xmlns="http://jelix.org/ns/dao/1.0">
   <datasources>
      <primarytable name="product_test" primarykey="code" />
   </datasources>
   <record>
      <property name="code" fieldname="code" datatype="string" insertpattern="now()"/>
      <property name="name" fieldname="name" datatype="string" />
      <property name="price" fieldname="price" datatype="float"/>
   </record>
</dao>';
        $parser = new jDaoParser ($this->_selector);
        $parser->parse(simplexml_load_string($doc), $this->_tools);

        $generator= new testMysqlDaoGenerator($this->_selector, $this->_tools, $parser);

        $fieldList = $generator->GetPropertiesBy('PrimaryTable');
        list($fields, $values) = $generator->PrepareValues($fieldList,'insertPattern', 'record->');
        $this->assertEquals("now()", $values['code']);
        
        $doc ='<?xml version="1.0"?>
<dao xmlns="http://jelix.org/ns/dao/1.0">
   <datasources>
      <primarytable name="product_test" primarykey="code" />
   </datasources>
   <record>
      <property name="code" fieldname="code" datatype="string" insertpattern="now(%s)"/>
      <property name="name" fieldname="name" datatype="string" />
      <property name="price" fieldname="price" datatype="float"/>
   </record>
</dao>';
        $parser = new jDaoParser ($this->_selector);
        $parser->parse(simplexml_load_string($doc), $this->_tools);

        $generator= new testMysqlDaoGenerator($this->_selector, $this->_tools, $parser);

        $fieldList = $generator->GetPropertiesBy('PrimaryTable');
        list($fields, $values) = $generator->PrepareValues($fieldList,'insertPattern', 'record->');
        $this->assertEquals('now(\'.($record->code === null ? \'NULL\' : $this->_conn->quote2($record->code,false)).\')', $values['code']);
        
    }

    function testUpdateQuery() {
        
        $doc ='<?xml version="1.0"?>
<dao xmlns="http://jelix.org/ns/dao/1.0">
   <datasources>
      <primarytable name="product_test" primarykey="code" />
   </datasources>
   <record>
      <property name="code" fieldname="code" datatype="string"/>
      <property name="name" fieldname="name" datatype="string" />
      <property name="price" fieldname="price" datatype="float"/>
      <property name="price_big" fieldname="price_big" datatype="float"/>
   </record>
   <factory>
       <method name="test" type="update">
            <parameter name="price" />
            <parameter name="price_big" />
            <values>
                 <value property="price"     expr="$price"     />
                 <value property="price_big" expr="$price_big" />
            </values>
       </method>
   </factory>
</dao>';
        $parser = new jDaoParser ($this->_selector);
        $parser->parse(simplexml_load_string($doc), $this->_tools);

        $generator= new testMysqlDaoGenerator($this->_selector, $this->_tools, $parser);
        $primaryFields = $generator->GetPropertiesBy('PrimaryTable');
        $methods = $parser->getMethods();
        $src = array();
        $generator->GetBuildUpdateUserQuery($methods['test'], $src, $primaryFields);
        
        $this->assertEquals('    $__query = \'UPDATE  SET '."\n".' `price`= \'.($price === null ? \'NULL\' : jDb::floatToStr($price)).\', `price_big`= \'.($price_big === null ? \'NULL\' : jDb::floatToStr($price_big)).\'\';', implode("\n",$src));
    }

}
