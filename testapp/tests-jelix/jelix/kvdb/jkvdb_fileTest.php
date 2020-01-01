<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Laurent Jouanneau
* @copyright   2010 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

require_once(__DIR__.'/jkvdb.lib.php');

/**
* Tests API jKVDb
* @package     testapp
* @subpackage  jelix_tests module
*/

class jkvdb_fileTest extends jKVDbTest {

    function setUp() : void {
        $this->profile = 'usingfile';
        self::initJelixConfig();

        if (!$this->_kvdbSetUp())
            return;
        if (file_exists(jApp::tempPath().'kvfiles/tests/'))
            jFile::removeDir(jApp::tempPath().'kvfiles/tests/',false);
        parent::setUp();
    }
    
    public function testSetGet (){
        parent::testSetGet();
        $this->assertTrue (file_exists(jApp::tempPath().'kvfiles/tests/'));
        $this->assertTrue (file_exists(jApp::tempPath().'kvfiles/tests/20/43/2043d5728dc57e7e24dad7d18b326c1a_noExpireKey'));
        $this->assertTrue(file_exists(jApp::tempPath().'kvfiles/tests/08/9a/089a2b9a8bc3e0982e2e6bbef530735a_expiredKey'));
        $this->assertTrue (file_exists(jApp::tempPath().'kvfiles/tests/69/13/6913deb0fc1924c87337790b6a049347_ttlInSecondesKey'));
        $this->assertFalse(file_exists(jApp::tempPath().'kvfiles/tests/52/a1/52a11fea379fe88ecb691f996e5fbf62_unableToSerializeDataKey'));
    }


    public function testInsertReplace () {
        parent::testInsertReplace();

        $this->assertEquals(serialize('new value'), file_get_contents(jApp::tempPath().'kvfiles/tests/a2/1d/a21d88063ed27afccd86342a31c8be60_existingKey'));
    }    


    public function testGarbage (){

        $kv = jKVDb::getConnection($this->profile);

        $kv->set('remainingDataKey','remaining data');
        $kv->setWithTtl('garbage1DataKey','data send to the garbage',1);
        $kv->setWithTtl('garbage2DataKey','other data send to the garbage',strtotime("-1 day"));

        $this->assertTrue(file_exists(jApp::tempPath().'kvfiles/tests/01/09/010911fe0ba55c611068527f805a7308_remainingDataKey'));
        $this->assertTrue(file_exists(jApp::tempPath().'kvfiles/tests/70/78/70784f015016dbe57006429c48c26be9_garbage1DataKey'));
        $this->assertTrue(file_exists(jApp::tempPath().'kvfiles/tests/23/e3/23e399e4c16042b29aa860f1aa0cf431_garbage2DataKey'));

        sleep(2);

        $this->assertTrue($kv->garbage());

        $this->assertTrue(file_exists(jApp::tempPath().'kvfiles/tests/01/09/010911fe0ba55c611068527f805a7308_remainingDataKey'));
        $this->assertFalse(file_exists(jApp::tempPath().'kvfiles/tests/70/78/70784f015016dbe57006429c48c26be9_garbage1DataKey'));
        $this->assertFalse(file_exists(jApp::tempPath().'kvfiles/tests/23/e3/23e399e4c16042b29aa860f1aa0cf431_garbage2DataKey'));
    }

    public function testFlush () {

        $kv = jKVDb::getConnection($this->profile);

        $kv->set('flush1DataKey','some data',0);
        $kv->setWithTtl('flush2DataKey','data to remove',strtotime("+1 day"));
        $kv->setWithTtl('flush3DataKey','other data to remove',time()+30);

        $this->assertTrue(file_exists(jApp::tempPath().'kvfiles/tests/b6/d7/b6d72473bc7663367d02de121871b573_flush1DataKey'));
        $this->assertTrue(file_exists(jApp::tempPath().'kvfiles/tests/e0/85/e085abf6184768075f5284e5700897c2_flush2DataKey'));
        $this->assertTrue(file_exists(jApp::tempPath().'kvfiles/tests/c8/52/c8527b170651bf375a29e9e77e867385_flush3DataKey'));
        $this->assertTrue($kv->flush());
        $this->assertFalse(file_exists(jApp::tempPath().'kvfiles/tests/b6/d7/b6d72473bc7663367d02de121871b573_flush1DataKey'));
        $this->assertFalse(file_exists(jApp::tempPath().'kvfiles/tests/e0/85/e085abf6184768075f5284e5700897c2_flush2DataKey'));
        $this->assertFalse(file_exists(jApp::tempPath().'kvfiles/tests/c8/52/c8527b170651bf375a29e9e77e867385_flush3DataKey'));
    }

}

?>
