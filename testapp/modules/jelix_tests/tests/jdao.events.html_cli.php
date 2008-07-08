<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Jouanneau Laurent
* @contributor
* @copyright   2006-2007 Jouanneau laurent
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

require_once(JELIX_LIB_PATH.'dao/jDaoCompiler.class.php');
require_once(JELIX_LIB_PATH.'dao/jDaoConditions.class.php');

$GLOBALS['TEST_DAO_EVENTS'] = array();

class UTDao_Events extends jUnitTestCaseDb {


    function testEvents() {
        global $TEST_DAO_EVENTS;
        $TEST_DAO_EVENTS = array();
        $this->emptyTable('product_test');

        $dao = jDao::get ('products_events');

        $prod1 = jDao::createRecord ('products_events');
        $prod1->name ='assiette';
        $prod1->price = 3.87;


        $prod2 = jDao::createRecord ('products_events');
        $prod2->name ='assiette';
        $prod2->price = 3.87;

        //$prod2 = clone $prod1;

        $res = $dao->insert($prod2);

        $this->assertTrue(isset($TEST_DAO_EVENTS['onDaoInsertBefore']));
        $this->assertTrue(isset($TEST_DAO_EVENTS['onDaoInsertAfter']));

        $this->assertEqual($TEST_DAO_EVENTS['onDaoInsertBefore']['dao'] , 'jelix_tests~products_events');
        $this->assertEqual($TEST_DAO_EVENTS['onDaoInsertBefore']['record'] , $prod1);

        $this->assertEqual($TEST_DAO_EVENTS['onDaoInsertAfter']['dao'] , 'jelix_tests~products_events');
        $this->assertEqual($TEST_DAO_EVENTS['onDaoInsertAfter']['record'] , $prod2);


        $prod2->name='nouvelle assiette';
        $prod = $dao->update($prod2);

        $this->assertTrue(isset($TEST_DAO_EVENTS['onDaoUpdateBefore']));
        $this->assertTrue(isset($TEST_DAO_EVENTS['onDaoUpdateAfter']));

        $this->assertEqual($TEST_DAO_EVENTS['onDaoUpdateBefore']['dao'] , 'jelix_tests~products_events');
        $this->assertEqual($TEST_DAO_EVENTS['onDaoUpdateBefore']['record'] , $prod2);

        $this->assertEqual($TEST_DAO_EVENTS['onDaoUpdateAfter']['dao'] , 'jelix_tests~products_events');
        $this->assertEqual($TEST_DAO_EVENTS['onDaoUpdateAfter']['record'] , $prod2);


        $dao->delete(0); // unexistant id

        $this->assertTrue(isset($TEST_DAO_EVENTS['onDaoDeleteBefore']));
        $this->assertTrue(isset($TEST_DAO_EVENTS['onDaoDeleteAfter']));

        $this->assertEqual($TEST_DAO_EVENTS['onDaoDeleteBefore']['dao'] , 'jelix_tests~products_events');
        $this->assertEqual($TEST_DAO_EVENTS['onDaoDeleteBefore']['keys'] , array('id'=>0));

        $this->assertEqual($TEST_DAO_EVENTS['onDaoDeleteAfter']['dao'] , 'jelix_tests~products_events');
        $this->assertEqual($TEST_DAO_EVENTS['onDaoDeleteAfter']['keys'] , array('id'=>0));
        $this->assertEqual($TEST_DAO_EVENTS['onDaoDeleteAfter']['result'] , 0);

        $dao->delete($prod2->id); 

        $this->assertTrue(isset($TEST_DAO_EVENTS['onDaoDeleteBefore']));
        $this->assertTrue(isset($TEST_DAO_EVENTS['onDaoDeleteAfter']));

        $this->assertEqual($TEST_DAO_EVENTS['onDaoDeleteBefore']['dao'] , 'jelix_tests~products_events');
        $this->assertEqual($TEST_DAO_EVENTS['onDaoDeleteBefore']['keys'] , array('id'=>$prod2->id));

        $this->assertEqual($TEST_DAO_EVENTS['onDaoDeleteAfter']['dao'] , 'jelix_tests~products_events');
        $this->assertEqual($TEST_DAO_EVENTS['onDaoDeleteAfter']['keys'] , array('id'=>$prod2->id));
        $this->assertEqual($TEST_DAO_EVENTS['onDaoDeleteAfter']['result'] , 1);

        $conditions = jDao::createConditions();
        $conditions->addCondition ('id', '=', $prod2->id);

        $dao->deleteBy($conditions); 

        $this->assertTrue(isset($TEST_DAO_EVENTS['onDaoDeleteByBefore']));
        $this->assertTrue(isset($TEST_DAO_EVENTS['onDaoDeleteByAfter']));

        $this->assertEqual($TEST_DAO_EVENTS['onDaoDeleteByBefore']['dao'] , 'jelix_tests~products_events');
        $this->assertEqual($TEST_DAO_EVENTS['onDaoDeleteByBefore']['criterias'] , $conditions);

        $this->assertEqual($TEST_DAO_EVENTS['onDaoDeleteByAfter']['dao'] , 'jelix_tests~products_events');
        $this->assertEqual($TEST_DAO_EVENTS['onDaoDeleteByAfter']['result'] , 0);
        $this->assertEqual($TEST_DAO_EVENTS['onDaoDeleteByAfter']['criterias'] , $conditions);
    }

}
