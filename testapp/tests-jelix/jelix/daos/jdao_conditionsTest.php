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


class jDao_ConditionsTest extends \Jelix\UnitTests\UnitTestCase {

    function testConditions() {

            $cond=new \Jelix\Dao\DaoConditions();

            $check='<?xml version="1.0"?>
            <object class="\Jelix\Dao\DaoConditions">
                <array p="order">[]</array>
                <boolean m="isEmpty()" value="true" />
                <object p="condition" class="\Jelix\Dao\DaoCondition">
                    <null p="parent" />
                    <array p="conditions">[]</array>
                    <array p="group">[]</array>
                    <string p="glueOp" value="AND"/>
                </object>
            </object>';

            $this->assertComplexIdenticalStr($cond, $check);


            $cond=new \Jelix\Dao\DaoConditions();
            $cond->addItemOrder('foo', 'DESC');
            $check='<?xml version="1.0"?>
            <object class="\Jelix\Dao\DaoConditions">
                <array p="order">{"foo":"DESC"}</array>
                <boolean m="isEmpty()" value="false" />
                <object p="condition" class="\Jelix\Dao\DaoCondition">
                    <null p="parent" />
                    <array p="conditions">[]</array>
                    <array p="group">[]</array>
                    <string p="glueOp" value="AND"/>
                </object>
            </object>';

            $this->assertComplexIdenticalStr($cond, $check);


            $cond=new \Jelix\Dao\DaoConditions();
            $cond->addCondition('foo', '=', 'toto', 'LOWER(%s)');

            $check='<?xml version="1.0"?>
            <object class="\Jelix\Dao\DaoConditions">
                <array p="order">[]</array>
                <boolean m="isEmpty()" value="false" />
                <object p="condition" class="\Jelix\Dao\DaoCondition">
                    <null p="parent" />
                    <array p="conditions">[
                    {"field_id":"foo","field_pattern":"LOWER(%s)","value":"toto", "operator":"=", "isExpr":false, "dbType":""}
                    ]</array>
                    <array p="group">[]</array>
                    <string p="glueOp" value="AND"/>
                </object>
            </object>';


            $cond=new \Jelix\Dao\DaoConditions();
            $cond->addCondition('foo', '=', 'toto', 'LOWER(%s)', false);

            $check='<?xml version="1.0"?>
            <object class="\Jelix\Dao\DaoConditions">
                <array p="order">[]</array>
                <boolean m="isEmpty()" value="false" />
                <object p="condition" class="\Jelix\Dao\DaoCondition">
                    <null p="parent" />
                    <array p="conditions">[
                    {"field_id":"foo","field_pattern":"LOWER(%s)","value":"toto", "operator":"=", "isExpr":false, "dbType":""}
                    ]</array>
                    <array p="group">[]</array>
                    <string p="glueOp" value="AND"/>
                </object>
            </object>';

            $this->assertComplexIdenticalStr($cond, $check);


            $cond=new \Jelix\Dao\DaoConditions();
            $cond->addCondition('foo', '=', 'toto', '%s', false);

            $check='<?xml version="1.0"?>
            <object class="\Jelix\Dao\DaoConditions">
                <array p="order">[]</array>
                <boolean m="isEmpty()" value="false" />
                <object p="condition" class="\Jelix\Dao\DaoCondition">
                    <null p="parent" />
                    <array p="conditions">[
                    {"field_id":"foo","field_pattern":"%s","value":"toto", "operator":"=", "isExpr":false, "dbType":""}
                    ]</array>
                    <array p="group">[]</array>
                    <string p="glueOp" value="AND"/>
                </object>
            </object>';

            $this->assertComplexIdenticalStr($cond, $check);


            $cond->startGroup('OR');
            $cond->addCondition('foo1', '<', '100');
            $cond->addCondition('foo1', '>', '0');
            $cond->endGroup ();
            $check='<?xml version="1.0"?>
            <object class="\Jelix\Dao\DaoConditions">
                <array p="order">[]</array>
                <boolean m="isEmpty()" value="false" />
                <object p="condition" class="\Jelix\Dao\DaoCondition">
                    <null p="parent" />
                    <array p="conditions">[
                    {"field_id":"foo","field_pattern":"%s","value":"toto", "operator":"=", "isExpr":false, "dbType":""}
                    ]</array>
                    <array p="group">
                        <object p="condition" class="\Jelix\Dao\DaoCondition">
                            <object p="parent" class="\Jelix\Dao\DaoCondition" />
                            <array p="conditions">[
                             {"field_id":"foo1","field_pattern":"%s","value":"100", "operator":"&lt;", "isExpr":false, "dbType":""},
                             {"field_id":"foo1","field_pattern":"%s","value":"0", "operator":"&gt;", "isExpr":false, "dbType":""}
                             ]</array>
                            <array p="group">[]</array>
                            <string p="glueOp" value="OR"/>
                        </object>
                    </array>
                    <string p="glueOp" value="AND"/>
                </object>
            </object>';

            $this->assertComplexIdenticalStr($cond, $check);


            $cond=new \Jelix\Dao\DaoConditions();
            $cond->addCondition('foo', '=', 'toto', 'LOWER(%s)', false);

            $check='<?xml version="1.0"?>
            <object class="\Jelix\Dao\DaoConditions">
                <array p="order">[]</array>
                <boolean m="isEmpty()" value="false" />
                <object p="condition" class="\Jelix\Dao\DaoCondition">
                    <null p="parent" />
                    <array p="conditions">
                    [
                    {"field_id":"foo","field_pattern":"LOWER(%s)","value":"toto", "operator":"=", "isExpr":false, "dbType":""}
                    ]</array>
                    <array p="group">[]</array>
                    <string p="glueOp" value="AND"/>
                </object>
            </object>';

            $this->assertComplexIdenticalStr($cond, $check);


            $cond->startGroup('OR');
            $cond->addCondition('foo1', '<', '100', 'ROUND(%s)');
            $cond->addCondition('foo1', '>', '0', 'CEIL(%s)');
            $cond->endGroup ();
            $check='<?xml version="1.0"?>
            <object class="\Jelix\Dao\DaoConditions">
                <array p="order">[]</array>
                <boolean m="isEmpty()" value="false" />
                <object p="condition" class="\Jelix\Dao\DaoCondition">
                    <null p="parent" />
                    <array p="conditions">[
                    {"field_id":"foo","field_pattern":"LOWER(%s)","value":"toto", "operator":"=", "isExpr":false, "dbType":""}
                    ]</array>
                    <array p="group">
                        <object p="condition" class="\Jelix\Dao\DaoCondition">
                            <object p="parent" class="\Jelix\Dao\DaoCondition" />
                            <array p="conditions">[
                                {"field_id":"foo1","field_pattern":"ROUND(%s)","value":"100", "operator":"&lt;", "isExpr":false, "dbType":""},
                                {"field_id":"foo1","field_pattern":"CEIL(%s)","value":"0", "operator":"&gt;", "isExpr":false, "dbType":""}
                            ]</array>
                            <array p="group">[]</array>
                            <string p="glueOp" value="OR"/>
                        </object>
                    </array>
                    <string p="glueOp" value="AND"/>
                </object>
            </object>';

            $this->assertComplexIdenticalStr($cond, $check);

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
