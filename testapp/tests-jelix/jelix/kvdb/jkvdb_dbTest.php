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

class jkvdb_dbTest extends jKVDbTest {

    protected $db = null;

    function setUp() : void {
        $this->profile = 'usingdb';
        self::initJelixConfig();
        if ($this->_kvdbSetUp()) {
            $this->dbProfile = $this->profileData['dbprofile'];
            $this->db = jDb::getConnection($this->dbProfile);
            $this->db->exec('delete from testkvdb');
        }
        parent::setUp();
    }

    public function testGarbage () {

        $kv = jKVDb::getConnection($this->profile);

        $kv->set('remainingDataKey','remaining data');
        $kv->setWithTtl('garbage1DataKey','data send to the garbage',1);
        $kv->setWithTtl('garbage2DataKey','other data send to the garbage',strtotime("-1 day"));

        sleep(2);

        $this->assertTableContainsRecords('testkvdb', array(
            array('k_key'=>'remainingDataKey', 'k_value'=>serialize('remaining data')),
            array('k_key'=>'garbage1DataKey', 'k_value'=>serialize('data send to the garbage')),
            array('k_key'=>'garbage2DataKey', 'k_value'=>serialize('other data send to the garbage')),
                                        ));

        $this->assertTrue($kv->garbage());

        $this->assertTableContainsRecords('testkvdb', array(
            array('k_key'=>'remainingDataKey', 'k_value'=>serialize('remaining data')),
                                        ));


    }

    public function testFlush (){

        $kv = jKVDb::getConnection($this->profile);

        $kv->set('flush1DataKey','some data',0);
        $kv->setWithTtl('flush2DataKey','data to remove',strtotime("+1 day"));
        $kv->setWithTtl('flush3DataKey','other data to remove',time()+30);

        // should test that keys exists
        $this->assertTableContainsRecords('testkvdb', array(
            array('k_key'=>'flush1DataKey', 'k_value'=>serialize('some data')),
            array('k_key'=>'flush2DataKey', 'k_value'=>serialize('data to remove')),
            array('k_key'=>'flush3DataKey', 'k_value'=>serialize('other data to remove')),
                                        ));
        $this->assertTrue($kv->flush());
        
        // should test that keys doesn't exist anymore
        $this->assertTableIsEmpty('testkvdb');
    }
}

?>
