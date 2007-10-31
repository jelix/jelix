<?php
/**
* @package     jelix
* @subpackage  junittests
* @author      Jouanneau Laurent
* @contributor
* @copyright   2007 Jouanneau laurent
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/
require_once(dirname(__FILE__).'/junittestcase.class.php');

class jUnitTestCaseDb extends jUnitTestCase {

    /**
    *   erase all record in a table
    */
    function emptyTable($table){
        $db = jDb::getConnection($this->dbProfil);
        $db->exec('DELETE FROM '.$table);
    }

    function insertRecordsIntoTable($table, $fields, $records, $emptyBefore=false){
        if($emptyBefore)
            $this->emptytable($table);
        $db = jDb::getConnection($this->dbProfil);

        $sql = 'INSERT INTO '.$table.'  ('.implode(',',$fields).') VALUES (';

        foreach($records as $rec){
            $ins='';
            foreach($fields as $f){
                $ins.= ','.$db->quote($rec[$f]);
            }
            $db->exec($sql.substr($ins,1).')');
        }
    }


    /**
     * check if the table is empty
     */
    function assertTableIsEmpty($table, $message="%s"){
        $db = jDb::getConnection($this->dbProfil);
        $rs = $db->query('SELECT count(*) as N FROM '.$table);
        if($r=$rs->fetch()){
            $message = sprintf( $message, $table. " table should be empty");
            if($r->N == 0){
                $this->pass($message);
                return true;
            }else{
                $this->fail($message);
                return false;
            }
        }else{
            $this->fail(sprintf( $message, $table. " table should be empty, but error when try to get record count"));
            return false;
        }
    }

    /**
     * check if the table is not empty
     */
    function assertTableIsNotEmpty($table, $message="%s"){
        $db = jDb::getConnection($this->dbProfil);
        $rs = $db->query('SELECT count(*) as N FROM '.$table);
        if($r=$rs->fetch()){
            $message = sprintf( $message, $table. " table shouldn't be empty");
            if($r->N > 0){
                $this->pass($message);
                return true;
            }else{
                $this->fail($message);
                return false;
            }
        }else{
            $this->fail(sprintf( $message, $table. " table shouldn't be empty, but error when try to get record count"));
            return false;
        }
    }

    /**
     * check if a table has a specific number of records
     */
    function assertTableHasNRecords($table, $n, $message="%s"){
        $db = jDb::getConnection($this->dbProfil);
        $rs = $db->query('SELECT count(*) as N FROM '.$table);
        if($r=$rs->fetch()){
            $message = sprintf( $message, $table. " table should contains ".$n." records");
            if($r->N == $n){
                $this->pass($message);
                return true;
            }else{
                $this->fail($message);
                return false;
            }
        }else{
            $this->fail(sprintf( $message, $table. " table shouldn't be empty, but error when try to get record count"));
            return false;
        }
    }

    /**
     * check if all given record are in the table
     */
    function assertTableContainsRecords($table, $records, $onlyThem = true, $message ="%s"){
        $db = jDb::getConnection($this->dbProfil);

        $message = sprintf( $message, $table. " table should contains given records.");

         $sql = 'SELECT * FROM '.$table;
         $rs = $db->query($sql);
         if(!$rs){
            $this->fail($message.' ( no results set)');
            return false;
         }
         $results = array();
         foreach($rs as $r){
            $results[]=get_object_vars($r);
         }

        $globalok=true;
        $resultsSaved = $results;
        foreach($records as $rec){
            $ok=false;
            foreach($results as $k=>$res){
                $sameValues = true;
                foreach($rec as $name=>$value){
                    if($res[$name] != $value) {
                        $sameValues = false;
                        break;
                    }
                }

                if($sameValues){
                    unset($results[$k]);
                    $ok = true;
                    break;
                }
            }
            if(!$ok){
                $globalok = false;
                $this->fail($message.'. No record found : '. var_export($rec,true));
            }
        }

        if($onlyThem && count($results) != 0){
            $globalok = false;
            $this->fail($message.'. Other unknow records exists');
        }

        if($globalok){
            $this->pass($message);
            return true;
        }else{
            $this->sendMessage('Results from database');
            $this->dump($resultsSaved);
            $this->sendMessage('Records we should find');
            $this->dump($records);
            return false;
        }
    }

    function getLastId($fieldName, $tableName){
        $db = jDb::getConnection($this->dbProfil);
        return $db->lastIdInTable($fieldName, $tableName);
    }

}



?>