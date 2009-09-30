<?php
/**
* @package    jelix
* @subpackage db
* @author     Laurent Jouanneau
* @contributor Gwendal Jouannic, Thomas, Julien Issler
* @copyright  2005-2006 Laurent Jouanneau
* @copyright  2008 Gwendal Jouannic, 2009 Thomas
* @copyright  2009 Julien Issler
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
    public function fetchAll ( $fetch_style = PDO::FETCH_OBJ, $column_index=0, $ctor_arg=null ){
        if($this->_fetchMode){
            if( $this->_fetchMode != PDO::FETCH_COLUMN)
                return parent::fetchAll($this->_fetchMode);
            else
                return parent::fetchAll($this->_fetchMode, $column_index);
        }else{
            return parent::fetchAll(PDO::FETCH_OBJ);
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
     * the profile the connection is using
     * @var array
     */
    public $profile;

    /**
     * The database type name (mysql, pgsql ...)
     */
    public $dbms;

    /**
     * Use a profile to do the connection
     */
    function __construct($profile){
        $this->profile = $profile;
        $this->dbms = substr($profile['dsn'],0,strpos($profile['dsn'],':'));
        $prof=$profile;
        $user= '';
        $password='';
        unset($prof['dsn']);
        if(isset($prof['user'])){ // sqlite par ex n'a pas besoin de user/password -> on test alors leur presence
            $user =$prof['user'];
            unset($prof['user']);
        }
        if(isset($prof['password'])){
            $password = $profile['password'];
            unset($prof['password']);
        }
        unset($prof['driver']);
        parent::__construct($profile['dsn'], $user, $password, $prof);
        $this->setAttribute(PDO::ATTR_STATEMENT_CLASS, array('jDbPDOResultSet'));
        $this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        // on ne peut pas lancer deux query en même temps avec PDO ! sauf si on utilise mysql
        // et que l'on utilise cet attribut...
        if($this->dbms == 'mysql')
            $this->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);

        // Oracle renvoie les noms de colonnes en majuscules, il faut donc forcer la casse en minuscules
        if ($this->dbms == 'oci')
            $this->setAttribute(PDO::ATTR_CASE, PDO::CASE_LOWER);

        if(isset($prof['force_encoding']) && $prof['force_encoding']==true){
            if($this->dbms == 'mysql' && isset($this->_mysqlCharsets[$GLOBALS['gJConfig']->charset])){
                $this->exec("SET NAMES '".$this->_mysqlCharsets[$GLOBALS['gJConfig']->charset]."'");
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
            $rs->setFetchMode(PDO::FETCH_OBJ);
            return $rs;
            break;
        case 2:
            return parent::query($args[0], $args[1]);
            break;
        case 3:
            return parent::query($args[0], $args[1], $args[2]);
            break;
        default:
            trigger_error('bad argument number in query',E_USER_ERROR);
        }

    }

    public function limitQuery ($queryString, $limitOffset = null, $limitCount = null){
        if ($limitOffset !== null && $limitCount !== null){
           if($this->dbms == 'mysql' || $this->dbms == 'sqlite'){
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
        $this->setAttribute(PDO::ATTR_AUTOCOMMIT,$state);
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
        if(!isset($this->profile['table_prefix']))
            return $table_name;
        return $this->profile['table_prefix'].$table_name;
    }

    /**
     * Check if the current connection has a table prefix set
     *
     * @return boolean
     * @author Julien Issler
     * @since 1.0
     */
    public function hasTablePrefix(){
        return (isset($this->profile['table_prefix']) && $this->profile['table_prefix']!='');
    }

    /**
     * enclose the field name
     * @param string $fieldName the field name
     * @return string the enclosed field name
     * @since 1.1.2
     */
    public function encloseName($fieldName){
        switch($this->dbms){
            case 'mysql': return '`'.$fieldName.'`';
            case 'pgsql': return '"'.$fieldName.'"';
            default: return $fieldName;
        }
    }

}
