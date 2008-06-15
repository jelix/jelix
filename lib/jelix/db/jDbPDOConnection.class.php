<?php
/**
* @package    jelix
* @subpackage db
* @author     Laurent Jouanneau
* @contributor Gwendal Jouannic
* @copyright  2005-2006 Laurent Jouanneau
* @copyright  2008 Gwendal Jouannic
* @link      http://www.jelix.org
* @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*/

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
     * @param integer $column_index
     * @param array $ctor_arg  (ignored)
     * @return array list of object which contain all rows
     */
    public function fetchAll ( $fetch_style = jDbPDOConnection::JPDO_FETCH_OBJ, $column_index=0, $ctor_arg=null ){
        if($this->_fetchMode){
            if( $this->_fetchMode != jDbPDOConnection::JPDO_FETCH_COLUMN)
                return parent::fetchAll($this->_fetchMode);
            else
                return parent::fetchAll($this->_fetchMode, $column_index);
        }else{
            return parent::fetchAll( jDbPDOConnection::JPDO_FETCH_OBJ);
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

    /**
    * PDO constant name have been change between php 5.0 and 5.1. So we use our own constant.
    * @link http://lxr.php.net/source/php-src/ext/pdo/php_pdo_driver.h
    * @since 1.0
    */
    const JPDO_FETCH_OBJ = 5; // PDO::FETCH_OBJ
    const JPDO_FETCH_ORI_NEXT = 0; // PDO::FETCH_ORI_NEXT
    const JPDO_FETCH_ORI_FIRST = 2;
    const JPDO_FETCH_COLUMN = 7; // PDO::FETCH_COLUMN
    const JPDO_FETCH_CLASS = 8; // PDO::FETCH_CLASS
    const JPDO_ATTR_STATEMENT_CLASS = 13; //PDO::ATTR_STATEMENT_CLASS
    const JPDO_ATTR_AUTOCOMMIT = 0; //PDO::ATTR_AUTOCOMMIT
    const JPDO_ATTR_CURSOR = 10; // PDO::ATTR_CURSOR
    const JPDO_CURSOR_SCROLL = 1; //PDO::CURSOR_SCROLL
    const JPDO_ATTR_ERRMODE = 3; // PDO::ATTR_ERRMODE
    const JPDO_ERRMODE_EXCEPTION = 2; // PDO::ERRMODE_EXCEPTION
    const JPDO_MYSQL_ATTR_USE_BUFFERED_QUERY = 1000; // PDO::MYSQL_ATTR_USE_BUFFERED_QUERY
    const JPDO_ATTR_CASE = 8; // PDO::ATTR_CASE
    const JPDO_CASE_LOWER = 2; // PDO::CASE_LOWER

    private $_mysqlCharsets =array( 'UTF-8'=>'utf8', 'ISO-8859-1'=>'latin1');
    private $_pgsqlCharsets =array( 'UTF-8'=>'UNICODE', 'ISO-8859-1'=>'LATIN1');

    /**
     * the profil the connection is using
     * @var array
     */
    public $profil;

    /**
     * The database type name (mysql, pgsql ...)
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
        $this->setAttribute(self::JPDO_ATTR_STATEMENT_CLASS, array('jDbPDOResultSet'));
        $this->setAttribute(self::JPDO_ATTR_ERRMODE, self::JPDO_ERRMODE_EXCEPTION);
        // on ne peut pas lancer deux query en même temps avec PDO ! sauf si on utilise mysql
        // et que l'on utilise cet attribut...
        if($this->dbms == 'mysql')
            $this->setAttribute(self::JPDO_MYSQL_ATTR_USE_BUFFERED_QUERY, true);
    
        // Oracle renvoie les noms de colonnes en majuscules, il faut donc forcer la casse en minuscules
        if ($this->dbms == 'oci')
            $this->setAttribute(self::JPDO_ATTR_CASE, self::JPDO_CASE_LOWER);            
            
        if(isset($prof['force_encoding']) && $prof['force_encoding']==true){
            if($this->dbms == 'mysql' && isset($this->_mysqlCharsets[$GLOBALS['gJConfig']->charset])){
                $this->exec("SET CHARACTER SET '".$this->_mysqlCharsets[$GLOBALS['gJConfig']->charset]."'");
            }elseif($this->dbms == 'pgsql' && isset($this->_pgsqlCharsets[$GLOBALS['gJConfig']->charset])){
                $this->exec("SET client_encoding to '".$this->_pgsqlCharsets[$GLOBALS['gJConfig']->charset]."'");
            }
        }
    }

    /**
     * @internal the implementation of Iterator on PDOStatement doesn't call fetch method of classes which inherit of PDOStatement
     * so, we cannot indicate to fetch object directly in jDbPDOResultSet::fetch(). So we overload query() to do it.
     */
    public function query(){
        $args=func_get_args();
        switch(count($args)){
        case 1:
            $rs = parent::query($args[0]);
            $rs->setFetchMode(self::JPDO_FETCH_OBJ);
            return $rs;
            break;
        case 2:
            return parent::query($args[0], $args[1]);
            break;
        case 3:
            return parent::query($args[0], $args[1]);
            break;
        default:
            trigger_error('bad argument number in query',E_USER_ERROR);
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
        $this->setAttribute(self::JPDO_ATTR_AUTOCOMMIT,$state);
    }

    public function lastIdInTable($fieldName, $tableName){
      $rs = $this->query ('SELECT MAX('.$fieldName.') as ID FROM '.$tableName);
      if (($rs !== null) && $r = $rs->fetch ()){
         return $r->ID;
      }
      return 0;
    }

    /**
     * Prefix the given table with the prefix specified in the connection's profile
     * If there's no prefix for the connection's profile, return the table's name unchanged.
     *
     * @param string $table the table's name
     * @return string the prefixed table's name
     * @author Julien Issler
     * @since 1.0
     */
    public function prefixTable($table_name){
        if(!isset($this->profil['table_prefix']))
            return $table_name;
        return $this->profil['table_prefix'].$table_name;
    }

    /**
     * Check if the current connection has a table prefix set
     *
     * @return boolean
     * @author Julien Issler
     * @since 1.0
     */
    public function hasTablePrefix(){
        return (isset($this->profil['table_prefix']) && $this->profil['table_prefix']!='');
    }
}
?>