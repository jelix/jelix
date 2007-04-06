<?php
/**
* @package     testapp
* @subpackage  unittest module
* @author      Jouanneau Laurent
* @contributor
* @copyright   2006 Jouanneau laurent
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

require_once(JELIX_LIB_DAO_PATH.'jDaoParser.class.php');

require_once(dirname(__FILE__).'/junittestcase.class.php');

class testDaoCompiler {
    public $foo1;
    public $foo2;
    public $foo3;
    public $foo4;
    public $foo5;
    public $foo6;
    public $foo7;

    public function getDbDriver(){ return  'mysql';}

    public function doDefError($message, $arg1=null){

    }
}


class UTSimpleTest extends jUnitTestCase {

    function testIdentical() {
        $v = new testDaoCompiler();
        $v->foo1="bar";
        $v->foo2=2;
        $v->foo3=3.56;
        $v->foo4=null;
        $v->foo5=false;
        $v->foo6=true;
        $v->foo7=array('toto', 'titi');

        $def='<?xml version="1.0"?>
<object class="testDaoCompiler">
    <string property="foo1" value="bar" />
    <int property="foo2" value="2" />
    <float property="foo3" value="3.56" />
    <null property="foo4" />
    <boolean property="foo5" value="false" />
    <boolean property="foo6" value="true" />
    <array property="foo7">array("toto", "titi")</array>
    <string method="getDbDriver()" value="mysql" />
</object>';

        $this->assertComplexIdenticalStr($v, $def);
    }


    function testIdentical2() {
        $v = new testDaoCompiler();
        $v->foo1="bar";
        $v->foo2=2;
        $v->foo3=3.568;
        $v->foo4=null;
        $v->foo5=false;
        $v->foo6=false;
        $v->foo7=array('toto', 'tit');

        $def='<?xml version="1.0"?>
<object class="testDaoCompiler">
    <string property="foo1" value="bar" />
    <int property="foo2" value="2" />
    <float property="foo3" value="3.56" />
    <null property="foo4" />
    <boolean property="foo5" value="false" />
    <boolean property="foo6" value="true" />
    <array property="foo7">array("toto", "titi")</array>
    <string method="getDbDriver()" value="mysql" />
</object>';

        $this->assertComplexIdenticalStr($v, $def);
    }

    function testIdentical3() {
        $v = new testDaoCompiler();
        $v->foo1="bar";
        $v->foo2=2;
        $v->foo3=3.56;
        $v->foo4=null;
        $v->foo5=false;
        $v->foo6=new testDaoCompiler();
        $v->foo6->foo1="hello";
        $v->foo6->foo2=array('popo','papa');

        $v->foo7=array('toto', 'titi');

        $def='<?xml version="1.0"?>
<object class="testDaoCompiler">
    <string property="foo1" value="bar" />
    <int property="foo2" value="2" />
    <float p="foo3" value="3.56" />
    <null p="foo4" />
    <boolean property="foo5" value="false" />
    <object property="foo6">
        <string property="foo1" value="hello" />
        <array property="foo2">
            <string value="popo" />
            <string key="1" value="papa" />
        </array>
    </object>
    <array property="foo7">array("toto", "titi")</array>
    <string m="getDbDriver()" value="mysql" />
</object>';

        $this->assertComplexIdenticalStr($v, $def);
    }


}



?>