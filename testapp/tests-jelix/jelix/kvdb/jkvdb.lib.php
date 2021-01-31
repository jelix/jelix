<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Tahina Ramaroson
* @contributor Sylvain de Vathaire, Laurent Jouanneau
* @copyright   NEOV 2009, 2010 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

/**
* Tests API jKVDb
* @package     testapp
* @subpackage  jelix_tests module
*/

abstract class jKVDbTest extends jUnitTestCaseDb {

    protected $profile;

    protected $mmc;
    
    protected $profileData;

    protected $supportTTL = true;

    protected function _kvdbSetUp() {
        try {
            $this->profileData = jProfiles::get('jkvdb', $this->profile);
            return true;
        }
        catch(Exception $e) {
            $this->markTestSkipped(get_class($this).' cannot be run with '.$this->profile.' : undefined profile');
            return false;
        }
    }

    public function testSetGet() {

        $myData=(object)array(
            'id'=>1,
            'content'=>'Lorem ipsum dolor sit amét, conséctetuer adipiscing elit. Donec at odio vitae libero tempus convallis. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Vestibulum purus mauris, dapibus eu, sagittis quis, sagittis quis, mi. Morbi fringilla massa quis velit. Curabitur metus massa, semper mollis, molestie vel, adipiscing nec, massa. Phasellus vitae felis sed lectus dapibus facilisis. In ultrices sagittis ipsum. In at est. Integer iaculis turpis vel magna. Cras eu est. Integer porttitor ligula a tellus. Curabitur accumsan ipsum a velit. Sed laoreet lectus quis leo. Nulla pellentesque molestie ante. Quisque vestibulum est id justo. Ut pellentesque ante in neque.'
        );
        $myObj=(object)array('property1'=>'string','property2'=>'integer');
        $img = @imagecreate(100,100);

        $kv = jKVDb::getConnection($this->profile);

        $this->assertTrue($kv->set('noExpireKey',$myData));
        $this->assertEquals($myData, $kv->get('noExpireKey'));

        $this->assertTrue($kv->set('noExpireKeyObj',$myObj));
        $this->assertEquals($myObj, $kv->get('noExpireKeyObj'));

        if ($this->supportTTL) {
            $this->assertTrue($kv->setWithTtl('expiredKey','data expired', strtotime("-1 year")));
            $this->assertNull($kv->get('expiredKey'));
    
            $this->assertTrue($kv->setWithTtl('ttlInSecondesKey', $myObj, 30));
            $this->assertEquals($myObj, $kv->get('ttlInSecondesKey'));
        }

        $this->assertFalse($kv->set('unableToSerializeDataKey',$img));
    }

    public function testMultipleGet (){
        $kv = jKVDb::getConnection($this->profile);
        $kv->set('getKey', 'string for data');
        if ($this->supportTTL)
            $kv->setWithTtl('expiredKey','data expired',strtotime("-1 day"));
        $data = $kv->get(array('getKey','expiredKey','inexistentKey'));

        $this->assertTrue(isset($data['getKey']));
        $this->assertEquals('string for data', $data['getKey']);
        $this->assertFalse(isset($data['expiredKey']));
        $this->assertFalse(isset($data['inexistentKey']));
    }

    public function testInsertReplace () {

        $kv = jKVDb::getConnection($this->profile);

        $this->assertNull($kv->get('unknowkey'));
        $this->assertFalse($kv->replace('unknowkey','new value'));
        $this->assertNull($kv->get('unknowkey'));

        $this->assertTrue($kv->set('existingKey', 'a value'));
        $this->assertFalse($kv->insert('existingKey','new value'));
        $this->assertEquals('a value', $kv->get('existingKey'));
        $this->assertTrue($kv->replace('existingKey','new value'));
        $this->assertEquals('new value', $kv->get('existingKey'));
    }

