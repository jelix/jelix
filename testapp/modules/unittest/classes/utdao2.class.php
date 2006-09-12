<?php
/**
* @package     testapp
* @subpackage  unittest module
* @version     $Id$
* @author      Jouanneau Laurent
* @contributor
* @copyright   2006 Jouanneau laurent
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

require_once(JELIX_LIB_DAO_PATH.'jDaoCompiler.class.php');
require_once(JELIX_LIB_DAO_PATH.'jDaoParser.class.php');

require_once(dirname(__FILE__).'/junittestcase.class.php');

class UTDao2 extends jUnitTestCase {

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
            <string p="distinct" value=""/>
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
            <string p="distinct" value=""/>
            <object m="getConditions()" class="jDaoConditions">
                <object p="condition" class="jDaoCondition">
                    <null p="parent" />
                    <array p="conditions">array()</array>
                    <array p="group">array()</array>
                </object>
                <array p="order">array("publishdate"=>"$aWay")</array>
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
            <string p="distinct" value=""/>
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
            <array m="getLimit ()">array(10,5,false,false)</array>
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
            <string p="distinct" value=""/>
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
            <array m="getLimit ()">array("$aOffset","$aCount",true,true)</array>
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
            <string p="distinct" value=""/>
            <object m="getConditions()" class="jDaoConditions">
                <object p="condition" class="jDaoCondition">
                    <null p="parent" />
                    <array p="conditions">array(
                     array("field_id"=>"subject","value"=>"bar", "operator"=>"=", "expr"=>""),
                     array("field_id"=>"texte","value"=>"machine", "operator"=>"=", "expr"=>""))</array>
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
          <method name="foo" type="select">
            <conditions logic="or">
                <eq property="subject" value="bar" />
                <eq property="texte" value="machine" />
            </conditions>
          </method>',
        '<?xml version="1.0"?>
        <object>
            <string p="name" value="foo"/>
            <string p="type" value="select"/>
            <string p="distinct" value=""/>
            <object m="getConditions()" class="jDaoConditions">
                <object p="condition" class="jDaoCondition">
                    <null p="parent" />
                    <array p="conditions">array(
                     array("field_id"=>"subject","value"=>"bar", "operator"=>"=", "expr"=>""),
                     array("field_id"=>"texte","value"=>"machine", "operator"=>"=", "expr"=>""))</array>
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
            <string p="distinct" value=""/>
            <object m="getConditions()" class="jDaoConditions">
                <object p="condition" class="jDaoCondition">
                    <null p="parent" />
                    <array p="conditions">array(
                     array("field_id"=>"subject","value"=>"bar", "operator"=>"=", "expr"=>""),
                     array("field_id"=>"texte","value"=>"machine", "operator"=>"=", "expr"=>""))</array>
                    <array p="group">
                        <object p="condition" class="jDaoCondition">
                            <notnull p="parent" />
                            <array p="conditions">array(
                            array("field_id"=>"subject","value"=>"bar", "operator"=>"=", "expr"=>""),
                            array("field_id"=>"texte","value"=>"machine", "operator"=>"=", "expr"=>""))</array>
                            <array p="group">array()</array>
                            <string p="glueOp" value="AND"/>
                        </object>
                        <object p="condition" class="jDaoCondition">
                            <object p="parent" class="jDaoCondition" />
                            <array p="conditions">array(
                            array("field_id"=>"subject","value"=>"bar2", "operator"=>"=", "expr"=>""))</array>
                            <array p="group">
                                <object p="condition" class="jDaoCondition">
                                    <notnull p="parent" />
                                    <array p="conditions">array(
                                    array("field_id"=>"texte","value"=>"machine2", "operator"=>"=", "expr"=>""),
                                    array("field_id"=>"texte","value"=>"truc", "operator"=>"=", "expr"=>""))</array>
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

 /*       array('<?xml version="1.0"?>
          <method name="foo" type="select">
            <limit >
                <orderitem property="publishdate" way="desc"/>
            </order>
          </method>',
        '<?xml version="1.0"?>
        <object>
             <string p="name" value="foo"/>
            <string p="type" value="select"/>
            <string p="distinct" value=""/>
            <object m="getConditions()" class="jDaoConditions">
                <object p="condition" class="jDaoCondition">
                    <null p="parent" />
                    <array p="conditions">array()</array>
                    <array p="group">array()</array>
                </object>
                <array p="order">array("publishdate"=>"desc")</array>
                <array p="fields">array()</array>
            </object>
            <array m="getParameters ()">array()</array>
            <array m="getParametersDefaultValues ()">array()</array>
            <null m="getLimit ()"/>
            <array m="getValues ()">array()</array>
            <null m="getProcStock ()"/>
            <null m="getBody ()"/>
        </object>'),
                array('<?xml version="1.0"?>
          <method type="select">
     <parameter />
     <conditions />
     <order />
     <limit />
  </method>',
        '<?xml version="1.0"?>
        <object>
            <string p="name" value="foo"/>
            <string p="type" value="select"/>
            <string p="distinct" value=""/>
            <object m="getConditions()" class="jDaoConditions">
                <object p="condition" class="jDaoCondition">
                    <null p="parent" />
                    <array p="conditions">array()</array>
                    <array p="group">array()</array>
                </object>
                <array p="order">array("publishdate"=>"desc")</array>
                <array p="fields">array()</array>
            </object>
            <array m="getParameters ()">array()</array>
            <array m="getParametersDefaultValues ()">array()</array>
            <null m="getLimit ()"/>
            <array m="getValues ()">array()</array>
            <null m="getProcStock ()"/>
            <null m="getBody ()"/>
        </object>'),
        array('<?xml version="1.0"?>
          <method type="selectfirst">
     <parameter />
     <conditions />
     <order />
     <limit />
  </method>',
        '<?xml version="1.0"?>
        <object>
            <string p="name" value="foo"/>
            <string p="type" value="select"/>
            <string p="distinct" value=""/>
            <object m="getConditions()" class="jDaoConditions">
                <object p="condition" class="jDaoCondition">
                    <null p="parent" />
                    <array p="conditions">array()</array>
                    <array p="group">array()</array>
                </object>
                <array p="order">array("publishdate"=>"desc")</array>
                <array p="fields">array()</array>
            </object>
            <array m="getParameters ()">array()</array>
            <array m="getParametersDefaultValues ()">array()</array>
            <null m="getLimit ()"/>
            <array m="getValues ()">array()</array>
            <null m="getProcStock ()"/>
            <null m="getBody ()"/>
        </object>'),

        array('<?xml version="1.0"?>
          <method type="count">
     <parameter />
     <conditions />
  </method>',
        '<?xml version="1.0"?>
        <object>
             <string p="name" value="foo"/>
            <string p="type" value="select"/>
            <string p="distinct" value=""/>
            <object m="getConditions()" class="jDaoConditions">
                <object p="condition" class="jDaoCondition">
                    <null p="parent" />
                    <array p="conditions">array()</array>
                    <array p="group">array()</array>
                </object>
                <array p="order">array("publishdate"=>"desc")</array>
                <array p="fields">array()</array>
            </object>
            <array m="getParameters ()">array()</array>
            <array m="getParametersDefaultValues ()">array()</array>
            <null m="getLimit ()"/>
            <array m="getValues ()">array()</array>
            <null m="getProcStock ()"/>
            <null m="getBody ()"/>
        </object>'),

        array('<?xml version="1.0"?>
         <method type="delete">
     <parameter />
     <conditions />
  </method>',
        '<?xml version="1.0"?>
        <object>
            <string p="name" value="foo"/>
            <string p="type" value="select"/>
            <string p="distinct" value=""/>
            <object m="getConditions()" class="jDaoConditions">
                <object p="condition" class="jDaoCondition">
                    <null p="parent" />
                    <array p="conditions">array()</array>
                    <array p="group">array()</array>
                </object>
                <array p="order">array("publishdate"=>"desc")</array>
                <array p="fields">array()</array>
            </object>
            <array m="getParameters ()">array()</array>
            <array m="getParametersDefaultValues ()">array()</array>
            <null m="getLimit ()"/>
            <array m="getValues ()">array()</array>
            <null m="getProcStock ()"/>
            <null m="getBody ()"/>
        </object>'),

        array('<?xml version="1.0"?>
        <method type="update">
     <parameter />
     <conditions />
     <values />
  </method>',
        '<?xml version="1.0"?>
        <object>
            <string p="name" value="foo"/>
            <string p="type" value="select"/>
            <string p="distinct" value=""/>
            <object m="getConditions()" class="jDaoConditions">
                <object p="condition" class="jDaoCondition">
                    <null p="parent" />
                    <array p="conditions">array()</array>
                    <array p="group">array()</array>
                </object>
                <array p="order">array("publishdate"=>"desc")</array>
                <array p="fields">array()</array>
            </object>
            <array m="getParameters ()">array()</array>
            <array m="getParametersDefaultValues ()">array()</array>
            <null m="getLimit ()"/>
            <array m="getValues ()">array()</array>
            <null m="getProcStock ()"/>
            <null m="getBody ()"/>
        </object>'),

        array('<?xml version="1.0"?>
          <method type="php">
    <parameter />
    <body><![CDATA[
        ici le code php de la méthode
    ]]></body>
  </method>',
        '<?xml version="1.0"?>
        <object>
            <string p="name" value="foo"/>
            <string p="type" value="select"/>
            <string p="distinct" value=""/>
            <object m="getConditions()" class="jDaoConditions">
                <object p="condition" class="jDaoCondition">
                    <null p="parent" />
                    <array p="conditions">array()</array>
                    <array p="group">array()</array>
                </object>
                <array p="order">array("publishdate"=>"desc")</array>
                <array p="fields">array()</array>
            </object>
            <array m="getParameters ()">array()</array>
            <array m="getParametersDefaultValues ()">array()</array>
            <null m="getLimit ()"/>
            <array m="getValues ()">array()</array>
            <null m="getProcStock ()"/>
            <null m="getBody ()"/>
        </object>'),
*/

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

        $parser = new jDaoParser();
        $parser->parse(simplexml_load_string($dao),1);

        foreach($this->methDatas as $k=>$t){
            $this->sendMessage("test good method ".$k);
            $xml= simplexml_load_string($t[0]);
            try{
                $p = new jDaoMethod($xml, $parser);
                $this->assertComplexIdenticalStr($p, $t[1]);
            }catch(jDaoXmlException $e){
                $this->fail("Exception sur le contenu xml inattendue : ".$e->getLocaleMessage());
            }catch(Exception $e){
                $this->fail("Exception inconnue : ".$e->getMessage());
            }
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
          'jelix~daoxml.method.orderitem.parameter.unknow', array('','','foo','$afoo')
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

        $parser = new jDaoParser();
        $parser->parse(simplexml_load_string($dao),1);

        foreach($this->badmethDatas as $k=>$t){
            $this->sendMessage("test bad method ".$k);
            $xml= simplexml_load_string($t[0]);
            try{
                $p = new jDaoMethod($xml, $parser);
                $this->fail("Pas d'exception survenue !");
            }catch(jDaoXmlException $e){
                $this->assertEqual($e->getMessage(), $t[1]);
                $this->assertEqual($e->localeParams, $t[2]);
            }catch(Exception $e){
                $this->fail("Exception inconnue : ".$e->getMessage());
            }
        }
    }


}



?>