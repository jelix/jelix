<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Laurent Jouanneau
* @contributor Julien Issler
* @copyright   2006-2007 Laurent Jouanneau
* @copyright   2008 Julien Issler
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

require_once(JELIX_LIB_PATH.'dao/jDaoCompiler.class.php');
require_once(JELIX_LIB_PATH.'dao/jDaoConditions.class.php');


class jDao_ConditionsTest extends jUnitTestCase {

    function testConditions() {

        try {
            $cond=new jDaoConditions();

            $check='<?xml version="1.0"?>
            <object class="jDaoConditions">
                <array p="order">array()</array>
                <boolean m="isEmpty()" value="true" />
                <object p="condition" class="jDaoCondition">
                    <null p="parent" />
                    <array p="conditions">array()</array>
                    <array p="group">array()</array>
                    <string p="glueOp" value="AND"/>
                </object>
            </object>';

            $this->assertComplexIdenticalStr($cond, $check);


            $cond=new jDaoConditions();
            $cond->addItemOrder('foo', 'DESC');
            $check='<?xml version="1.0"?>
            <object class="jDaoConditions">
                <array p="order">array("foo"=>"DESC")</array>
                <boolean m="isEmpty()" value="false" />
                <object p="condition" class="jDaoCondition">
                    <null p="parent" />
                    <array p="conditions">array()</array>
                    <array p="group">array()</array>
                    <string p="glueOp" value="AND"/>
                </object>
            </object>';

            $this->assertComplexIdenticalStr($cond, $check);


            $cond=new jDaoConditions();
            $cond->addCondition('foo', '=', 'toto', 'LOWER(%s)');

            $check='<?xml version="1.0"?>
            <object class="jDaoConditions">
                <array p="order">array()</array>
                <boolean m="isEmpty()" value="false" />
                <object p="condition" class="jDaoCondition">
                    <null p="parent" />
                    <array p="conditions">array(array("field_id"=>"foo","field_pattern"=>"LOWER(%s)","value"=>"toto", "operator"=>"=", "isExpr"=>false))</array>
                    <array p="group">array()</array>
                    <string p="glueOp" value="AND"/>
                </object>
            </object>';


            $cond=new jDaoConditions();
            $cond->addCondition('foo', '=', 'toto', 'LOWER(%s)', false);

            $check='<?xml version="1.0"?>
            <object class="jDaoConditions">
                <array p="order">array()</array>
                <boolean m="isEmpty()" value="false" />
                <object p="condition" class="jDaoCondition">
                    <null p="parent" />
                    <array p="conditions">array(array("field_id"=>"foo","field_pattern"=>"LOWER(%s)","value"=>"toto", "operator"=>"=", "isExpr"=>false))</array>
                    <array p="group">array()</array>
                    <string p="glueOp" value="AND"/>
                </object>
            </object>';

            $this->assertComplexIdenticalStr($cond, $check);


            $cond=new jDaoConditions();
            $cond->addCondition('foo', '=', 'toto', '%s', false);

            $check='<?xml version="1.0"?>
            <object class="jDaoConditions">
                <array p="order">array()</array>
                <boolean m="isEmpty()" value="false" />
                <object p="condition" class="jDaoCondition">
                    <null p="parent" />
                    <array p="conditions">array(array("field_id"=>"foo","field_pattern"=>"%s","value"=>"toto", "operator"=>"=", "isExpr"=>false))</array>
                    <array p="group">array()</array>
                    <string p="glueOp" value="AND"/>
                </object>
            </object>';

            $this->assertComplexIdenticalStr($cond, $check);


            $cond->startGroup('OR');
            $cond->addCondition('foo1', '<', '100');
            $cond->addCondition('foo1', '>', '0');
            $cond->endGroup ();
            $check='<?xml version="1.0"?>
            <object class="jDaoConditions">
                <array p="order">array()</array>
                <boolean m="isEmpty()" value="false" />
                <object p="condition" class="jDaoCondition">
                    <null p="parent" />
                    <array p="conditions">array(array("field_id"=>"foo","field_pattern"=>"%s","value"=>"toto", "operator"=>"=", "isExpr"=>false))</array>
                    <array p="group">
                        <object p="condition" class="jDaoCondition">
                            <object p="parent" class="jDaoCondition" />
                            <array p="conditions">array(
                             array("field_id"=>"foo1","field_pattern"=>"%s","value"=>"100", "operator"=>"&lt;", "isExpr"=>false),
                             array("field_id"=>"foo1","field_pattern"=>"%s","value"=>"0", "operator"=>"&gt;", "isExpr"=>false))</array>
                            <array p="group">array()</array>
                            <string p="glueOp" value="OR"/>
                        </object>
                    </array>
                    <string p="glueOp" value="AND"/>
                </object>
            </object>';

            $this->assertComplexIdenticalStr($cond, $check);


            $cond=new jDaoConditions();
            $cond->addCondition('foo', '=', 'toto', 'LOWER(%s)', false);

            $check='<?xml version="1.0"?>
            <object class="jDaoConditions">
                <array p="order">array()</array>
                <boolean m="isEmpty()" value="false" />
                <object p="condition" class="jDaoCondition">
                    <null p="parent" />
                    <array p="conditions">array(array("field_id"=>"foo","field_pattern"=>"LOWER(%s)","value"=>"toto", "operator"=>"=", "isExpr"=>false))</array>
                    <array p="group">array()</array>
                    <string p="glueOp" value="AND"/>
                </object>
            </object>';

            $this->assertComplexIdenticalStr($cond, $check);


            $cond->startGroup('OR');
            $cond->addCondition('foo1', '<', '100', 'ROUND(%s)');
            $cond->addCondition('foo1', '>', '0', 'CEIL(%s)');
            $cond->endGroup ();
            $check='<?xml version="1.0"?>
            <object class="jDaoConditions">
                <array p="order">array()</array>
                <boolean m="isEmpty()" value="false" />
                <object p="condition" class="jDaoCondition">
                    <null p="parent" />
                    <array p="conditions">array(array("field_id"=>"foo","field_pattern"=>"LOWER(%s)","value"=>"toto", "operator"=>"=", "isExpr"=>false))</array>
                    <array p="group">
                        <object p="condition" class="jDaoCondition">
                            <object p="parent" class="jDaoCondition" />
                            <array p="conditions">array(
                             array("field_id"=>"foo1","field_pattern"=>"ROUND(%s)","value"=>"100", "operator"=>"&lt;", "isExpr"=>false),
                             array("field_id"=>"foo1","field_pattern"=>"CEIL(%s)","value"=>"0", "operator"=>"&gt;", "isExpr"=>false))</array>
                            <array p="group">array()</array>
                            <string p="glueOp" value="OR"/>
                        </object>
                    </array>
                    <string p="glueOp" value="AND"/>
                </object>
            </object>';

            $this->assertComplexIdenticalStr($cond, $check);

        }catch(jDaoXmlException $e){
            $this->fail("Exception sur le contenu xml inattendue : ".$e->getMessage().' ('.$e->getLocaleKey().')');
        }catch(Exception $e){
            $this->fail("Exception inconnue : ".$e->getMessage());
        }
    }

    function testEmptyRecursive(){
        $cond = new jDaoConditions();
        $cond->startGroup();
        $cond->startGroup('OR');
        $cond->endGroup();
        $cond->endGroup();
        $this->assertFalse($cond->hasConditions());
        $this->assertTrue($cond->isEmpty());
    }

    function testNonEmptyRecursive(){
        $cond = new jDaoConditions();
        $cond->startGroup();
        $cond->startGroup('OR');
        $cond->addCondition('test','=',1);
        $cond->endGroup();
        $cond->endGroup();
        $this->assertTrue($cond->hasConditions());
        $this->assertFalse($cond->isEmpty());
    }
}