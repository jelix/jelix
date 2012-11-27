<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Laurent Jouanneau
* @contributor
* @copyright   2007 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

/**
 * CAREFULL ! DON'T CHANGE THE ORDER OF METHODS
 */
abstract class UTDao_base extends jUnitTestCaseDb {

    function setUpRun() {
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
        $this->assertEqual($record->lang , 'fr');

        $record->setPk(5,'es');
        $this->assertEqual($record->id , 5);
        $this->assertEqual($record->lang , 'es');

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
        $this->prod1->promo = false;
        $res = $dao->insert($this->prod1);

        $this->assertEqual($res, 1, 'jDaoBase::insert does not return 1');
        $this->assertNotEqual($this->prod1->id, '', 'jDaoBase::insert : id not set');
        $this->assertNotEqual($this->prod1->create_date, '', 'jDaoBase::insert : create_date not updated');

        $this->prod2 = jDao::createRecord ('products');
        $this->prod2->name ='fourchette';
        $this->prod2->price = 1.54;
        $this->prod2->promo = true;
        $this->prod2->dummy = 'started';
        $res = $this->prod2->save();

        $this->assertEqual($res, 1, 'jDaoBase::insert does not return 1');
        $this->assertNotEqual($this->prod2->id, '', 'jDaoBase::insert : id not set');
        $this->assertNotEqual($this->prod2->create_date, '', 'jDaoBase::insert : create_date not updated');

        $this->prod3 = jDao::createRecord ('products');
        $this->prod3->name ='verre';
        $this->prod3->price = 2.43;
        $this->prod3->promo = false;
        $res = $dao->insert($this->prod3);

        $this->assertEqual($res, 1, 'jDaoBase::insert does not return 1');
        $this->assertNotEqual($this->prod3->id, '', 'jDaoBase::insert : id not set');
        $this->assertNotEqual($this->prod3->create_date, '', 'jDaoBase::insert : create_date not updated');

        $this->records = array(
            array('id'=>$this->prod1->id,
            'name'=>'assiette',
            'price'=>3.87,
            'promo'=>0),
            array('id'=>$this->prod2->id,
            'name'=>'fourchette',
            'price'=>1.54,
            'promo'=>1),
            array('id'=>$this->prod3->id,
            'name'=>'verre',
            'price'=>2.43,
            'promo'=>0),
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
        $this->assertEqual($prod->promo,0, 'jDao::get : bad promo property on record');
    }

    function testUpdate(){
        $dao = jDao::create ('products');
        $prod = jDao::createRecord ('products');
        $prod->name ='assiette nouvelle';
        $prod->price = 5.90;
        $prod->promo = true;
        $prod->id = $this->prod1->id;

        $dao->update($prod);

        $prod2 = $dao->get($this->prod1->id);
        $this->assertTrue($prod2 instanceof jDaoRecordBase,'jDao::get doesn\'t return a jDaoRecordBase object');
        $this->assertEqual($prod2->id, $this->prod1->id, 'jDao::get : bad id on record');
        $this->assertEqual($prod2->name,'assiette nouvelle', 'jDao::get : bad name property on record');
        $this->assertEqual($prod2->price,5.90, 'jDao::get : bad price property on record');
        $this->assertEqual($prod2->promo,1, 'jDao::get : bad promo property on record');
        
        $prod->promo = 't';
        $prod->save();
        $prod2 = $dao->get($this->prod1->id);
        $this->assertEqual($prod2->promo,1, 'jDao::get : bad promo property on record : %');
        
        $prod->promo = 'f';
        $dao->update($prod);
        $prod2 = $dao->get($this->prod1->id);
        $this->assertEqual($prod2->promo,0, 'jDao::get : bad promo property on record : %');

        $prod->promo = false;
        $dao->update($prod);
        $prod2 = $dao->get($this->prod1->id);
        $this->assertEqual($prod2->promo,0, 'jDao::get : bad promo property on record : ');

        $prod->promo = 'true';
        $dao->update($prod);
        $prod2 = $dao->get($this->prod1->id);
        $this->assertEqual($prod2->promo,1, 'jDao::get : bad promo property on record : %');

        $prod->promo = 'on';
        $dao->update($prod);
        $prod2 = $dao->get($this->prod1->id);
        $this->assertEqual($prod2->promo,1, 'jDao::get : bad promo property on record : %');

        $prod->promo = 'false';
        $dao->update($prod);
        $prod2 = $dao->get($this->prod1->id);
        $this->assertEqual($prod2->promo,0, 'jDao::get : bad promo property on record : %');

        $prod->promo = 0;
        $dao->update($prod);
        $prod2 = $dao->get($this->prod1->id);
        $this->assertEqual($prod2->promo,0, 'jDao::get : bad promo property on record : '.var_export($prod2->promo,true).' ');

        $prod->promo = 1;
        $dao->update($prod);
        $prod2 = $dao->get($this->prod1->id);
        $this->assertEqual($prod2->promo,1, 'jDao::get : bad promo property on record : %');

        $prod->promo = '0';
        $dao->update($prod);
        $prod2 = $dao->get($this->prod1->id);
        $this->assertEqual($prod2->promo,0, 'jDao::get : bad promo property on record : %');

        $prod->promo = '1';
        $dao->update($prod);
        $prod2 = $dao->get($this->prod1->id);
        $this->assertEqual($prod2->promo,1, 'jDao::get : bad promo property on record : %');

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
        <string property="promo" value="1" />
    </object>
    <object>
        <string property="id" value="'.$this->prod2->id.'" />
        <string property="name" value="fourchette" />
        <string property="price" value="1.54" />
        <string property="promo" value="1" />
    </object>
    <object>
        <string property="id" value="'.$this->prod3->id.'" />
        <string property="name" value="verre" />
        <string property="price" value="2.43" />
        <string property="promo" value="0" />
    </object>
</array>';
        $this->assertComplexIdenticalStr($list, $verif);
    }

    function testEqualityOnValue() {
        $dao = jDao::create ('products');

        $res = $dao->findFourchette();
        $list = array();
        foreach($res as $r){
            $list[] = $r;
        }
        $this->assertEqual(count($list), 1, 'findFourchette doesn\'t return one record. %s ');

    $verif='<array>
    <object>
        <string property="id" value="'.$this->prod2->id.'" />
        <string property="name" value="fourchette" />
        <string property="price" value="1.54" />
        <string property="promo" value="1" />
    </object>
</array>';
        $this->assertComplexIdenticalStr($list, $verif);


        $res = $dao->findStarted();
        $list = array();
        foreach($res as $r){
            $list[] = $r;
        }
        $this->assertEqual(count($list), 1, 'findStarted doesn\'t return one record. %s ');

    $verif='<array>
    <object>
        <string property="id" value="'.$this->prod2->id.'" />
        <string property="name" value="fourchette" />
        <string property="price" value="1.54" />
        <string property="promo" value="1" />
        <string property="dummy" value="started" />
    </object>
</array>';
        $this->assertComplexIdenticalStr($list, $verif);
    }

    function testFindByCountByOrder(){
        $dao = jDao::create ('products');

        $conditions = jDao::createConditions();
        $conditions->addItemOrder('id','DESC');

        $count = $dao->countBy($conditions);
        $this->assertEqual($count, 3, 'countBy: %s');

        $res = $dao->findBy($conditions);
        $list = array();
        foreach($res as $r){
            $list[] = $r;
        }
        $this->assertEqual(count($list), 3, 'findBy doesn\'t return all products. %s ');

        $verif='<array>
    <object>
        <string property="id" value="'.$this->prod3->id.'" />
        <string property="name" value="verre" />
        <string property="price" value="2.43" />
    </object>
    <object>
        <string property="id" value="'.$this->prod2->id.'" />
        <string property="name" value="fourchette" />
        <string property="price" value="1.54" />
    </object>
    <object>
        <string property="id" value="'.$this->prod1->id.'" />
        <string property="name" value="assiette nouvelle" />
        <string property="price" value="5.90" />
    </object>
</array>';
        $this->assertComplexIdenticalStr($list, $verif);
    }

    function testFindByCountByConditionsOrder(){
        $dao = jDao::create ('products');

        $conditions = jDao::createConditions();
        $conditions->addItemOrder('id','DESC');
        $conditions->addCondition ('id', '>=', $this->prod2->id);

        $count = $dao->countBy($conditions);
        $this->assertEqual($count, 2, 'countBy: %s');

        $res = $dao->findBy($conditions);
        $list = array();
        foreach($res as $r){
            $list[] = $r;
        }
        $this->assertEqual(count($list), 2, 'findBy doesn\'t return all products. %s ');

        $verif='<array>
    <object>
        <string property="id" value="'.$this->prod3->id.'" />
        <string property="name" value="verre" />
        <string property="price" value="2.43" />
    </object>
    <object>
        <string property="id" value="'.$this->prod2->id.'" />
        <string property="name" value="fourchette" />
        <string property="price" value="1.54" />
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

    function testDeleteBy(){
        $dao = jDao::create ('products');

        $conditions = jDao::createConditions();
        $conditions->addCondition ('id', '=', $this->prod2->id);

        $dao->deleteBy($conditions);
        $this->assertTrue($dao->countAll() == 1, 'countAll doesn\'t return 1');

        $this->records = array(
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
        $this->assertEqual(count($list), 1, 'findAll doesn\'t return all products. %s ');

    $verif='<array>
    <object>
        <string property="id" value="'.$this->prod3->id.'" />
        <string property="name" value="verre" />
        <string property="price" value="2.43" />
    </object>
</array>';
        $this->assertComplexIdenticalStr($list, $verif);
    }

    function testCheck() {

        $record = jDao::createRecord('products');
        $check = $record->check();
        $results = array('name'=>array(jDaoRecordBase::ERROR_REQUIRED));
        $this->assertEqual($results,$check);

        $record->name = 'Foo';
        $check = $record->check();
        $this->assertFalse($check);

        $record->create_date = 'foo';
        $check = $record->check();
        $results = array('create_date'=>array(jDaoRecordBase::ERROR_BAD_FORMAT));
        $this->assertEqual($results,$check);

        $record->create_date = '2008-02-15';
        $check = $record->check();
        $results = array('create_date'=>array(jDaoRecordBase::ERROR_BAD_FORMAT));
        $this->assertEqual($results,$check);

        $record->create_date = '2008-02-15 12:03:34';
        $check = $record->check();
        $this->assertFalse($check);

        $record->price='foo';
        $check = $record->check();
        $results = array('price'=>array(jDaoRecordBase::ERROR_BAD_TYPE));
        $this->assertEqual($results,$check);

        $record->price=56;
        $check = $record->check();
        $this->assertFalse($check);

    }

    function testBinaryField() {
        $this->emptyTable('jsessions');
        
        $dao = jDao::create ('jelix~jsession');

        $sess1 = jDao::createRecord ('jelix~jsession');
        $sess1->id ='sess_02939873A32B';
        $sess1->creation = '2010-02-09 10:28';
        $sess1->access = '2010-02-09 11:00';
        $sess1->data = chr(0).chr(254).chr(1);

        $res = $dao->insert($sess1);
        $this->assertEqual($res, 1, 'jDaoBase::insert does not return 1');

        $sess2 = $dao->get('sess_02939873A32B');
        $this->assertEqualOrDiff($sess2->id, $sess1->id, 'jDao::get : bad id on record');
        $this->assertEqual(bin2hex($sess2->data), bin2hex($sess1->data), 'jDao::get : bad binary value on record');
    } 
}
?>