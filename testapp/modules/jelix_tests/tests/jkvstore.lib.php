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
* Tests API jKVStore
* @package     testapp
* @subpackage  jelix_tests module
*/

abstract class UTjKVStore extends jUnitTestCaseDb {

    protected $profile;

    protected $mmc;

    function getTests(){
        $conf = parse_ini_file(JELIX_APP_CONFIG_PATH.'kvprofiles.ini.php', true);
        if (isset($conf[$this->profile])) {
             return parent::getTests();
        }
        else {
            $this->sendMessage('UTjKVStore cannot be run with '.$this->profile.' : undefined profile');
            return array();
        }
    }

    public function testSetGet (){

        $myData=(object)array(
            'id'=>1,
            'content'=>'Lorem ipsum dolor sit amét, conséctetuer adipiscing elit. Donec at odio vitae libero tempus convallis. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Vestibulum purus mauris, dapibus eu, sagittis quis, sagittis quis, mi. Morbi fringilla massa quis velit. Curabitur metus massa, semper mollis, molestie vel, adipiscing nec, massa. Phasellus vitae felis sed lectus dapibus facilisis. In ultrices sagittis ipsum. In at est. Integer iaculis turpis vel magna. Cras eu est. Integer porttitor ligula a tellus. Curabitur accumsan ipsum a velit. Sed laoreet lectus quis leo. Nulla pellentesque molestie ante. Quisque vestibulum est id justo. Ut pellentesque ante in neque.'
        );
        $myObj=(object)array('property1'=>'string','property2'=>'integer');
        $img=@imagecreate(100,100);

        $kv = jKVStore::getConnection($this->profile);

        $this->assertTrue($kv->set('noExpireKey',$myData));
        $this->assertTrue($kv->get('noExpireKey')==$myData);

        $this->assertTrue($kv->setWithTtl('expiredKey','data expired', strtotime("-1 year")));
        $this->assertFalse($kv->get('expiredKey'));

        $this->assertTrue($kv->setWithTtl('ttlInSecondesKey', $myObj, 30));
        $this->assertTrue($kv->get('ttlInSecondesKey')==$myObj);

        $this->assertFalse($kv->set('unableToSerializeDataKey',$img));

        if ($this->profile == 'usingfile') {
            $this->assertTrue (file_exists(JELIX_APP_TEMP_PATH.'kvfiles/tests/'));
            $this->assertTrue (file_exists(JELIX_APP_TEMP_PATH.'kvfiles/tests/20/43/2043d5728dc57e7e24dad7d18b326c1a_noExpireKey'));
            $this->assertTrue(file_exists(JELIX_APP_TEMP_PATH.'kvfiles/tests/08/9a/089a2b9a8bc3e0982e2e6bbef530735a_expiredKey'));
            $this->assertTrue (file_exists(JELIX_APP_TEMP_PATH.'kvfiles/tests/69/13/6913deb0fc1924c87337790b6a049347_ttlInSecondesKey'));
            $this->assertFalse(file_exists(JELIX_APP_TEMP_PATH.'kvfiles/tests/52/a1/52a11fea379fe88ecb691f996e5fbf62_unableToSerializeDataKey'));
        }
    }

    public function testMultipleGet (){
        $kv = jKVStore::getConnection($this->profile);
        $kv->set('getKey', 'string for data');
        $kv->setWithTtl('expiredKey','data expired',strtotime("-1 day"));
        $data = $kv->get(array('getKey','expiredKey','inexistentKey'));
        $this->assertTrue($data['getKey']=='string for data');
        $this->assertTrue(!isset($data['expiredKey']));
        $this->assertTrue(!isset($data['inexistentKey']));
    }

    public function testInsertReplace () {

        $kv = jKVStore::getConnection($this->profile);

        $this->assertFalse($kv->get('unknowkey'));
        $this->assertFalse($kv->replace('unknowkey','new value'));
        $this->assertFalse($kv->get('unknowkey'));

        $this->assertTrue($kv->set('existingKey', 'a value'));
        $this->assertFalse($kv->insert('existingKey','new value'));
        $this->assertEqual($kv->get('existingKey'), 'a value');
        $this->assertTrue($kv->replace('existingKey','new value'));
        $this->assertEqual($kv->get('existingKey'), 'new value');
        if ($this->profile == 'usingfile') {
            $this->assertEqual(file_get_contents(JELIX_APP_TEMP_PATH.'kvfiles/tests/a2/1d/a21d88063ed27afccd86342a31c8be60_existingKey')
                           , serialize('new value')
                           );
        }
    }

