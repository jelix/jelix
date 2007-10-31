<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Jouanneau Laurent
* @contributor
* @copyright   2007 Jouanneau laurent
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

/**
 * CAREFULL ! DON'T CHANGE THE ORDER OF METHODS
 */
class UTDao extends jUnitTestCaseDb {

    function testStart() {
        $this->emptyTable('product_test');
    }

    function testInstanciation() {
        $dao = jDao::create ('products');
        $this->assertTrue($dao instanceof jDaoFactoryBase,'jDao::create doesn\'t return a jDaoFactoryBase object');

        $dao = jDao::get ('products');
        $this->assertTrue($dao instanceof jDaoFactoryBase,'jDao::get doesn\'t return a jDaoFactoryBase object');

        $dao = jDao::createRecord ('products');
        $this->assertTrue($dao instanceof jDaoRecordBase,'jDao::createRecord doesn\'t return a jDaoRecordBase object');

    }

    function testRecordCheck() {

        $record = jDao::createRecord ('products');
        $this->assertEqual($record->id , '');
        $record->setPk(5);
        $this->assertEqual($record->id , 5);

        $this->assertEqual($record->getPk(), 5);
 
        $record = jDao::createRecord ('description');
        $this->assertEqual($record->id , '');
        $this->assertEqual($record->lang , '');

        $record->setPk(5,'fr');
        $this->assertEqual($record->id , 5);
        $this->assertEqual($record->lang , 'fr');

        $record->setPk(array(4,'en'));
        $this->assertEqual($record->id , 4);
        $this->assertEqual($record->lang , 'en');

        $pk = $record->getPk();
        $this->assertEqual($pk, array(4,'en'));
    }

    function testFindAllEmpty() {
        $dao = jDao::create ('products');
        $res = $dao->findAll();
        $list = array();
        foreach($res as $r){
            $list[] = $r;
        }
        $this->assertTrue(count($list) == 0, 'findAll doesn\'t return an empty list');
        $this->assertTrue($dao->countAll() == 0, 'countAll doesn\'t return 0');
    }

    protected $prod1;
    protected $prod2;
    protected $prod3;
    protected $records;

    function testInsert() {
        $dao = jDao::create ('products');

        $this->prod1 = jDao::createRecord ('products');
        $this->prod1->name ='assiette';
        $this->prod1->price = 3.87;
        $res = $dao->insert($this->prod1);

        $this->assertEqual($res, 1, 'jDaoBase::insert does not return 1');
        $this->assertNotEqual($this->prod1->id, '', 'jDaoBase::insert : id not set');
        $this->assertNotEqual($this->prod1->create_date, '', 'jDaoBase::insert : create_date not updated');

        $this->prod2 = jDao::createRecord ('products');
        $this->prod2->name ='fourchette';
        $this->prod2->price = 1.54;
        $res = $dao->insert($this->prod2);

        $this->assertEqual($res, 1, 'jDaoBase::insert does not return 1');
        $this->assertNotEqual($this->prod2->id, '', 'jDaoBase::insert : id not set');
        $this->assertNotEqual($this->prod2->create_date, '', 'jDaoBase::insert : create_date not updated');

        $this->prod3 = jDao::createRecord ('products');
        $this->prod3->name ='verre';
        $this->prod3->price = 2.43;
        $res = $dao->insert($this->prod3);

        $this->assertEqual($res, 1, 'jDaoBase::insert does not return 1');
        $this->assertNotEqual($this->prod3->id, '', 'jDaoBase::insert : id not set');
        $this->assertNotEqual($this->prod3->create_date, '', 'jDaoBase::insert : create_date not updated');

        $this->records = array(
            array('id'=>$this->prod1->id,
            'name'=>'assiette',
            'price'=>3.87),
            array('id'=>$this->prod2->id,
            'name'=>'fourchette',
            'price'=>1.54),
            array('id'=>$this->prod3->id,
            'name'=>'verre',
            'price'=>2.43),
        );
        $this->assertTableContainsRecords('product_test', $this->records);

    }

    function testGet() {
        $dao = jDao::create ('products');

        $prod = $dao->get($this->prod1->id);
        $this->assertTrue($prod instanceof jDaoRecordBase,'jDao::get doesn\'t return a jDaoRecordBase object');
        $this->assertEqual($prod->id, $this->prod1->id, 'jDao::get : bad id on record');
        $this->assertEqual($prod->name,'assiette', 'jDao::get : bad name property on record');
        $this->assertEqual($prod->price,3.87, 'jDao::get : bad price property on record');
    }

    function testUpdate(){
        $dao = jDao::create ('products');
        $prod = jDao::createRecord ('products');
        $prod->name ='assiette nouvelle';
        $prod->price = 5.90;
        $prod->id = $this->prod1->id;

        $prod = $dao->update($prod);
        
        $prod2 = $dao->get($this->prod1->id);
        $this->assertTrue($prod2 instanceof jDaoRecordBase,'jDao::get doesn\'t return a jDaoRecordBase object');
        $this->assertEqual($prod2->id, $this->prod1->id, 'jDao::get : bad id on record');
        $this->assertEqual($prod2->name,'assiette nouvelle', 'jDao::get : bad name property on record');
        $this->assertEqual($prod2->price,5.90, 'jDao::get : bad price property on record');

    }

    function testFindAllNotEmpty() {
        $dao = jDao::create ('products');

        $res = $dao->findAll();
        $list = array();
        foreach($res as $r){
            $list[] = $r;
        }
        $this->assertEqual(count($list), 3, 'findAll doesn\'t return all products. %s ');
        $this->assertTrue($dao->countAll() == 3, 'countAll doesn\'t return 3');

    $verif='<array>
    <object>
        <string property="id" value="'.$this->prod1->id.'" />
        <string property="name" value="assiette nouvelle" />
        <string property="price" value="5.90" />
    </object>
    <object>
        <string property="id" value="'.$this->prod2->id.'" />
        <string property="name" value="fourchette" />
        <string property="price" value="1.54" />
    </object>
    <object>
        <string property="id" value="'.$this->prod3->id.'" />
        <string property="name" value="verre" />
        <string property="price" value="2.43" />
    </object>
</array>';
        $this->assertComplexIdenticalStr($list, $verif);



    }

    function testDelete(){
        $dao = jDao::create ('products');
        $dao->delete($this->prod1->id);
        $this->assertTrue($dao->countAll() == 2, 'countAll doesn\'t return 2');

        $this->records = array(
            array('id'=>$this->prod2->id,
            'name'=>'fourchette',
            'price'=>1.54),
            array('id'=>$this->prod3->id,
            'name'=>'verre',
            'price'=>2.43),
        );
        $this->assertTableContainsRecords('product_test', $this->records);


        $res = $dao->findAll();
        $list = array();
        foreach($res as $r){
            $list[] = $r;
        }
        $this->assertEqual(count($list), 2, 'findAll doesn\'t return all products. %s ');

    $verif='<array>
    <object>
        <string property="id" value="'.$this->prod2->id.'" />
        <string property="name" value="fourchette" />
        <string property="price" value="1.54" />
    </object>
    <object>
        <string property="id" value="'.$this->prod3->id.'" />
        <string property="name" value="verre" />
        <string property="price" value="2.43" />
    </object>
</array>';
        $this->assertComplexIdenticalStr($list, $verif);

    }
}
?>