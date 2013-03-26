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


class jdao_parser2Test extends jUnitTestCase {

    protected $_selector;
    function setUp() {
        $this->_selector = new fakejSelectorDao("foo", "bar", "mysql");
    }

    function tearDown() {
        $this->_selector = null;
    }

    protected $methDatas=array(
        array('<?xml version="1.0"?>
          <method name="foo" type="select">
            <order>
                <orderitem property="publishdate" way="desc"/>
            </order>
          </method>',
        '<?xml version="1.0"?>
        <object>
            <string p="name" value="foo"/>
            <string p="type" value="select"/>
            <boolean p="distinct" value="false"/>
            <boolean p="eventBeforeEnabled" value="false"/>
            <boolean p="eventAfterEnabled" value="false"/>
            <object m="getConditions()" class="jDaoConditions">
                <object p="condition" class="jDaoCondition">
                    <null p="parent" />
                    <array p="conditions">array()</array>
                    <array p="group">array()</array>
                </object>
                <array p="order">array("publishdate"=>"desc")</array>
            </object>
            <array m="getParameters ()">array()</array>
            <array m="getParametersDefaultValues ()">array()</array>
            <null m="getLimit ()"/>
            <array m="getValues ()">array()</array>
            <null m="getProcStock ()"/>
            <null m="getBody ()"/>
        </object>'),
        array('<?xml version="1.0"?>
          <method name="foo" type="select">
            <parameter name="aWay" />
            <order>
                <orderitem property="publishdate" way="$aWay"/>
            </order>
          </method>',
        '<?xml version="1.0"?>
        <object>
            <string p="name" value="foo"/>
            <string p="type" value="select"/>
            <boolean p="distinct" value="false"/>
            <boolean p="eventBeforeEnabled" value="false"/>
            <boolean p="eventAfterEnabled" value="false"/>
            <object m="getConditions()" class="jDaoConditions">
                <object p="condition" class="jDaoCondition">
                    <null p="parent" />
                    <array p="conditions">array()</array>
                    <array p="group">array()</array>
                </object>
                <array p="order">array("publishdate"=>\'$aWay\')</array>
            </object>
            <array m="getParameters ()">array("aWay")</array>
            <array m="getParametersDefaultValues ()">array()</array>
            <null m="getLimit ()"/>
            <array m="getValues ()">array()</array>
            <null m="getProcStock ()"/>
            <null m="getBody ()"/>
        </object>'),
        array('<?xml version="1.0"?>
          <method name="foo" type="select">
            <limit offset="10" count="5" />
          </method>',
        '<?xml version="1.0"?>
        <object>
            <string p="name" value="foo"/>
            <string p="type" value="select"/>
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
            <array m="getLimit ()">array("offset"=>10, "count"=>5, "offsetparam"=>false,"countparam"=>false)</array>
            <array m="getValues ()">array()</array>
            <null m="getProcStock ()"/>
            <null m="getBody ()"/>
        </object>'),
        array('<?xml version="1.0"?>
          <method name="foo" type="select">
            <parameter name="aOffset" />
            <parameter name="aCount" />
            <limit offset="$aOffset" count="$aCount" />
          </method>',
        '<?xml version="1.0"?>
        <object>
            <string p="name" value="foo"/>
            <string p="type" value="select"/>
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
            <array m="getParameters ()">array("aOffset","aCount")</array>
            <array m="getParametersDefaultValues ()">array()</array>
            <array m="getLimit ()">array("offset"=>\'$aOffset\', "count"=>\'$aCount\', "offsetparam"=>true,"countparam"=>true)</array>
            <array m="getValues ()">array()</array>
            <null m="getProcStock ()"/>
            <null m="getBody ()"/>
        </object>'),

        array('<?xml version="1.0"?>
          <method name="foo" type="select">
            <conditions>
                <eq property="subject" value="bar" />
                <eq property="texte" value="machine" />
            </conditions>
          </method>',
        '<?xml version="1.0"?>
        <object>
            <string p="name" value="foo"/>
            <string p="type" value="select"/>
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
            <array m="getValues ()">array()</array>
            <null m="getProcStock ()"/>
            <null m="getBody ()"/>
        </object>'),

        array('<?xml version="1.0"?>
          <method name="foo" type="select" distinct="true">
            <conditions logic="or">
                <eq property="subject" value="bar" />
                <eq property="texte" value="machine" />
            </conditions>
          </method>',
        '<?xml version="1.0"?>
        <object>
            <string p="name" value="foo"/>
            <string p="type" value="select"/>
            <boolean p="distinct" value="true"/>
            <boolean p="eventBeforeEnabled" value="false"/>
            <boolean p="eventAfterEnabled" value="false"/>
            <object m="getConditions()" class="jDaoConditions">
                <object p="condition" class="jDaoCondition">
                    <null p="parent" />
                    <array p="conditions">array(
                     array("field_id"=>"subject","field_pattern"=>"","value"=>"bar", "operator"=>"=", "isExpr"=>false),
                     array("field_id"=>"texte","field_pattern"=>"","value"=>"machine", "operator"=>"=", "isExpr"=>false))</array>
                    <array p="group">array()</array>
                    <string p="glueOp" value="OR"/>
                </object>
                <array p="order">array()</array>
            </object>
            <array m="getParameters ()">array()</array>
            <array m="getParametersDefaultValues ()">array()</array>
            <null m="getLimit ()"/>
            <array m="getValues ()">array()</array>
            <null m="getProcStock ()"/>
            <null m="getBody ()"/>
        </object>'),


        array('<?xml version="1.0"?>
          <method name="foo" type="select">
            <conditions logic="or">
                <conditions>
                    <eq property="subject" value="bar" />
                    <eq property="texte" value="machine" />
                </conditions>
                <conditions>
                    <eq property="subject" value="bar2" />
                    <conditions logic="or">
                        <eq property="texte" value="machine2" />
                        <eq property="texte" value="truc" />
                    </conditions>
                </conditions>
            </conditions>
          </method>',
        '<?xml version="1.0"?>
        <object>
            <string p="name" value="foo"/>
            <string p="type" value="select"/>
            <boolean p="distinct" value="false"/>
            <boolean p="eventBeforeEnabled" value="false"/>
            <boolean p="eventAfterEnabled" value="false"/>
            <object m="getConditions()" class="jDaoConditions">
                <object p="condition" class="jDaoCondition">
                    <null p="parent" />
                    <array p="conditions">array()</array>
                    <array p="group">
                        <object p="condition" class="jDaoCondition">
                            <notnull p="parent" />
                            <array p="conditions">array(
                            array("field_id"=>"subject","field_pattern"=>"","value"=>"bar", "operator"=>"=", "isExpr"=>false),
                            array("field_id"=>"texte","field_pattern"=>"","value"=>"machine", "operator"=>"=", "isExpr"=>false))</array>
                            <array p="group">array()</array>
                            <string p="glueOp" value="AND"/>
                        </object>
                        <object p="condition" class="jDaoCondition">
                            <object p="parent" class="jDaoCondition" />
                            <array p="conditions">array(
                            array("field_id"=>"subject","field_pattern"=>"","value"=>"bar2", "operator"=>"=", "isExpr"=>false))</array>
                            <array p="group">
                                <object p="condition" class="jDaoCondition">
                                    <notnull p="parent" />
                                    <array p="conditions">array(
                                    array("field_id"=>"texte","field_pattern"=>"","value"=>"machine2", "operator"=>"=", "isExpr"=>false),
                                    array("field_id"=>"texte","field_pattern"=>"","value"=>"truc", "operator"=>"=", "isExpr"=>false))</array>
                                    <array p="group">array()</array>
                                    <string p="glueOp" value="OR"/>
                                </object>
                            </array>
                            <string p="glueOp" value="AND"/>
                        </object>
                    </array>
                    <string p="glueOp" value="OR"/>
                </object>
                <array p="order">array()</array>
            </object>
            <array m="getParameters ()">array()</array>
            <array m="getParametersDefaultValues ()">array()</array>
            <null m="getLimit ()"/>
            <array m="getValues ()">array()</array>
            <null m="getProcStock ()"/>
            <null m="getBody ()"/>
        </object>'),

        array('<?xml version="1.0"?>
          <method name="foo" type="select">
            <conditions>
                <eq property="subject" value="" />
                <eq property="texte" expr="\'machine\'" />
            </conditions>
          </method>',
        '<?xml version="1.0"?>
        <object>
            <string p="name" value="foo"/>
            <string p="type" value="select"/>
            <boolean p="distinct" value="false"/>
            <boolean p="eventBeforeEnabled" value="false"/>
            <boolean p="eventAfterEnabled" value="false"/>
            <object m="getConditions()" class="jDaoConditions">
                <object p="condition" class="jDaoCondition">
                    <null p="parent" />
                    <array p="conditions">array(
                     array("field_id"=>"subject","field_pattern"=>"","value"=>"", "operator"=>"=", "isExpr"=>false),
                     array("field_id"=>"texte","field_pattern"=>"","value"=>"\'machine\'", "operator"=>"=", "isExpr"=>true))</array>
                    <array p="group">array()</array>
                    <string p="glueOp" value="AND"/>
                </object>
                <array p="order">array()</array>
            </object>
            <array m="getParameters ()">array()</array>
            <array m="getParametersDefaultValues ()">array()</array>
            <null m="getLimit ()"/>
            <array m="getValues ()">array()</array>
            <null m="getProcStock ()"/>
            <null m="getBody ()"/>
            <null m="getGroupBy ()"/>
        </object>'),

        array('<?xml version="1.0"?>
          <method name="foo" type="select" groupby="id">
            <conditions>
                <eq property="subject" value="" />
            </conditions>
          </method>',
        '<?xml version="1.0"?>
        <object>
            <string p="name" value="foo"/>
            <string p="type" value="select"/>
            <boolean p="distinct" value="false"/>
            <boolean p="eventBeforeEnabled" value="false"/>
            <boolean p="eventAfterEnabled" value="false"/>
            <object m="getConditions()" class="jDaoConditions">
                <object p="condition" class="jDaoCondition">
                    <null p="parent" />
                    <array p="conditions">array(
                     array("field_id"=>"subject","field_pattern"=>"","value"=>"", "operator"=>"=", "isExpr"=>false))</array>
                    <array p="group">array()</array>
                    <string p="glueOp" value="AND"/>
                </object>
                <array p="order">array()</array>
            </object>
            <array m="getParameters ()">array()</array>
            <array m="getParametersDefaultValues ()">array()</array>
            <null m="getLimit ()"/>
            <array m="getValues ()">array()</array>
            <null m="getProcStock ()"/>
            <null m="getBody ()"/>
            <array m="getGroupBy ()">array("id")</array>
        </object>'),

        array('<?xml version="1.0"?>
          <method name="foo" type="select" groupby="id, subject">
            <conditions>
                <eq property="subject" value="" />
            </conditions>
          </method>',
        '<?xml version="1.0"?>
        <object>
            <string p="name" value="foo"/>
            <string p="type" value="select"/>
            <boolean p="distinct" value="false"/>
            <boolean p="eventBeforeEnabled" value="false"/>
            <boolean p="eventAfterEnabled" value="false"/>
            <object m="getConditions()" class="jDaoConditions">
                <object p="condition" class="jDaoCondition">
                    <null p="parent" />
                    <array p="conditions">array(
                     array("field_id"=>"subject","field_pattern"=>"","value"=>"", "operator"=>"=", "isExpr"=>false))</array>
                    <array p="group">array()</array>
                    <string p="glueOp" value="AND"/>
                </object>
                <array p="order">array()</array>
            </object>
            <array m="getParameters ()">array()</array>
            <array m="getParametersDefaultValues ()">array()</array>
            <null m="getLimit ()"/>
            <array m="getValues ()">array()</array>
            <null m="getProcStock ()"/>
            <null m="getBody ()"/>
            <array m="getGroupBy ()">array("id","subject")</array>
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
        $doc = simplexml_load_string($dao);
        $parser->testParseDatasource($doc);
        $parser->testParseRecord($doc, new mysqlDbTools(null));

        foreach($this->methDatas as $k=>$t){
            // $this->sendMessage("test good method ".$k);
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



    protected $badmethDatas=array(
      array('<?xml version="1.0"?>
          <method name="foo" type="select">
            <parameter name="aWay" />
            <order>
                <orderitem property="publishdate" way="$afoo"/>
            </order>
          </method>',
          'jelix~daoxml.method.orderitem.parameter.unknown', array('foo~bar','','foo','$afoo')
          ),

    );

   function testBadMethods() {
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
        $doc = simplexml_load_string($dao);
        $parser->testParseDatasource($doc);
        $parser->testParseRecord($doc, new mysqlDbTools(null));
        
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

}
