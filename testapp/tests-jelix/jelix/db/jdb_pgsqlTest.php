<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Laurent Jouanneau
* @contributor Julien Issler
* @copyright   2007-2009 Laurent Jouanneau
* @copyright   2009 Julien Issler
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

class jDb_PgsqlTest extends jUnitTestCaseDb {

    public static function setUpBeforeClass() {
        self::initJelixConfig();
    }
    protected $dbProfile = 'pgsql_profile';

    function setUp() {
        try{
            // check if we have profile
            $prof = jProfiles::get('jdb', $this->dbProfile, true);
            $this->emptyTable('product_test');
        }
        catch (Exception $e) {
            $this->markTestSkipped('jDb_PgsqlTest cannot be run: '.$e->getMessage());
        }
    }

    function testTools(){
        $tools = jDb::getConnection($this->dbProfile)->tools();

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

        $this->assertEquals(1, $res, 'jDaoBase::insert does not return 1');
        $this->assertNotEquals('', $this->prod1->id, 'jDaoBase::insert : id not set');
        $this->assertNotEquals('', $this->prod1->create_date, 'jDaoBase::insert : create_date not updated');

        $this->prod2 = jDao::createRecord ('products', $this->dbProfile);
        $this->prod2->name ='fourchette';
        $this->prod2->price = 1.54;
        $this->prod2->promo = true;
        $res = $dao->insert($this->prod2);

        $this->assertEquals(1, $res, 'jDaoBase::insert does not return 1');
        $this->assertNotEquals('', $this->prod2->id, 'jDaoBase::insert : id not set');
        $this->assertNotEquals('', $this->prod2->create_date, 'jDaoBase::insert : create_date not updated');

        $this->prod3 = jDao::createRecord ('products', $this->dbProfile);
        $this->prod3->name ='verre';
        $this->prod3->price = 2.43;
        $this->prod3->promo = false;
        $res = $dao->insert($this->prod3);

        $this->assertEquals(1, $res, 'jDaoBase::insert does not return 1');
        $this->assertNotEquals('', $this->prod3->id, 'jDaoBase::insert : id not set');
        $this->assertNotEquals('', $this->prod3->create_date, 'jDaoBase::insert : create_date not updated');

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
        $this->assertEquals($this->prod1->id, $prod->id, 'jDao::get : bad id on record');
        $this->assertEquals('assiette', $prod->name, 'jDao::get : bad name property on record');
        $this->assertEquals(3.87, $prod->price, 'jDao::get : bad price property on record');
        $this->assertEquals('f', $prod->promo, 'jDao::get : bad promo property on record');
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
        $this->assertEquals($this->prod1->id, $prod2->id, 'jDao::get : bad id on record');
        $this->assertEquals('assiette nouvelle', $prod2->name,'jDao::get : bad name property on record');
        $this->assertEquals(5.90, $prod2->price,'jDao::get : bad price property on record');
        $this->assertEquals('t', $prod2->promo,'jDao::get : bad promo property on record');


        $prod->promo = 't';
        $dao->update($prod);
        $prod2 = $dao->get($this->prod1->id);
        $this->assertEquals('t', $prod2->promo,'jDao::get : bad promo property on record : %');

        $prod->promo = 1;
        $dao->update($prod);
        $prod2 = $dao->get($this->prod1->id);
        $this->assertEquals('t', $prod2->promo, 'jDao::get : bad promo property on record : %');

        $prod->promo = 'f';
        $dao->update($prod);
        $prod2 = $dao->get($this->prod1->id);
        $this->assertEquals('f', $prod2->promo, 'jDao::get : bad promo property on record : %');

        $prod->promo = false;
        $dao->update($prod);
        $prod2 = $dao->get($this->prod1->id);
        $this->assertEquals('f',$prod2->promo, 'jDao::get : bad promo property on record : %');

        $prod->promo = 0;
        $dao->update($prod);
        $prod2 = $dao->get($this->prod1->id);
        $this->assertEquals('f', $prod2->promo, 'jDao::get : bad promo property on record : %');

    }

   function testBinaryField() {
        $this->emptyTable('jsessions');

        $dao = jDao::create ('jelix~jsession', $this->dbProfile);

        $sess1 = jDao::createRecord ('jelix~jsession', $this->dbProfile);
        $sess1->id ='sess_02939873A32B';
        $sess1->creation = '2010-02-09 10:28';
        $sess1->access = '2010-02-09 11:00';
        $sess1->data = chr(0).chr(254).chr(1);

        $res = $dao->insert($sess1);
        $this->assertEquals(1, $res, 'jDaoBase::insert does not return 1');

        $sess2 = $dao->get('sess_02939873A32B');

        $this->assertEquals($sess1->id, $sess2->id, 'jDao::get : bad id on record');
        $this->assertEquals(bin2hex($sess1->data), bin2hex($sess2->data), 'jDao::get : bad binary data');
    }

    function testFieldNameEnclosure(){
        $this->assertEquals('"toto"', jDb::getConnection($this->dbProfile)->encloseName('toto'));
    }
}