    public function testAppendPrepend() {

        $kv = jKVStore::getConnection($this->profile);

        $this->assertFalse($kv->get('unknowkey'));
        $this->assertFalse($kv->append('unknowkey','new value'));
        $this->assertFalse($kv->prepend('unknowkey','new value'));
        $this->assertFalse($kv->get('unknowkey'));

        $this->assertTrue($kv->set('existingKey', 'a value'));
        $this->assertEqual($kv->get('existingKey'), 'a value');
        if ($this->profile == 'usingfile') {
            $this->assertEqual(file_get_contents(JELIX_APP_TEMP_PATH.'kvfiles/tests/a2/1d/a21d88063ed27afccd86342a31c8be60_existingKey')
                           , serialize('a value'));
        }

        $this->assertEqual($kv->append('existingKey','_suffix'), 'a value_suffix');
        if ($this->profile == 'usingfile') {
            $this->assertEqual(file_get_contents(JELIX_APP_TEMP_PATH.'kvfiles/tests/a2/1d/a21d88063ed27afccd86342a31c8be60_existingKey')
                           , serialize('a value_suffix'));
        }

        $this->assertEqual($kv->get('existingKey'), 'a value_suffix');

        $this->assertEqual($kv->prepend('existingKey','prefix_'), 'prefix_a value_suffix');
        $this->assertEqual($kv->get('existingKey'), 'prefix_a value_suffix');
        if ($this->profile == 'usingfile') {
            $this->assertEqual(file_get_contents(JELIX_APP_TEMP_PATH.'kvfiles/tests/a2/1d/a21d88063ed27afccd86342a31c8be60_existingKey')
                           , serialize('prefix_a value_suffix'));
        }
    }

    
    public function testIncrement() {

        $kv = jKVStore::getConnection($this->profile);

        $this->assertFalse($kv->increment('InexistentKey',1));

        $this->assertTrue($kv->set('integerDataKey', 100));
        $this->assertEqual($kv->increment('integerDataKey'), 101);

        $this->assertTrue($kv->set('floatDataKey', 100.5));
        $this->assertEqual($kv->get('floatDataKey'), 100.5);
        $this->assertEqual($kv->increment('floatDataKey',1), 101);
       
        $this->assertTrue($kv->set('floatIncrementationKey',100));
        $this->assertEqual($kv->increment('floatIncrementationKey',1.5), 101);

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

        $kv = jKVStore::getConnection($this->profile);

        $this->assertFalse($kv->decrement('InexistentKey',1));

        $this->assertTrue($kv->set('integerDataKey',100));
        $this->assertEqual($kv->decrement('integerDataKey',1),99);

        $this->assertTrue($kv->set('floatDataKey',100.5));
        $this->assertEqual($kv->decrement('floatDataKey',1),99);

        $this->assertTrue($kv->set('floatDecrementationKey',100));
        $this->assertEqual($kv->decrement('floatDecrementationKey',1.5),99);

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

        $kv = jKVStore::getConnection($this->profile);

        $kv->set('deleteKey','data to delete');

        $this->assertTrue($kv->delete('deleteKey'));
        $this->assertFalse($kv->get('deleteKey'));
        $this->assertFalse($kv->delete('inexistentKey'));

    }