    public function testAppendPrepend() {

        $kv = jKVDb::getConnection($this->profile);

        $this->assertNull($kv->get('unknowkey'));
        $this->assertFalse($kv->append('unknowkey','new value'));
        $this->assertFalse($kv->prepend('unknowkey','new value'));
        $this->assertNull($kv->get('unknowkey'));

        $this->assertTrue($kv->set('existingKey', 'a value'));
        $this->assertEquals('a value', $kv->get('existingKey'));
        if ($this->profile == 'usingfile') {
            $this->assertEquals(serialize('a value'),file_get_contents(jApp::tempPath().'kvfiles/tests/a2/1d/a21d88063ed27afccd86342a31c8be60_existingKey'));
        }

        $this->assertEquals('a value_suffix', $kv->append('existingKey','_suffix'));
        if ($this->profile == 'usingfile') {
            $this->assertEquals(serialize('a value_suffix'), file_get_contents(jApp::tempPath().'kvfiles/tests/a2/1d/a21d88063ed27afccd86342a31c8be60_existingKey'));
        }

        $this->assertEquals('a value_suffix', $kv->get('existingKey'));

        $this->assertEquals('prefix_a value_suffix', $kv->prepend('existingKey','prefix_'));
        $this->assertEquals('prefix_a value_suffix', $kv->get('existingKey'));
        if ($this->profile == 'usingfile') {
            $this->assertEquals( serialize('prefix_a value_suffix'), file_get_contents(jApp::tempPath().'kvfiles/tests/a2/1d/a21d88063ed27afccd86342a31c8be60_existingKey'));
        }
    }

    
    public function testIncrement() {

        $kv = jKVDb::getConnection($this->profile);

        $this->assertFalse($kv->increment('InexistentKey',1));

        $this->assertTrue($kv->set('integerDataKey', 100));
        $this->assertEquals(101, $kv->increment('integerDataKey'));

        $this->assertTrue($kv->set('floatDataKey', 100.5));
        $this->assertEquals(100.5, $kv->get('floatDataKey'));
        $this->assertEquals(101, $kv->increment('floatDataKey',1));
       
        $this->assertTrue($kv->set('floatIncrementationKey',100));
        $this->assertEquals(101, $kv->increment('floatIncrementationKey',1.5));

        $this->assertTrue($kv->set('stringIncrementationKey',1));
        $this->assertFalse($kv->increment('stringIncrementationKey','increment by string'));

        $this->assertTrue($kv->set('stringDataKey','string data'));
        $this->assertFalse($kv->increment('stringDataKey',100));

        $this->assertTrue($kv->set('arrayDataKey',array(1)));
        $this->assertFalse($kv->increment('arrayDataKey',1));

        $oData=(object)array('property1'=>'string','property2'=>1);
        $this->assertTrue($kv->set('objectDataKey',$oData));
        $this->assertFalse($kv->increment('objectDataKey',1));

    }

    public function testDecrement (){

        $kv = jKVDb::getConnection($this->profile);

        $this->assertFalse($kv->decrement('InexistentKey',1));

        $this->assertTrue($kv->set('integerDataKey',100));
        $this->assertEquals(99, $kv->decrement('integerDataKey',1));

        $this->assertTrue($kv->set('floatDataKey',100.5));
        $this->assertEquals(99, $kv->decrement('floatDataKey',1));

        $this->assertTrue($kv->set('floatDecrementationKey',100));
        $this->assertEquals(99, $kv->decrement('floatDecrementationKey',1.5));

        $this->assertTrue($kv->set('stringDecrementationKey',1));
        $this->assertFalse($kv->decrement('stringDecrementationKey','decrement by string'));

        $this->assertTrue($kv->set('stringDataKey','string data'));
        $this->assertFalse($kv->decrement('stringDataKey',100));

        $this->assertTrue($kv->set('arrayDataKey',array(1)));
        $this->assertFalse($kv->decrement('arrayDataKey',1));

        $oData=(object)array('property1'=>'string','property2'=>1);
        $this->assertTrue($kv->set('objectDataKey',$oData));
        $this->assertFalse($kv->decrement('objectDataKey',1));

    }

    public function testDelete (){

        $kv = jKVDb::getConnection($this->profile);

        $kv->set('deleteKey','data to delete');

        $this->assertTrue($kv->delete('deleteKey'));
        $this->assertNull($kv->get('deleteKey'));
        $this->assertFalse($kv->delete('inexistentKey'));

    }

    public function testGarbage () {
        /*
        $kv = jKVDb::getConnection($this->profile);

        $kv->set('remainingDataKey','remaining data');
        $kv->setWithTtl('garbage1DataKey','data send to the garbage',1);
        $kv->setWithTtl('garbage2DataKey','other data send to the garbage',strtotime("-1 day"));

        sleep(2);

        $this->assertTrue($kv->garbage());
      */

    }

    public function testFlush (){
        /*
        $kv = jKVDb::getConnection($this->profile);

        $kv->set('flush1DataKey','some data',0);
        $kv->setWithTtl('flush2DataKey','data to remove',strtotime("+1 day"));
        $kv->setWithTtl('flush3DataKey','other data to remove',time()+30);

        // should test that keys exists
        
        $this->assertTrue($kv->flush());
        
        // should test that keys doesn't exist anymore
      */
    }
}
