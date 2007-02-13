<?php
/**
* @package    jelix
* @subpackage db
* @author     Laurent Jouanneau
* @contributor
* @copyright  2005-2006 Laurent Jouanneau
* @link      http://www.jelix.org
* @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*/
#ifnot ENABLE_PHP_JELIX
/**
 * PDO constant name have been change between php 5.0 and 5.1. So we use our own constant.
 * @link http://lxr.php.net/source/php-src/ext/pdo/php_pdo_driver.h
 */
define('JPDO_FETCH_OBJ',5); // PDO::FETCH_OBJ
define('JPDO_FETCH_ORI_NEXT',0); // PDO::FETCH_ORI_NEXT
define('JPDO_FETCH_ORI_FIRST',2);
define('JPDO_FETCH_COLUMN',7); // PDO::FETCH_COLUMN
define('JPDO_FETCH_CLASS',8); // PDO::FETCH_CLASS
define('JPDO_ATTR_STATEMENT_CLASS',13); //PDO::ATTR_STATEMENT_CLASS
define('JPDO_ATTR_AUTOCOMMIT',0); //PDO::ATTR_AUTOCOMMIT
define('JPDO_ATTR_CURSOR',10); // PDO::ATTR_CURSOR
define('JPDO_CURSOR_SCROLL',1); //PDO::CURSOR_SCROLL
define('JPDO_ATTR_ERRMODE',3); // PDO::ATTR_ERRMODE
define('JPDO_ERRMODE_EXCEPTION',2); // PDO::ERRMODE_EXCEPTION
define('JPDO_MYSQL_ATTR_USE_BUFFERED_QUERY',1000); // PDO::MYSQL_ATTR_USE_BUFFERED_QUERY
#endif
/**
 * a resultset based on PDOStatement
 * @package  jelix
 * @subpackage db
 */
class jDbPDOResultSet extends PDOStatement {

    const FETCH_CLASS = 8;

    protected $_fetchMode = 0;

    /**
     * return all results from the statement.
     * Arguments are ignored. JDb don't care about it (fetch always as classes or objects)
     * But there are here because of the compatibility of internal methods of PDOStatement
     * @param integer $fetch_style ignored
     * @param integer $column_index ignored
     * @return array list of object which contain all rows
     */
    public function fetchAll ( $fetch_style = JPDO_FETCH_OBJ, $column_index=0 ){
        if($this->_fetchMode){
            if( $this->_fetchMode != JPDO_FETCH_COLUMN)
                return parent::fetchAll($this->_fetchMode);
            else
                return parent::fetchAll($this->_fetchMode, $column_index);
        }else{
            return parent::fetchAll( JPDO_FETCH_OBJ);
        }
    }

    /**
     * return next result in the resultset.
     * Arguments are ignored. JDb don't care about it (fetch always as classes or objects)
     * But there are here because of the compatibility of internal methods of PDOStatement
     * @param integer $fetch_style ignored
     * @param integer $cur_or ignored
     * @param integer $cur_offset  ignored
     * @return array an object which contains datas of a row
     */
    public function fetch( $fetch_style= null, $cur_or=JPDO_FETCH_ORI_NEXT, $cur_offset=0 ){
        if($this->_fetchMode){
            return parent::fetch($this->_fetchMode, $cur_or, $cur_offset);
        }else{
            return parent::fetch(JPDO_FETCH_OBJ,$cur_or,$cur_offset);
        }
    }

    /**
     * Set the fetch mode.
     */
    public function setFetchMode($mode, $param=null){
        $this->_fetchMode = $mode;
        return parent::setFetchMode($mode, $param);
    }
}


/**
 * A connection object based on PDO
 * @package  jelix
 * @subpackage db
 */
class jDbPDOConnection extends PDO {

    private $_mysqlCharsets =array( 'UTF-8'=>'utf8', 'ISO-8859-1'=>'latin1');
    private $_pgsqlCharsets =array( 'UTF-8'=>'UNICODE', 'ISO-8859-1'=>'LATIN1');
    /**
    * the profil the connection is using
    * @var array
    */
    public $profil;

    /**
     * The database type name (mysql, postgresql ...)
     */
    public $dbms;

    /**
    * Use a profil to do the connection
    */
    function __construct($profil){
        $this->profil = $profil;
        $this->dbms=substr($profil['dsn'],0,strpos($profil['dsn'],':'));
        $prof=$profil;
        $user= '';
        $password='';
        unset($prof['dsn']);
        if(isset($prof['user'])){ // sqlite par ex n'a pas besoin de user/password -> on test alors leur presence
            $user =$prof['user'];
            unset($prof['user']);
        }
        if(isset($prof['password'])){
            $password = $profil['password'];
            unset($prof['password']);
        }
        unset($prof['driver']);
        parent::__construct($profil['dsn'], $user, $password, $prof);
        $this->setAttribute(JPDO_ATTR_STATEMENT_CLASS, array('jDbPDOResultSet'));
        $this->setAttribute(JPDO_ATTR_ERRMODE, JPDO_ERRMODE_EXCEPTION);
        // on ne peut pas lancer deux query en même temps avec PDO ! sauf si on utilise mysql
        // et que l'on utilise cet attribut...
        if($this->dbms == 'mysql')
            $this->setAttribute(JPDO_MYSQL_ATTR_USE_BUFFERED_QUERY, true);
    
        if(isset($prof['force_encoding']) && $prof['force_encoding']==true){
            if($this->dbms == 'mysql' && isset($this->_mysqlCharsets[$GLOBALS['gJConfig']->defaultCharset])){
                $this->exec("SET CHARACTER SET '".$this->_mysqlCharsets[$GLOBALS['gJConfig']->defaultCharset]."'");
            }elseif($this->dbms == 'pgsql' && isset($this->_pgsqlCharsets[$GLOBALS['gJConfig']->defaultCharset])){
                $this->exec("SET client_encoding to '".$this->_pgsqlCharsets[$GLOBALS['gJConfig']->defaultCharset]."'");
            }
        }
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