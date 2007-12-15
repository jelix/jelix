<?php
/**
* @package     jelix
* @subpackage  db
* @author      Laurent Jouanneau
* @contributor Julien Issler
* @copyright   2005-2006 Laurent Jouanneau
* @copyright   2007 Julien Issler
*
* This class was get originally from the Copix project (CopixDbConnection, Copix 2.3dev20050901, http://www.copix.org)
* However only few lines of code are still copyrighted 2001-2005 CopixTeam (LGPL licence).
* Initial authors of this Copix classes are Gerald Croes and Laurent Jouanneau,
* and this class was adapted/improved for Jelix by Laurent Jouanneau
*
* @link        http://www.jelix.org
* @licence     http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*/

/**
 * @package  jelix
 * @subpackage db
 */
abstract class jDbConnection {

    /**
    * profil properties used by the connector
    * @var array
    */
    public $profil;

    /**
     * The database type name (mysql, pgsql ...)
     */
    public $dbms;

    /**
    * The last error message if any
    * @var string
    */
    public $msgError = '';

    /**
     * last executed query
     */
    public $lastQuery;

    /**
    * Are we using an automatic commit ?
    * @var boolean
    */
    private $_autocommit = true;

    /**
    * the internal connection.
    */
    protected $_connection = null;

    /**
    * do a connection to the database, using properties of the given profil
    * @param array $profil  profil properties
    */
    function __construct($profil){
        $this->profil = & $profil;
        $this->dbms = $profil['driver'];
        $this->_connection = $this->_connect();
    }

    function __destruct(){
        if($this->_connection !== null){
            $this->_disconnect ();
        }
    }

    /**
    * Launch a SQL Query which returns rows (typically, a SELECT statement)
    * @param   string   $queryString   the SQL query
    * @return  jDbResultSet|boolean  False if the query has failed.
    */
    public function query ($queryString){
        $this->lastQuery = $queryString;
        $result = $this->_doQuery ($queryString);
        return $result;
    }

    /**
    * Launch a SQL Query with limit parameter (so only a subset of a result)
    * @param   string   $queryString   the SQL query
    * @param   integer  $limitOffset   the offset of the first row to return
    * @param   integer  $limitCount    the maximum of number of rows to return
    * @return  jDbResultSet|boolean  SQL Select. False if the query has failed.
    */
    public function limitQuery ($queryString, $limitOffset, $limitCount){
        $this->lastQuery = $queryString;
        $result = $this->_doLimitQuery ($queryString, intval($limitOffset), intval($limitCount));
        return $result;
    }

    /**
    * Launch a SQL Query (update, delete..) which doesn't return rows
    * @param   string   $query   the SQL query
    * @return  integer  the number of affected rows. False if the query has failed.
    */
    public function exec ($query){
        $this->lastQuery = $query;
        $result = $this->_doExec ($query);
        return $result;
    }

    /**
    * Escape and quotes strings. if null, will only return the text "NULL"
    * @param string $text   string to quote
    * @param boolean $checknull if true, check if $text is a null value, and then return NULL
    * @return string escaped string
    */
    public function quote($text, $checknull=true){
        if($checknull)
            return (is_null ($text) ? 'NULL' : "'".$this->_quote($text)."'");
        else
            return "'".$this->_quote ($text)."'";
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
        return (isset($this->profil['table_prefix']) && $this->profil['table_prefix'] != '');
    }

    /**
    * sets the autocommit state
    * @param boolean state the status of autocommit
    */
    public function setAutoCommit($state=true){
        $this->_autocommit = $state;
        $this->_autoCommitNotify ($this->_autocommit);
    }

    /**
     * begin a transaction. Call it after doQuery, doLimitQuery, doExec,
     * And then commit() or rollback()
     */
    abstract public function beginTransaction ();

    /**
     * validate all queries and close a transaction
     */
    abstract public function commit ();

    /**
     * cancel all queries of a transaction and close the transaction
     */
    abstract public function rollback ();

    /**
     * prepare a query
     * @param string $query a sql query with parameters
     * @return statement a statement
     */
    abstract public function prepare ($query);

    /**
     * @return string the last error description
     */
    abstract public function errorInfo();

    /**
     * @return integer the last error code
     */
    abstract public function errorCode();

    /**
     * return the id value of the last inserted row.
     * Some driver need a sequence name, so give it at first parameter
     * @param string $fromSequence the sequence name
     * @return integer the id value
     */
    abstract public function lastInsertId($fromSequence='');

    /**
     * Not implemented
     * @param integer $id the attribut id
     * @return string the attribute value
     */
    public function getAttribute($id){ return '';}

    /**
     * Not implemented
     * @param integer $id the attribut id
     * @param string $value the attribute value
     */
    public function setAttribute($id, $value){ }

    /**
     *
     */
    public function lastIdInTable($fieldName, $tableName){
        $rs = $this->query ('SELECT MAX('.$fieldName.') as ID FROM '.$tableName);
        if (($rs !== null) && $r = $rs->fetch ()){
            return $r->ID;
        }
        return 0;
    }

    /**
    * Notify the changes on autocommit
    * Drivers may overload this
    * @param boolean state the new state of autocommit
    */
    abstract protected function _autoCommitNotify ($state);

    /**
    * return a connection identifier or false/null if there is an error
    * @return integer connection identifier
    */
    abstract protected function _connect ();

    /**
    * do a disconnection
    * (no need to do a test on the connection id)
    */
    abstract protected function _disconnect ();

    /**
    * do a query which return results
    * @return jDbResultSet/boolean
    */
    abstract protected function _doQuery ($queryString);
    /**
    * do a query which return nothing
    * @return jDbResultSet/boolean
    */
    abstract protected function _doExec ($queryString);

    /**
    * do a query which return a limited number of results
    * @return jDbResultSet/boolean
    */
    abstract protected function _doLimitQuery ($queryString, $offset, $number);

    /**
    * do the escaping of a string.
    * you should override it into the driver
    */
    protected function _quote($text){
        return addslashes($text);
    }
}
?>