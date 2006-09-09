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

class UTDao extends jUnitTestCase {

    protected $dsTest=array(
        array('<?xml version="1.0"?>
<dao xmlns="http://jelix.org/ns/dao/1.0">
  <datasources>
     <primarytable name="news" primarykey="news_id" />
  </datasources>
</dao>',

'<?xml version="1.0"?>
<object class="jDaoParser">
    <array method="getProperties()">array()</array>
    <array method="getTables()">
        <array key="news">
            <string key="name" value="news" />
            <string key="realname" value="news" />
            <array key="pk" value="">array(\'news_id\')</array>
            <!--<array key="fk" value="">array()</array>-->
            <array key="fields">array()</array>
        </array>
    </array>
    <string method="getPrimaryTable()" value="news"/>
    <array method="getMethods()">array()</array>
    <array method="getOuterJoins()">array()</array>
    <array method="getInnerJoins()">array()</array>
</object>'
),

        array('<?xml version="1.0"?>
<dao xmlns="http://jelix.org/ns/dao/1.0">
  <datasources>
     <primarytable name="news" realname="foo_news" primarykey="news_id" />
  </datasources>
</dao>',

'<?xml version="1.0"?>
<object class="jDaoParser">
    <array method="getProperties()">array()</array>
    <array method="getTables()">
        <array key="news">
            <string key="name" value="news" />
            <string key="realname" value="foo_news" />
            <array key="pk" value="">array(\'news_id\')</array>
            <!--<array key="fk" value="">array()</array>-->
            <array key="fields">array()</array>
        </array>
    </array>
    <string method="getPrimaryTable()" value="news"/>
    <array method="getMethods()">array()</array>
    <array method="getOuterJoins()">array()</array>
    <array method="getInnerJoins()">array()</array>
</object>'
),



        array('<?xml version="1.0"?>
<dao xmlns="http://jelix.org/ns/dao/1.0">
  <datasources>
     <primarytable name="news"           primarykey="news_id" />
     <foreigntable name="news_rubriques" primarykey="news_rubriques_id" onforeignkey="news_rubrique" />
  </datasources>
</dao>',

'<?xml version="1.0"?>
<object class="jDaoParser">
    <array method="getProperties()">array()</array>
    <array method="getTables()">
        <array key="news">
            <string key="name" value="news" />
            <string key="realname" value="news" />
            <array key="pk" value="">array(\'news_id\')</array>
            <!-- <array key="fk" value="">array()</array>-->
            <array key="fields">array()</array>
        </array>
        <array key="news_rubriques">
            <string key="name" value="news_rubriques" />
            <string key="realname" value="news_rubriques" />
            <array key="pk" value="">array(\'news_rubriques_id\')</array>
            <array key="fk" value="">array(\'news_rubrique\')</array>
            <array key="fields">array()</array>
        </array>
    </array>
    <string method="getPrimaryTable()" value="news"/>
    <array method="getMethods()">array()</array>
    <array method="getOuterJoins()">array()</array>
    <array method="getInnerJoins()">array(array(\'news_rubriques\',0))</array>
</object>'
),


 array('<?xml version="1.0"?>
<dao xmlns="http://jelix.org/ns/dao/1.0">
  <datasources>
     <primarytable name="news"           primarykey="news_id" />
     <optionalforeigntable name="news_rubriques" primarykey="news_rubriques_id" onforeignkey="news_rubrique" />
  </datasources>
</dao>',

'<?xml version="1.0"?>
<object class="jDaoParser">
    <array method="getProperties()">array()</array>
    <array method="getTables()">
        <array key="news">
            <string key="name" value="news" />
            <string key="realname" value="news" />
            <array key="pk" value="">array(\'news_id\')</array>
            <!-- <array key="fk" value="">array()</array>-->
            <array key="fields">array()</array>
        </array>
        <array key="news_rubriques">
            <string key="name" value="news_rubriques" />
            <string key="realname" value="news_rubriques" />
            <array key="pk" value="">array(\'news_rubriques_id\')</array>
            <array key="fk" value="">array(\'news_rubrique\')</array>
            <array key="fields">array()</array>
        </array>
    </array>
    <string method="getPrimaryTable()" value="news"/>
    <array method="getMethods()">array()</array>
    <array method="getOuterJoins()">array(\'news_rubriques\')</array>
    <array method="getInnerJoins()"></array>
</object>'
),


      );

    function testGoodDatasources() {

        foreach($this->dsTest as $k=>$t){
            $this->sendMessage("test good datasource ".$k);
            $xml= simplexml_load_string($t[0]);
            $p = new jDaoParser();
            try{
                $p->parse($xml,2);
                $this->assertComplexIdenticalStr($p, $t[1]);
            }catch(jDaoXmlException $e){
                $this->fail("Exception sur le contenu xml inattendue : ".$e->getMessage());
            }catch(Exception $e){
                $this->fail("Exception inconnue : ".$e->getMessage());
            }
        }
    }




    protected $dsTestbad=array(
        array('<?xml version="1.0"?>
<dao xmlns="http://jelix.org/ns/dao/1.0">
  <datasources>
  </datasources>
</dao>',
'jelix~daoxml.datasource.missing',
array('','')
),

        array('<?xml version="1.0"?>
<dao xmlns="http://jelix.org/ns/dao/1.0">
  <datasources>
    <primarytable />
  </datasources>
</dao>',
'jelix~daoxml.table.name',
array('','')

),

        array('<?xml version="1.0"?>
<dao xmlns="http://jelix.org/ns/dao/1.0">
  <datasources>
    <primarytable name="news" />
  </datasources>
</dao>',
'jelix~daoxml.primarykey.missing',
array('','')

),
        array('<?xml version="1.0"?>
<dao xmlns="http://jelix.org/ns/dao/1.0">
  <datasources>
    <primarytable name="news" primarykey=""/>
  </datasources>
</dao>',
'jelix~daoxml.primarykey.missing',
array('','')

),
        array('<?xml version="1.0"?>
<dao xmlns="http://jelix.org/ns/dao/1.0">
  <datasources>
    <primarytable name="news"           primarykey="news_id" />
    <primarytable />
  </datasources>
</dao>',
'jelix~daoxml.table.two.many',
array('','')

),


      );

    function testBadDatasources() {

        foreach($this->dsTestbad as $k=>$t){
            $this->sendMessage("test bad datasource ".$k);
            $xml= simplexml_load_string($t[0]);
            $p = new jDaoParser();
            try{
                $p->parse($xml,2);
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