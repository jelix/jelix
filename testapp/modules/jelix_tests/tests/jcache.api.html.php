<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Tahina Ramaroson
* @contributor Sylvain de Vathaire
* @copyright   NEOV 2009
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

/**
* Tests API jCache
* @package     testapp
* @subpackage  jelix_tests module
*/

class UTjCacheAPI extends jUnitTestCaseDb {

    protected $profils = array();

    public function setUp (){
        $this->profils = array();
        $conf = parse_ini_file(JELIX_APP_CONFIG_PATH.'cache.ini.php', true);
        if ($conf['usingdb']['enabled'])
            $this->profils[] = 'usingdb';
        if ($conf['usingmemcached']['enabled'])
            $this->profils[] = 'usingmemcached';
        if ($conf['usingfile']['enabled'])
            $this->profils[] = 'usingfile';

        foreach($this->profils as $profil){
            switch($profil){
                case 'usingdb':
                    $this->emptyTable('jlx_cache');
                    break;
                case 'usingmemcached':
                    $mmc=memcache_connect('localhost',11211);
                    memcache_flush($mmc);
                    break;
                case 'usingfile':
                    if (file_exists(JELIX_APP_TEMP_PATH.'cache'))
                        jFile::removeDir(JELIX_APP_TEMP_PATH.'cache/',false);
                    break;
            }
        }

    }

    public function testSet (){

        $myData=(object)array(
            'id'=>1,
            'content'=>'Lorem ipsum dolor sit amét, conséctetuer adipiscing elit. Donec at odio vitae libero tempus convallis. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Vestibulum purus mauris, dapibus eu, sagittis quis, sagittis quis, mi. Morbi fringilla massa quis velit. Curabitur metus massa, semper mollis, molestie vel, adipiscing nec, massa. Phasellus vitae felis sed lectus dapibus facilisis. In ultrices sagittis ipsum. In at est. Integer iaculis turpis vel magna. Cras eu est. Integer porttitor ligula a tellus. Curabitur accumsan ipsum a velit. Sed laoreet lectus quis leo. Nulla pellentesque molestie ante. Quisque vestibulum est id justo. Ut pellentesque ante in neque.'
        );
        $myObj=(object)array('property1'=>'string','property2'=>'integer');
        $img=@imagecreate(100,100);

        $this->assertFalse(jCache::set('defaultProfileDisabledKey',$myData));

        foreach($this->profils as $profil){

            $this->assertTrue(jCache::set('noExpireKey',$myData,0,$profil));
            $this->assertTrue(jCache::get('noExpireKey',$profil)==$myData);

            $this->assertFalse(jCache::set('expiredKey','data expired',strtotime("-1 day"),$profil));
            $this->assertFalse(jCache::get('expiredKey',$profil));


            $this->assertTrue(jCache::set('ttlInDateKey',$myObj,'2020-12-31 00:00:00',$profil));
            $this->assertTrue(jCache::get('ttlInDateKey',$profil)==$myObj);

            $this->assertTrue(jCache::set('ttlInSecondesKey',$myObj,30,$profil));
            $this->assertTrue(jCache::get('ttlInSecondesKey',$profil)==$myObj);

            try{
                jCache::set('invalid Key','data for an invalid key',0,$profil);
                $this->fail();
            }catch(jException $e){
                $this->pass();
            }

            $this->assertFalse(jCache::set('unableToSerializeDataKey',$img,0,$profil));

            if ($profil == 'usingfile') {
                $this->assertTrue(file_exists(JELIX_APP_TEMP_PATH.'cache'));
                $this->assertTrue(file_exists(JELIX_APP_TEMP_PATH.'cache/jelix_cache___noExpireKey.cache'));
                $this->assertFalse(file_exists(JELIX_APP_TEMP_PATH.'cache/jelix_cache___expiredKey.cache'));
                $this->assertTrue(file_exists(JELIX_APP_TEMP_PATH.'cache/jelix_cache___ttlInDateKey.cache'));
                $this->assertTrue(file_exists(JELIX_APP_TEMP_PATH.'cache/jelix_cache___ttlInSecondesKey.cache'));
            }
       }
    }

    public function testGet (){


        foreach($this->profils as $profil){

            jCache::set('getKey','string for data',0,$profil);
            jCache::set('expiredKey','data expired',strtotime("-1 day"),$profil);

            $data = jCache::get(array('getKey','expiredKey','inexistentKey'),$profil);
            $this->assertTrue($data['getKey']=='string for data');
            $this->assertTrue(!isset($data['expiredKey']));
            $this->assertTrue(!isset($data['inexistentKey']));

            switch($profil){
                case 'usingdb':
                    $this->insertRecordsIntoTable('jlx_cache', array('cache_key','cache_data','cache_date'),array(array('cache_key'=>'phpIncompleteClassKey','cache_data'=>'O:9:"dummyData":2:{s:5:"label";s:23:"test unserializing data";s:11:"description";s:26:"for expecting an exception";}','cache_date'=>null)));
                    $data=jCache::get('phpIncompleteClassKey',$profil);
                    if(!is_object($data)){
                        $this->pass();
                    }else{
                        $this->fail();
                    }
                    break;
                case 'usingmemcached':
                    //Memcache manages serialization and unserialization process internally. It throws an exception in case of errors
                    $this->pass();
                    break;
            }

        }

    }

