<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Jouanneau Laurent
* @contributor Julien Issler
* @copyright   2007-2009 Jouanneau laurent
* @copyright   2009 Julien Issler
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

class UTjDbPgsql extends jUnitTestCaseDb {
    protected $dbProfile ='testapp_pgsql';

    function getTests(){
        try{
            $profile = jDb::getConnection($this->dbProfile);
        }
        catch (Exception $e) {
            $this->sendMessage('UTjDbPgsql cannot be run: '.$e->getMessage());
            return array();
        }
        return parent::getTests();
    }

    function testStart() {
        $this->emptyTable('product_test');
    }

    function testTools(){
        $tools = jDb::getTools($this->dbProfile);

        $fields = $tools->getFieldList('products');
        $structure = '<array>
    <object key="id" class="jDbFieldProperties">
        <string property="type" value="int" />
        <string property="name" value="id" />
        <boolean property="notNull" value="true" />
        <boolean property="primary" value="true" />
        <boolean property="autoIncrement" value="true" />
        <boolean property="hasDefault" value="true" />
        <string property="default" value="" />
        <integer property="length" value="0" />
    </object>
    <object key="name" class="jDbFieldProperties">
        <string property="type" value="varchar" />
        <string property="name" value="name" />
        <boolean property="notNull" value="true" />
        <boolean property="primary" value="false" />
        <boolean property="autoIncrement" value="false" />
        <boolean property="hasDefault" value="false" />
        <null property="default" />
        <integer property="length" value="150" />
    </object>
    <object key="price" class="jDbFieldProperties">
        <string property="type" value="float" />
        <string property="name" value="price" />
        <boolean property="notNull" value="false" />
        <boolean property="primary" value="false" />
        <boolean property="autoIncrement" value="false" />
        <boolean property="hasDefault" value="true" />
        <string property="default" value="0" />
        <integer property="length" value="0" />
    </object>
    <object key="promo" class="jDbFieldProperties">
        <string property="type" value="bool" />
        <string property="name" value="promo" />
        <boolean property="notNull" value="true" />
        <boolean property="primary" value="false" />
        <boolean property="autoIncrement" value="false" />
        <boolean property="hasDefault" value="false" />
        <null property="default" />
        <integer property="length" value="0" />
    </object>
</array>';
        $this->assertComplexIdenticalStr($fields, $structure, 'bad results');
    }

    function testInsert() {
        $dao = jDao::create ('products', $this->dbProfile);

        $this->prod1 = jDao::createRecord ('products', $this->dbProfile);
        $this->prod1->name ='assiette';
        $this->prod1->price = 3.87;
        $this->prod1->promo = false;
        $res = $dao->insert($this->prod1);

        $this->assertEqual($res, 1, 'jDaoBase::insert does not return 1');
        $this->assertNotEqual($this->prod1->id, '', 'jDaoBase::insert : id not set');
        $this->assertNotEqual($this->prod1->create_date, '', 'jDaoBase::insert : create_date not updated');

        $this->prod2 = jDao::createRecord ('products', $this->dbProfile);
        $this->prod2->name ='fourchette';
        $this->prod2->price = 1.54;
        $this->prod2->promo = true;
        $res = $dao->insert($this->prod2);

        $this->assertEqual($res, 1, 'jDaoBase::insert does not return 1');
        $this->assertNotEqual($this->prod2->id, '', 'jDaoBase::insert : id not set');
        $this->assertNotEqual($this->prod2->create_date, '', 'jDaoBase::insert : create_date not updated');

        $this->prod3 = jDao::createRecord ('products', $this->dbProfile);
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
            'promo'=>'f'),
            array('id'=>$this->prod2->id,
            'name'=>'fourchette',
            'price'=>1.54,
            'promo'=>'t'),
            array('id'=>$this->prod3->id,
            'name'=>'verre',
            'price'=>2.43,
            'promo'=>'f'),
        );
        $this->assertTableContainsRecords('product_test', $this->records);

    }

    function testGet() {
        $dao = jDao::create ('products', $this->dbProfile);

        $prod = $dao->get($this->prod1->id);
        $this->assertTrue($prod instanceof jDaoRecordBase,'jDao::get doesn\'t return a jDaoRecordBase object');
        $this->assertEqual($prod->id, $this->prod1->id, 'jDao::get : bad id on record');
        $this->assertEqual($prod->name,'assiette', 'jDao::get : bad name property on record');
        $this->assertEqual($prod->price,3.87, 'jDao::get : bad price property on record');
        $this->assertEqual($prod->promo,'f', 'jDao::get : bad promo property on record');
    }

    function testUpdate(){
        $dao = jDao::create ('products', $this->dbProfile);
        $prod = jDao::createRecord ('products', $this->dbProfile);
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
        $this->assertEqual($prod2->promo,'t', 'jDao::get : bad promo property on record');


        $prod->promo = 't';
        $dao->update($prod);
        $prod2 = $dao->get($this->prod1->id);
        $this->assertEqual($prod2->promo,'t', 'jDao::get : bad promo property on record : %');

        $prod->promo = 1;
        $dao->update($prod);
        $prod2 = $dao->get($this->prod1->id);
        $this->assertEqual($prod2->promo,'t', 'jDao::get : bad promo property on record : %');

        $prod->promo = 'f';
        $dao->update($prod);
        $prod2 = $dao->get($this->prod1->id);
        $this->assertEqual($prod2->promo,'f', 'jDao::get : bad promo property on record : %');

        $prod->promo = false;
        $dao->update($prod);
        $prod2 = $dao->get($this->prod1->id);
        $this->assertEqual($prod2->promo,'f', 'jDao::get : bad promo property on record : %');

        $prod->promo = 0;
        $dao->update($prod);
        $prod2 = $dao->get($this->prod1->id);
        $this->assertEqual($prod2->promo,'f', 'jDao::get : bad promo property on record : %');

    }

    function testFieldNameEnclosure(){
        $this->assertEqualOrDiff(jDb::getConnection($this->dbProfile)->encloseFieldName('toto'),'"toto"');
    }

}

?>