<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Laurent Jouanneau
* @contributor
* @copyright   2006-2009 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

require_once(__DIR__.'/daotests.lib.php');


class jdao_parser_updateTest extends jUnitTestCase {

    protected $_selector;
    function setUp() {
        $this->_selector = new fakejSelectorDao("foo", "bar", "mysql");
    }

    function tearDown() {
        $this->_selector = null;
    }

    protected $methDatas=array(
        array('<?xml version="1.0"?>
          <method name="foo" type="update">
                <values>
                    <value property="subject" expr="\'abc\'" />
                </values>
          </method>',
        '<?xml version="1.0"?>
        <object>
            <string p="name" value="foo"/>
            <string p="type" value="update"/>
            <boolean p="distinct" value="false"/>
            <boolean p="eventBeforeEnabled" value="false"/>
            <boolean p="eventAfterEnabled" value="false"/>
            <object m="getConditions()" class="jDaoConditions">
                <object p="condition" class="jDaoCondition">
                    <null p="parent" />
                    <array p="conditions">array()</array>
                    <array p="group">array()</array>
                </object>
                <array p="order">array()</array>
            </object>
            <array m="getParameters ()">array()</array>
            <array m="getParametersDefaultValues ()">array()</array>
            <null m="getLimit ()"/>
            <array m="getValues ()">array(\'subject\'=>array(\'\\\'abc\\\'\',true))</array>
            <null m="getProcStock ()"/>
            <null m="getBody ()"/>
        </object>'),
        array('<?xml version="1.0"?>
          <method name="foo" type="update">
            <parameter name="mytext" />
                <values>
                    <value property="subject" expr="$mytext" />
                </values>
          </method>',
        '<?xml version="1.0"?>
        <object>
            <string p="name" value="foo"/>
            <string p="type" value="update"/>
            <boolean p="distinct" value="false"/>
            <boolean p="eventBeforeEnabled" value="false"/>
            <boolean p="eventAfterEnabled" value="false"/>
            <object m="getConditions()" class="jDaoConditions">
                <object p="condition" class="jDaoCondition">
                    <null p="parent" />
                    <array p="conditions">array()</array>
                    <array p="group">array()</array>
                </object>
                <array p="order">array()</array>
            </object>
            <array m="getParameters ()">array("mytext")</array>
            <array m="getParametersDefaultValues ()">array()</array>
            <null m="getLimit ()"/>
            <array m="getValues ()">array(\'subject\'=>array(\'$mytext\',true))</array>
            <null m="getProcStock ()"/>
            <null m="getBody ()"/>
        </object>'),
        array('<?xml version="1.0"?>
          <method name="foo" type="update">
                <values>
                    <value property="subject" value="my text" />
                </values>
          </method>',
        '<?xml version="1.0"?>
        <object>
            <string p="name" value="foo"/>
            <string p="type" value="update"/>
            <boolean p="distinct" value="false"/>
            <boolean p="eventBeforeEnabled" value="false"/>
            <boolean p="eventAfterEnabled" value="false"/>
            <object m="getConditions()" class="jDaoConditions">
                <object p="condition" class="jDaoCondition">
                    <null p="parent" />
                    <array p="conditions">array()</array>
                    <array p="group">array()</array>
                </object>
                <array p="order">array()</array>
            </object>
            <array m="getParameters ()">array()</array>
            <array m="getParametersDefaultValues ()">array()</array>
            <null m="getLimit ()"/>
            <array m="getValues ()">array(\'subject\'=>array(\'my text\',false))</array>
            <null m="getProcStock ()"/>
            <null m="getBody ()"/>
        </object>'),
       
        array('<?xml version="1.0"?>
          <method name="foo" type="update">
            <values>
                <value property="subject" value="my text" />
            </values>
            <conditions>
                <eq property="subject" value="bar" />
                <eq property="texte" value="machine" />
            </conditions>
          </method>',
        '<?xml version="1.0"?>
        <object>
            <string p="name" value="foo"/>
            <string p="type" value="update"/>
            <boolean p="distinct" value="false"/>
            <boolean p="eventBeforeEnabled" value="false"/>
            <boolean p="eventAfterEnabled" value="false"/>
            <object m="getConditions()" class="jDaoConditions">
                <object p="condition" class="jDaoCondition">
                    <null p="parent" />
                    <array p="conditions">array(
                     array("field_id"=>"subject","field_pattern"=>"","value"=>"bar", "operator"=>"=", "isExpr"=>false),
                     array("field_id"=>"texte","field_pattern"=>"","value"=>"machine", "operator"=>"=", "isExpr"=>false))</array>
                    <array p="group">array()</array>
                    <string p="glueOp" value="AND"/>
                </object>
                <array p="order">array()</array>
            </object>
            <array m="getParameters ()">array()</array>
            <array m="getParametersDefaultValues ()">array()</array>
            <null m="getLimit ()"/>
            <array m="getValues ()">array(\'subject\'=>array(\'my text\',false))</array>
            <null m="getProcStock ()"/>
            <null m="getBody ()"/>
        </object>'),
       
        array('<?xml version="1.0"?>
          <method name="foo" type="update">
            <values>
                <value property="subject" value="my text" />
            </values>
            <conditions>
                <eq property="subject" pattern="CONCAT(%s, \'b\')" value="bar" />
                <eq property="texte" pattern="LOWER(%s)" value="machine" />
            </conditions>
          </method>',
        '<?xml version="1.0"?>
        <object>
            <string p="name" value="foo"/>
            <string p="type" value="update"/>
            <boolean p="distinct" value="false"/>
            <boolean p="eventBeforeEnabled" value="false"/>
            <boolean p="eventAfterEnabled" value="false"/>
            <object m="getConditions()" class="jDaoConditions">
                <object p="condition" class="jDaoCondition">
                    <null p="parent" />
                    <array p="conditions">array(
                     array("field_id"=>"subject","field_pattern"=>"CONCAT(%s, \'b\')","value"=>"bar", "operator"=>"=", "isExpr"=>false),
                     array("field_id"=>"texte","field_pattern"=>"LOWER(%s)","value"=>"machine", "operator"=>"=", "isExpr"=>false))</array>
                    <array p="group">array()</array>
                    <string p="glueOp" value="AND"/>
                </object>
                <array p="order">array()</array>
            </object>
            <array m="getParameters ()">array()</array>
            <array m="getParametersDefaultValues ()">array()</array>
            <null m="getLimit ()"/>
            <array m="getValues ()">array(\'subject\'=>array(\'my text\',false))</array>
            <null m="getProcStock ()"/>
            <null m="getBody ()"/>
        </object>'),

        array('<?xml version="1.0"?>
          <method name="foo" type="update" eventbefore="true">
            <values>
                <value property="subject" value="my text" />
            </values>
          </method>',
        '<?xml version="1.0"?>
        <object>
            <string p="name" value="foo"/>
            <string p="type" value="update"/>
            <boolean p="distinct" value="false"/>
            <boolean p="eventBeforeEnabled" value="true"/>
            <boolean p="eventAfterEnabled" value="false"/>
            <object m="getConditions()" class="jDaoConditions">
                <object p="condition" class="jDaoCondition">
                    <null p="parent" />
                    <array p="conditions">array()</array>
                    <array p="group">array()</array>
                </object>
                <array p="order">array()</array>
            </object>
            <array m="getParameters ()">array()</array>
            <array m="getParametersDefaultValues ()">array()</array>
            <null m="getLimit ()"/>
            <array m="getValues ()">array(\'subject\'=>array(\'my text\',false))</array>
            <null m="getProcStock ()"/>
            <null m="getBody ()"/>
        </object>'),
        
        array('<?xml version="1.0"?>
          <method name="foo" type="update" eventafter="true">
            <values>
                <value property="subject" value="my text" />
            </values>
          </method>',
        '<?xml version="1.0"?>
        <object>
            <string p="name" value="foo"/>
            <string p="type" value="update"/>
            <boolean p="distinct" value="false"/>
            <boolean p="eventBeforeEnabled" value="false"/>
            <boolean p="eventAfterEnabled" value="true"/>
            <object m="getConditions()" class="jDaoConditions">
                <object p="condition" class="jDaoCondition">
                    <null p="parent" />
                    <array p="conditions">array()</array>
                    <array p="group">array()</array>
                </object>
                <array p="order">array()</array>
            </object>
            <array m="getParameters ()">array()</array>
            <array m="getParametersDefaultValues ()">array()</array>
            <null m="getLimit ()"/>
            <array m="getValues ()">array(\'subject\'=>array(\'my text\',false))</array>
            <null m="getProcStock ()"/>
            <null m="getBody ()"/>
        </object>'),
    );

    function testMethods() {
        $dao ='<?xml version="1.0"?>
<dao xmlns="http://jelix.org/ns/dao/1.0">
  <datasources>
    <primarytable name="news" primarykey="news_id" />
    <foreigntable name="news_author" primarykey="author_id" onforeignkey="author_id" />
  </datasources>
  <record>
    <property name="id" fieldname="news_id" datatype="autoincrement" />
    <property name="subject" datatype="string" />
    <property name="texte" datatype="string" />
    <property name="publishdate" datatype="date" />
    <property name="author_firstname" fieldname="firstname" datatype="string" table="news_author" />
    <property name="author_lastname" fieldname="lastname"  datatype="string" table="news_author" />
  </record>
</dao>';

        $parser = new testjDaoParser($this->_selector);
        $xml = simplexml_load_string($dao);
        $tools = new mysqlDbTools(null);
        $parser->testParseDatasource($xml);
        $parser->testParseRecord($xml,$tools);

        foreach($this->methDatas as $k=>$t){
            //$this->sendMessage("test good method ".$k);
            $xml= simplexml_load_string($t[0]);
            try{
                $p = new jDaoMethod($xml, $parser);
                $this->assertComplexIdenticalStr($p, $t[1]);
            }catch(jDaoXmlException $e){
                $this->fail("Exception sur le contenu xml inattendue : ".$e->getMessage());
            }/*catch(Exception $e){
                $this->fail("Exception inconnue : ".$e->getMessage());
            }*/
        }
    }

    function testMethods2() {
        $dao ='<?xml version="1.0"?>
<dao xmlns="http://jelix.org/ns/dao/1.0">
  <datasources>
    <primarytable name="product" primarykey="product_id" />
  </datasources>
  <record>
    <property name="id" fieldname="product_id" datatype="autoincrement" />
    <property name="price" datatype="float" />
    <property name="price_big" datatype="float" />
  </record>
</dao>';

        $parser = new testjDaoParser($this->_selector);
        $xml = simplexml_load_string($dao);
        $tools = new mysqlDbTools(null);
        $parser->testParseDatasource($xml);
        $parser->testParseRecord($xml,$tools);

        $xmlMethod = '<?xml version="1.0"?>
          <method name="foo" type="update">
            <parameter name="price" />
            <parameter name="price_big" />
            <values>
                 <value property="price"     expr="$price"     />
                 <value property="price_big" expr="$price_big" />
            </values>
          </method>';
        $result = 
        '<?xml version="1.0"?>
        <object>
            <string p="name" value="foo"/>
            <string p="type" value="update"/>
            <boolean p="distinct" value="false"/>
            <boolean p="eventBeforeEnabled" value="false"/>
            <boolean p="eventAfterEnabled" value="false"/>
            <object m="getConditions()" class="jDaoConditions">
                <object p="condition" class="jDaoCondition">
                    <null p="parent" />
                    <array p="conditions">array()</array>
                    <array p="group">array()</array>
                </object>
                <array p="order">array()</array>
            </object>
            <array m="getParameters ()">array(\'price\',\'price_big\')</array>
            <array m="getParametersDefaultValues ()">array()</array>
            <null m="getLimit ()"/>
            <array m="getValues ()">array(\'price\'=>array(\'$price\',true), \'price_big\'=>array(\'$price_big\',true))</array>
            <null m="getProcStock ()"/>
            <null m="getBody ()"/>
        </object>';

        //$this->sendMessage("test good method ".$k);
        $xml= simplexml_load_string($xmlMethod);
        try{
            $p = new jDaoMethod($xml, $parser);
            $this->assertComplexIdenticalStr($p, $result);
        }catch(jDaoXmlException $e){
            $this->fail("Exception sur le contenu xml inattendue : ".$e->getMessage());
        }catch(Exception $e){
            $this->fail("Exception inconnue : ".$e->getMessage());
        }
    }



    protected $badmethDatas=array(
      array('<?xml version="1.0"?>
          <method name="foo" type="update">
          </method>',
          'jelix~daoxml.method.values.undefine', array('foo~bar','','foo')
          ),
      array('<?xml version="1.0"?>
          <method name="foo" type="update">
            <values>
                <value  value="" />
            </values>
          </method>',
          'jelix~daoxml.method.values.property.unknown', array('foo~bar','','foo','')
          ),
      array('<?xml version="1.0"?>
          <method name="foo" type="update">
            <values>
                <value property="plop" value="" />
            </values>
          </method>',
          'jelix~daoxml.method.values.property.unknown', array('foo~bar','','foo','plop')
          ),
      array('<?xml version="1.0"?>
          <method name="foo" type="update">
            <values>
                <value property="author_firstname" value="" />
            </values>
          </method>',
          'jelix~daoxml.method.values.property.bad', array('foo~bar','','foo','author_firstname')
          ),
      array('<?xml version="1.0"?>
          <method name="foo" type="update">
            <values>
                <value property="id" value="" />
            </values>
          </method>',
          'jelix~daoxml.method.values.property.pkforbidden', array('foo~bar','','foo','id')
          ),
      array('<?xml version="1.0"?>
          <method name="foo" type="update">
            <values>
                <value property="subject" value="abc" expr="\'abs\'"/>
            </values>
          </method>',
          'jelix~daoxml.method.values.valueexpr', array('foo~bar','','foo','subject')
          ),

    );

   function testBadUpdateMethods() {
 $dao ='<?xml version="1.0"?>
<dao xmlns="http://jelix.org/ns/dao/1.0">
  <datasources>
    <primarytable name="news" primarykey="news_id" />
    <foreigntable name="news_author" primarykey="author_id" onforeignkey="author_id" />
  </datasources>
  <record>
    <property name="id" fieldname="news_id" datatype="autoincrement" />
    <property name="subject" datatype="string" />
    <property name="texte" datatype="string" />
    <property name="publishdate" datatype="date" />
    <property name="author_firstname" fieldname="firstname" datatype="string" table="news_author" />
    <property name="author_lastname" fieldname="lastname"  datatype="string" table="news_author" />
  </record>
</dao>';

        $parser = new testjDaoParser($this->_selector);
        $xml = simplexml_load_string($dao);
        $tools = new mysqlDbTools(null);
        $parser->testParseDatasource($xml);
        $parser->testParseRecord($xml,$tools);

        foreach($this->badmethDatas as $k=>$t){
            //$this->sendMessage("test bad method ".$k);
            $xml= simplexml_load_string($t[0]);
            try{
                $p = new jDaoMethod($xml, $parser);
                $this->fail("Pas d'exception survenue !");
            }catch(jDaoXmlException $e){
                $this->assertEquals($t[1], $e->getLocaleKey());
                $this->assertEquals($t[2], $e->getLocaleParameters());
            }catch(Exception $e){
                $this->fail("Exception inconnue : ".$e->getMessage());
            }
        }
    }

   function testBadUpdateMethods2() {
 $dao ='<?xml version="1.0"?>
<dao xmlns="http://jelix.org/ns/dao/1.0">
  <datasources>
    <primarytable name="news" primarykey="news_id,foo_id" />
  </datasources>
  <record>
    <property name="id" fieldname="news_id" datatype="autoincrement" />
    <property name="id2" fieldname="foo_id" datatype="integer" />
  </record>
</dao>';

        $parser = new testjDaoParser($this->_selector);
        $xml = simplexml_load_string($dao);
        $tools = new mysqlDbTools(null);
        $parser->testParseDatasource($xml);
        $parser->testParseRecord($xml,$tools);

        //$this->sendMessage("test bad update method ");
        $xml= simplexml_load_string('<?xml version="1.0"?>
          <method name="tryupdate" type="update">
            <parameter name="something" />
            <values>
                <value property="foo_id" expr="$something" />
            </values>
          </method>');

        try{
            $p = new jDaoMethod($xml, $parser);
            $this->fail("Pas d'exception survenue !");
        }catch(jDaoXmlException $e){
            $this->assertEquals('jelix~daoxml.method.update.forbidden', $e->getLocaleKey());
            $this->assertEquals(array('foo~bar','','tryupdate'), $e->getLocaleParameters());
        }catch(Exception $e){
            $this->fail("Exception inconnue : ".$e->getMessage());
        }
    }
}


