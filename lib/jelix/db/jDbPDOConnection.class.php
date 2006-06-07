<?php
/**
* @package    jelix
* @subpackage db
* @version    $Id:$
* @author     Laurent Jouanneau
* @contributor
* @copyright  2005-2006 Laurent Jouanneau
* @link      http://www.jelix.org
* @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*
*/

// les noms des constantes de PDO ont changés entre php 5.0 et 5.1
// on utilise alors les notres
define('JPDO_FETCH_OBJ',5); // PDO::FETCH_OBJ
define('JPDO_FETCH_ORI_NEXT',0); // PDO::FETCH_ORI_NEXT
define('JPDO_FETCH_ORI_FIRST',3);
define('JPDO_FETCH_CLASS',8); // PDO::FETCH_CLASS
define('JPDO_ATTR_STATEMENT_CLASS',13); //PDO::ATTR_STATEMENT_CLASS
define('JPDO_ATTR_AUTOCOMMIT',0); //PDO::ATTR_AUTOCOMMIT
define('JPDO_ATTR_CURSOR',10); // PDO::ATTR_CURSOR
define('JPDO_CURSOR_SCROLL',1); //PDO::CURSOR_SCROLL

class jDbPDOResultSet extends PDOStatement implements Iterator {

    const FETCH_CLASS = 8;

    protected $_fetchMode = 0;

    public function fetchAll ( $fetch_style = JPDO_FETCH_OBJ, $column_index=0 ){
        if($this->_fetchMode){
            return parent::fetchAll($this->_fetchModeSet, $column_index);
        }else{
            return parent::fetchAll( JPDO_FETCH_OBJ, $column_index);
        }
    }

    public function fetch( $fetch_style= null, $cur_or=JPDO_FETCH_ORI_NEXT, $cur_offset=0 ){
        if($this->_fetchMode){
            return parent::fetch($this->_fetchModeSet, $cur_or, $cur_offset);
        }else{
            return parent::fetch(JPDO_FETCH_OBJ,$cur_or,$cur_offset);
        }
    }

    public function setFetchMode($mode, $param=null){
        $this->_fetchMode = $mode;
        return parent::setFetchMode($mode, $param);
    }

    //--------------- interface Iterator
    protected $_currentRecord = false;
    protected $_recordIndex = 0;

    function current () {
        return $this->_currentRecord;
    }
 	function key () {
 	  return $this->_recordIndex;
 	}

 	function next () {
 	  $this->_currentRecord =  $this->fetch(JPDO_FETCH_OBJ,JPDO_FETCH_ORI_NEXT);
 	  if($this->_currentRecord)
 	      $this->_recordIndex++;
 	}

 	function rewind () {
 	  $this->_rewind();
 	  $this->_recordIndex = 0;
 	  $this->_currentRecord =  $this->fetch(JPDO_FETCH_OBJ,JPDO_FETCH_ORI_FIRST);
 	}

 	function valid () {
 	  return ($this->_currentRecord != false);
 	}

}



class jDbPDOConnection extends PDO {

    /**
    * the profil the connection is using
    * @var array
    */
    public $profil;
    public $dbms;

    /**
    * @constructor
    */
    function __construct($profil){
       $this->profil = $profil;
       $this->dbms=substr($profil['dsn'],0,strpos(':',$profil['dsn']));
       $prof=$profil;
       unset($prof['dsn']);
       unset($prof['user']);
       unset($prof['password']);
       unset($prof['driver']);
       parent::__construct($profil['dsn'], $profil['user'], $profil['password'], $prof);
       $this->setAttribute(JPDO_ATTR_STATEMENT_CLASS, array('jDbPDOResultSet'));
    }

    public function query ($queryString, $opt=false){
        if($opt) return parent::query($queryString);
        // on passe par prepare, pour pouvoir specifier JPDO_CURSOR_SCROLL à cause de l'iterateur
        $sth = $this->prepare($queryString, array(JPDO_ATTR_CURSOR=> JPDO_CURSOR_SCROLL));
        $sth->execute();
        return $sth;
    }

    public function limitQuery ($queryString, $limitOffset = null, $limitCount = null){
        if ($limitOffset !== null && $limitCount !== null){
           if($this->dbms == 'mysql'){
               $queryString.= ' LIMIT '.intval($limitOffset).','. intval($limitCount);
           }elseif($this->dbms == 'pgsql'){
               $queryString.= ' LIMIT '.intval($limitCount).' OFFSET '.intval($limitOffset);
           }
        }
        $result = $this->query ($queryString);
        return $result;
    }

    /**
    * sets the autocommit state
    * @param boolean state the status of autocommit
    */
    public function setAutoCommit($state=true){
        $this->setAttribute(JPDO_ATTR_AUTOCOMMIT,$state);
    }


    public function lastIdInTable($fieldName, $tableName){
      $rs = $this->query ('SELECT MAX('.$fieldName.') as ID FROM '.$tableName);
      if (($rs !== null) && $r = $rs->fetch ()){
         return $r->ID;
      }
      return 0;
    }

}
?>