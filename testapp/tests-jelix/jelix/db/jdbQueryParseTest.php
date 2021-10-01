<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Laurent Jouanneau
* @contributor
* @copyright   2015 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/


class queryparseConnection extends \Jelix\Database\AbstractConnection {
    function __construct($profile) { }
    function parseQuery($sql, $reParam) {
        return $this->findParameters($sql, $reParam);
    }

     public function beginTransaction () {}
     public function commit () {}
     public function rollback () {}
     public function prepare ($query, $driverOptions = array()) {}
     public function errorInfo() {}
     public function errorCode() {}
     public function lastInsertId($fromSequence='') {}
     public function getAttribute($id) {}
     public function setAttribute($id, $value) {}
     protected function _autoCommitNotify ($state) {}
     protected function _connect () {}
     protected function _disconnect () {}
     protected function _doQuery ($queryString) {}
     protected function _doExec ($queryString) {}
     protected function _doLimitQuery ($queryString, $offset, $number) {}
    protected function _getSchema(){}
}



class jdbQueryParseTest  extends \Jelix\UnitTests\UnitTestCase
{
    /*public function setUp() : void {
        //self::initJelixConfig();
    }

    public function tearDown() : void {
        //parent::tearDown();
    }
*/
    function testQuestionMarker() {
        $cn = new queryparseConnection('');
        
        $res = $cn->parseQuery('select * from c WHERE id= :id and foo= :bar','?');
        $this->assertEquals($res[0], 'select * from c WHERE id= ? and foo= ?');
        $this->assertEquals($res[1], array('id','bar'));

        $res = $cn->parseQuery('select * from c WHERE id= :id and foo= :bar and u=:id','?');
        $this->assertEquals($res[0], 'select * from c WHERE id= ? and foo= ? and u=?');
        $this->assertEquals($res[1], array('id','bar', 'id'));

        $res = $cn->parseQuery('select * from c WHERE id= :id and t = ":popo" AND foo= :bar','?');
        $this->assertEquals($res[0], 'select * from c WHERE id= ? and t = ":popo" AND foo= ?');
        $this->assertEquals($res[1], array('id','bar'));

        $res = $cn->parseQuery('select * from c WHERE id= :id and
                               t = ":po\\"p\'o" AND u=\'\\\' :ui zer\'
                               foo= :bar2','?');
        $this->assertEquals($res[0], 'select * from c WHERE id= ? and
                               t = ":po\\"p\'o" AND u=\'\\\' :ui zer\'
                               foo= ?','?');
        $this->assertEquals($res[1], array('id','bar2'));

    }

    function testNumericalMarker() {
        $cn = new queryparseConnection('');

        $res = $cn->parseQuery('select * from c WHERE id= :id and foo= :bar','$%');
        $this->assertEquals($res[0], 'select * from c WHERE id= $1 and foo= $2');
        $this->assertEquals($res[1], array('id','bar'));

        $res = $cn->parseQuery('select * from c WHERE id= :id and foo= :bar and u=:id','$%');
        $this->assertEquals($res[0], 'select * from c WHERE id= $1 and foo= $2 and u=$1');
        $this->assertEquals($res[1], array('id','bar'));

        $res = $cn->parseQuery('select * from c WHERE id= :id and t = ":popo" AND foo= :bar','$%');
        $this->assertEquals($res[0], 'select * from c WHERE id= $1 and t = ":popo" AND foo= $2');
        $this->assertEquals($res[1], array('id','bar'));

        $res = $cn->parseQuery('select * from c WHERE id= :id and
                               t = ":po\\"p\'o" AND u=\'\\\' :ui zer\'
                               foo= :bar2','$%');
        $this->assertEquals($res[0], 'select * from c WHERE id= $1 and
                               t = ":po\\"p\'o" AND u=\'\\\' :ui zer\'
                               foo= $2');
        $this->assertEquals($res[1], array('id','bar2'));

    }

}