    public function testCall(){

        jClasses::inc('jelix_tests~testCache');
        $myClass = new testCache();
        foreach($this->profils as $profil){
            $returnData=jCache::call(array('testCache','staticMethod'),array(1,2),0,$profil);
            $this->assertTrue($returnData==3);
            $dataCached=jCache::get(md5(serialize(array('testCache','staticMethod')).serialize(array(1,2))),$profil);
            $this->assertTrue($dataCached==$returnData);

            try{
                jCache::call(array('testCache','missingStaticMethod'),null,0,$profil);
                $this->fail();
            }catch(jException $e){
                $this->pass();
            }

            $returnData=jCache::call(array($myClass,'method'),array(1,2),0,$profil);
            $this->assertTrue($returnData==3);
            $dataCached=jCache::get(md5(serialize(array($myClass,'method')).serialize(array(1,2))),$profil);
            $this->assertTrue($dataCached==$returnData);

            try{
                jCache::call(array($myClass,'missingMethod'),null,0,$profil);
                $this->fail();
            }catch(jException $e){
                $this->pass();
            }

            $returnData=jCache::call('testFunction',array(1,2),0,$profil);
            $this->assertTrue($returnData==3);
            $dataCached=jCache::get(md5(serialize('testFunction').serialize(array(1,2))),$profil);
            $this->assertTrue($dataCached==$returnData);

            try{
                jCache::call('testFunction_missing',null,0,$profil);
                $this->fail();
            }catch(jException $e){
                $this->pass();
            }
        }

    }

    public function testAdd (){

        $ttl=strtotime("+1 day");
        try{
            jCache::add('added1Key',111,$ttl,'invalidProfil');
            $this->fail();
        }catch(jException $e){
            $this->pass();
        }

        foreach($this->profils as $profil){
            jCache::set('existentKey',array((object)array('x'=>0,'y'=>0),'a screen point'),$ttl,$profil);
            $this->assertFalse(jCache::add('existentKey','add an existing data',$ttl,$profil));
            $this->assertTrue(jCache::add('added1Key',111,$ttl,$profil));
            $this->assertTrue(jCache::add('added2Key','some text for example','2020-12-31 00:00:00',$profil));
            $this->assertTrue(jCache::add('added3Key','for testing ttl',1,$profil));
            $data=jCache::get(array('added1Key','added2Key','added3Key'),$profil);
            $this->assertTrue(isset($data['added1Key']) && isset($data['added2Key']) && isset($data['added3Key']));
        }

    }

    public function testIncrement (){

        foreach($this->profils as $profil){

            $this->assertFalse(jCache::increment('InexistentKey',1,$profil));

            $this->assertTrue(jCache::set('integerDataKey',100,1,$profil));
            $this->assertTrue(jCache::increment('integerDataKey',1,$profil)==101);

            $this->assertTrue(jCache::set('floatDataKey',100.5,1,$profil));
            $this->assertTrue(jCache::increment('floatDataKey',1,$profil)==101);

            $this->assertTrue(jCache::set('floatIncrementationKey',100,1,$profil));
            $this->assertTrue(jCache::increment('floatIncrementationKey',1.5,$profil)==101);

            $this->assertTrue(jCache::set('stringIncrementationKey',1,1,$profil));
            $this->assertFalse(jCache::increment('stringIncrementationKey','increment by string',$profil));

            $this->assertTrue(jCache::set('stringDataKey','string data',1,$profil));
            $this->assertFalse(jCache::increment('stringDataKey',100,$profil));

            $this->assertTrue(jCache::set('arrayDataKey',array(1),1,$profil));
            $this->assertFalse(jCache::increment('arrayDataKey',1,$profil));

            $oData=(object)array('property1'=>'string','property2'=>1);
            $this->assertTrue(jCache::set('objectDataKey',$oData,1,$profil));
            $this->assertFalse(jCache::increment('objectDataKey',1,$profil));

        }

    }

    public function testDecrement (){

        foreach($this->profils as $profil){

            $this->assertFalse(jCache::decrement('InexistentKey',1,$profil));

            $this->assertTrue(jCache::set('integerDataKey',100,1,$profil));
            $this->assertTrue(jCache::decrement('integerDataKey',1,$profil)==99);

            $this->assertTrue(jCache::set('floatDataKey',100.5,1,$profil));
            $this->assertTrue(jCache::decrement('floatDataKey',1,$profil)==99);

            $this->assertTrue(jCache::set('floatDecrementationKey',100,1,$profil));
            $this->assertTrue(jCache::decrement('floatDecrementationKey',1.5,$profil)==99);

            $this->assertTrue(jCache::set('stringDecrementationKey',1,1,$profil));
            $this->assertFalse(jCache::decrement('stringDecrementationKey','decrement by string',$profil));

            $this->assertTrue(jCache::set('stringDataKey','string data',1,$profil));
            $this->assertFalse(jCache::decrement('stringDataKey',100,$profil));

            $this->assertTrue(jCache::set('arrayDataKey',array(1),1,$profil));
            $this->assertFalse(jCache::decrement('arrayDataKey',1,$profil));

            $oData=(object)array('property1'=>'string','property2'=>1);
            $this->assertTrue(jCache::set('objectDataKey',$oData,1,$profil));
            $this->assertFalse(jCache::decrement('objectDataKey',1,$profil));

        }

    }