    public function testGarbage (){

        $kv = jKVStore::getConnection($this->profile);

        $kv->set('remainingDataKey','remaining data');
        $kv->setWithTtl('garbage1DataKey','data send to the garbage',1);
        $kv->setWithTtl('garbage2DataKey','other data send to the garbage',strtotime("-1 day"));

        if ($this->profile =='usingfile') {
            $this->assertTrue(file_exists(JELIX_APP_TEMP_PATH.'kvfiles/tests/01/09/010911fe0ba55c611068527f805a7308_remainingDataKey'));
            $this->assertTrue(file_exists(JELIX_APP_TEMP_PATH.'kvfiles/tests/70/78/70784f015016dbe57006429c48c26be9_garbage1DataKey'));
            $this->assertTrue(file_exists(JELIX_APP_TEMP_PATH.'kvfiles/tests/23/e3/23e399e4c16042b29aa860f1aa0cf431_garbage2DataKey'));
        }

        sleep(2);

        $this->assertTrue($kv->garbage());

        switch($this->profile){
            case 'usingmemcache':
                $this->assertTrue(memcache_get($this->mmc,'remainingDataKey')=='remaining data');
                $this->assertFalse(memcache_get($this->mmc,'garbage1DataKey'));
                $this->assertFalse(memcache_get($this->mmc,'garbage2DataKey'));
                break;
            case 'usingfile':
                $this->assertTrue(file_exists(JELIX_APP_TEMP_PATH.'kvfiles/tests/01/09/010911fe0ba55c611068527f805a7308_remainingDataKey'));
                $this->assertFalse(file_exists(JELIX_APP_TEMP_PATH.'kvfiles/tests/70/78/70784f015016dbe57006429c48c26be9_garbage1DataKey'));
                $this->assertFalse(file_exists(JELIX_APP_TEMP_PATH.'kvfiles/tests/23/e3/23e399e4c16042b29aa860f1aa0cf431_garbage2DataKey'));
                break;
            case 'usingredis':
                $this->assertEqual($this->redis->get('remainingDataKey'),serialize('remaining data'));
                $this->assertNull($this->redis->get('garbage1DataKey'));
                $this->assertNull($this->redis->get('garbage2DataKey'));
                break;
        }
    }

    public function testFlush (){

        $kv = jKVStore::getConnection($this->profile);

        $kv->set('flush1DataKey','some data',0);
        $kv->setWithTtl('flush2DataKey','data to remove',strtotime("+1 day"));
        $kv->setWithTtl('flush3DataKey','other data to remove',time()+30);

        switch($this->profile){
            case 'usingmemcache':
                $this->assertTrue(memcache_get($this->mmc,'flush1DataKey'));
                $this->assertTrue(memcache_get($this->mmc,'flush2DataKey'));
                $this->assertTrue(memcache_get($this->mmc,'flush3DataKey'));
                $this->assertTrue($kv->flush());
                $this->assertFalse(memcache_get($this->mmc,'flush1DataKey'));
                $this->assertFalse(memcache_get($this->mmc,'flush2DataKey'));
                $this->assertFalse(memcache_get($this->mmc,'flush3DataKey'));
                break;
            case 'usingfile':
                $this->assertTrue(file_exists(JELIX_APP_TEMP_PATH.'kvfiles/tests/b6/d7/b6d72473bc7663367d02de121871b573_flush1DataKey'));
                $this->assertTrue(file_exists(JELIX_APP_TEMP_PATH.'kvfiles/tests/e0/85/e085abf6184768075f5284e5700897c2_flush2DataKey'));
                $this->assertTrue(file_exists(JELIX_APP_TEMP_PATH.'kvfiles/tests/c8/52/c8527b170651bf375a29e9e77e867385_flush3DataKey'));
                $this->assertTrue($kv->flush());
                $this->assertFalse(file_exists(JELIX_APP_TEMP_PATH.'kvfiles/tests/b6/d7/b6d72473bc7663367d02de121871b573_flush1DataKey'));
                $this->assertFalse(file_exists(JELIX_APP_TEMP_PATH.'kvfiles/tests/e0/85/e085abf6184768075f5284e5700897c2_flush2DataKey'));
                $this->assertFalse(file_exists(JELIX_APP_TEMP_PATH.'kvfiles/tests/c8/52/c8527b170651bf375a29e9e77e867385_flush3DataKey'));
                break;
            case 'usingredis':
                $this->assertEqual($this->redis->get('flush1DataKey'),serialize('some data'));
                $this->assertEqual($this->redis->get('flush2DataKey'),serialize('data to remove'));
                $this->assertEqual($this->redis->get('flush3DataKey'),serialize('other data to remove'));
                $this->assertTrue($kv->flush());
                $this->assertNull($this->redis->get('flush1DataKey'));
                $this->assertNull($this->redis->get('flush2DataKey'));
                $this->assertNull($this->redis->get('flush3DataKey'));
                break;
        }
    }
}

?>