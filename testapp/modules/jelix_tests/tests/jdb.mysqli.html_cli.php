<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Florian Lonqueu-Brochard
* @copyright   2012 Florian Lonqueu-Brochard
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

class UTjDbMysqli extends jUnitTestCaseDb {
    protected $dbProfile ='mysqli_profile';

    function skip() {
        try{
            $prof = jProfiles::get('jdb', $this->dbProfile, true);
        }
        catch (Exception $e) {
            $this->skipIf(true, 'UTjDbMysqli cannot be run: '.$e->getMessage());
        }
    }

    function setUp() {
        $this->emptyTable('labels_test');
    }
    function tearDown() {
        $this->emptyTable('labels_test');
    }

    function testTransaction() { //labels_test is an InnoDb table so transaction are supported
        $this->assertTableIsEmpty('labels_test');
        $cnx = jDb::getConnection($this->dbProfile);
        $dao = jDao::create ('labels', $this->dbProfile);


        $cnx->beginTransaction();

        $l1 = jDao::createRecord ('labels', $this->dbProfile);
        $l1->key =12;
        $l1->lang = 'fr';
        $l1->label = 'test1';
        $dao->insert($l1);
        $this->assertTableIsNotEmpty('labels_test');

        $cnx->rollback();
        $this->assertTableIsEmpty('labels_test');


        $cnx->beginTransaction();

        $l2 = jDao::createRecord ('labels', $this->dbProfile);
        $l2->key =15;
        $l2->lang = 'en';
        $l2->label = 'test2';
        $dao->insert($l2);

        $cnx->commit();
        $this->assertTableIsNotEmpty('labels_test');
    }


    function testExecMulti(){
        $cnx = jDb::getConnection($this->dbProfile);

        $this->assertTableIsEmpty('labels_test');
        $queries = "INSERT INTO `labels_test` (`key`,`lang` ,`label`) VALUES ('12', 'fr', 'test1');";
        $queries .= "INSERT INTO `labels_test` (`key`,`lang` ,`label`) VALUES ('24', 'en', 'test2');";

        $res = $cnx->exec_multi($queries);
        $this->assertTrue($res);
        $this->assertTableHasNRecords('labels_test', 2);
    }


}

?>