    public function testReplace (){

        $newData = 'data to replace';

        foreach($this->profils as $profil){

            jCache::set('replace1Key','data one',0,$profil);
            jCache::set('replace2Key','data two',0,$profil);

            $this->assertFalse(jCache::replace('replace3Key',$newData,0,$profil));
            $this->assertTrue(jCache::replace('replace1Key',$newData,0,$profil));
            $this->assertTrue(jCache::get('replace1Key',$profil)==$newData);
            $this->assertTrue(jCache::replace('replace2Key',$newData,strtotime("-1 day"),$profil));
            $this->assertFalse(jCache::get('replace2Key',$profil));

        }

    }

    public function testDelete (){

        foreach($this->profils as $profil){

            jCache::set('deleteKey','data to delete',0,$profil);

            $this->assertTrue(jCache::delete('deleteKey',$profil));
            $this->assertFalse(jCache::get('deleteKey',$profil));
            $this->assertFalse(jCache::delete('inexistentKey',$profil));

        }

    }

    public function testGarbage (){

        $this->assertFalse(jCache::garbage());

        foreach($this->profils as $profil){

            jCache::set('remainingDataKey','remaining data',0,$profil);
            jCache::set('garbage1DataKey','data send to the garbage',1,$profil);
            jCache::set('garbage2DataKey','other data send to the garbage',strtotime("-1 day"),$profil);

            sleep(2);

            $this->assertTrue(jCache::garbage($profil));

            switch($profil){
                case 'usingdb':
                    $this->assertTableContainsRecords('jlx_cache',array(
                        array('cache_key'=>'remainingDataKey','cache_data'=>serialize('remaining data'),'cache_date'=>null)
                    ));
                    break;
                case 'usingmemcached':
                    $mmc=memcache_connect('localhost',11211);
                    $this->assertTrue(memcache_get($mmc,'remainingDataKey')=='remaining data');
                    $this->assertFalse(memcache_get($mmc,'garbage1DataKey'));
                    $this->assertFalse(memcache_get($mmc,'garbage2DataKey'));
                    break;
                case 'usingfile':
                    $this->assertTrue(file_exists(JELIX_APP_TEMP_PATH.'cache/jelix_cache___remainingDataKey.cache'));
                    $this->assertFalse(file_exists(JELIX_APP_TEMP_PATH.'cache/jelix_cache___garbage1DataKey.cache'));
                    $this->assertFalse(file_exists(JELIX_APP_TEMP_PATH.'cache/jelix_cache___garbage1DataKey.cache'));
            }

        }

    }

    public function testFlush (){

        $this->assertFalse(jCache::flush());

        foreach($this->profils as $profil){

            jCache::set('flush1DataKey','some data',0,$profil);
            jCache::set('flush2DataKey','data to remove',strtotime("+1 day"),$profil);
            jCache::set('flush3DataKey','other data to remove',time()+30,$profil);

            switch($profil){
                case 'usingdb':
                    $this->assertTableHasNRecords('jlx_cache', 3);
                    $this->assertTrue(jCache::flush($profil));
                    $this->assertTableIsEmpty('jlx_cache');
                    break;
                case 'usingmemcached':
                    $mmc=memcache_connect('localhost',11211);
                    $this->assertTrue(memcache_get($mmc,'flush1DataKey'));
                    $this->assertTrue(memcache_get($mmc,'flush2DataKey'));
                    $this->assertTrue(memcache_get($mmc,'flush3DataKey'));
                    $this->assertTrue(jCache::flush($profil));
                    $this->assertFalse(memcache_get($mmc,'flush1DataKey'));
                    $this->assertFalse(memcache_get($mmc,'flush2DataKey'));
                    $this->assertFalse(memcache_get($mmc,'flush3DataKey'));
                    break;
                case 'usingfile':
                    $this->assertTrue(file_exists(JELIX_APP_TEMP_PATH.'cache/jelix_cache___flush1DataKey.cache'));
                    $this->assertTrue(file_exists(JELIX_APP_TEMP_PATH.'cache/jelix_cache___flush2DataKey.cache'));
                    $this->assertTrue(file_exists(JELIX_APP_TEMP_PATH.'cache/jelix_cache___flush3DataKey.cache'));
                    $this->assertTrue(jCache::flush($profil));
                    $this->assertFalse(file_exists(JELIX_APP_TEMP_PATH.'cache/jelix_cache___flush1DataKey.cache'));
                    $this->assertFalse(file_exists(JELIX_APP_TEMP_PATH.'cache/jelix_cache___flush2DataKey.cache'));
                    $this->assertFalse(file_exists(JELIX_APP_TEMP_PATH.'cache/jelix_cache___flush3DataKey.cache'));
            }

        }

    }

}

?>