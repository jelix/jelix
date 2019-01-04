<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Laurent Jouanneau
* @contributor
* @copyright   2006-2007 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

require_once(JELIX_LIB_PATH.'dao/jDaoCompiler.class.php');
require_once(JELIX_LIB_PATH.'dao/jDaoConditions.class.php');


class jdao_eventsTest extends jUnitTestCaseDb {

    function setUp() {
        self::initJelixConfig();
        jApp::pushCurrentModule('jelix_tests');
        $GLOBALS['TEST_DAO_EVENTS'] = array();
    }

    function tearDown() {
        jApp::popCurrentModule();
        unset($GLOBALS['TEST_DAO_EVENTS']);
    }

    function testEvents() {
        global $TEST_DAO_EVENTS;

        $this->emptyTable('product_test');

        $dao = jDao::get ('products_events');

        $prod1 = $dao->createRecord();
        $prod1->name ='assiette';
        $prod1->price = 3.87;


        $prod2 = $dao->createRecord();
        $prod2->name ='assiette';
        $prod2->price = 3.87;

        //$prod2 = clone $prod1;

        $res = $dao->insert($prod2);

        $this->assertTrue(isset($TEST_DAO_EVENTS['onDaoInsertBefore']));
        $this->assertTrue(isset($TEST_DAO_EVENTS['onDaoInsertAfter']));

        $this->assertEquals('jelix_tests~products_events', $TEST_DAO_EVENTS['onDaoInsertBefore']['dao']);
        $this->assertEquals($prod1, $TEST_DAO_EVENTS['onDaoInsertBefore']['record']);

        $this->assertEquals('jelix_tests~products_events', $TEST_DAO_EVENTS['onDaoInsertAfter']['dao']);
        $this->assertEquals($prod2, $TEST_DAO_EVENTS['onDaoInsertAfter']['record']);


        $prod2->name='nouvelle assiette';
        $prod = $dao->update($prod2);

        $this->assertTrue(isset($TEST_DAO_EVENTS['onDaoUpdateBefore']));
        $this->assertTrue(isset($TEST_DAO_EVENTS['onDaoUpdateAfter']));

        $this->assertEquals('jelix_tests~products_events', $TEST_DAO_EVENTS['onDaoUpdateBefore']['dao']);
        $this->assertEquals($prod2, $TEST_DAO_EVENTS['onDaoUpdateBefore']['record']);

        $this->assertEquals('jelix_tests~products_events', $TEST_DAO_EVENTS['onDaoUpdateAfter']['dao']);
        $this->assertEquals($prod2, $TEST_DAO_EVENTS['onDaoUpdateAfter']['record']);


        $dao->delete(0); // unexistant id

        $this->assertTrue(isset($TEST_DAO_EVENTS['onDaoDeleteBefore']));
        $this->assertTrue(isset($TEST_DAO_EVENTS['onDaoDeleteAfter']));

        $this->assertEquals('jelix_tests~products_events', $TEST_DAO_EVENTS['onDaoDeleteBefore']['dao']);
        $this->assertEquals(array('id'=>0), $TEST_DAO_EVENTS['onDaoDeleteBefore']['keys']);

        $this->assertEquals('jelix_tests~products_events', $TEST_DAO_EVENTS['onDaoDeleteAfter']['dao']);
        $this->assertEquals(array('id'=>0), $TEST_DAO_EVENTS['onDaoDeleteAfter']['keys']);
        $this->assertEquals(0, $TEST_DAO_EVENTS['onDaoDeleteAfter']['result']);

        $dao->delete($prod2->id); 

        $this->assertTrue(isset($TEST_DAO_EVENTS['onDaoDeleteBefore']));
        $this->assertTrue(isset($TEST_DAO_EVENTS['onDaoDeleteAfter']));

        $this->assertEquals('jelix_tests~products_events', $TEST_DAO_EVENTS['onDaoDeleteBefore']['dao']);
        $this->assertEquals(array('id'=>$prod2->id), $TEST_DAO_EVENTS['onDaoDeleteBefore']['keys']);

        $this->assertEquals('jelix_tests~products_events', $TEST_DAO_EVENTS['onDaoDeleteAfter']['dao']);
        $this->assertEquals(array('id'=>$prod2->id), $TEST_DAO_EVENTS['onDaoDeleteAfter']['keys']);
        $this->assertEquals(1, $TEST_DAO_EVENTS['onDaoDeleteAfter']['result']);

        $conditions = jDao::createConditions();
        $conditions->addCondition ('id', '=', $prod2->id);

        $dao->deleteBy($conditions); 

        $this->assertTrue(isset($TEST_DAO_EVENTS['onDaoDeleteByBefore']));
        $this->assertTrue(isset($TEST_DAO_EVENTS['onDaoDeleteByAfter']));

        $this->assertEquals('jelix_tests~products_events', $TEST_DAO_EVENTS['onDaoDeleteByBefore']['dao']);
        $this->assertEquals($conditions, $TEST_DAO_EVENTS['onDaoDeleteByBefore']['criterias']);

        $this->assertEquals('jelix_tests~products_events', $TEST_DAO_EVENTS['onDaoDeleteByAfter']['dao']);
        $this->assertEquals(0, $TEST_DAO_EVENTS['onDaoDeleteByAfter']['result']);
        $this->assertEquals($conditions, $TEST_DAO_EVENTS['onDaoDeleteByAfter']['criterias']);
    }

}
