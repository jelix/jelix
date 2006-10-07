<?php
/**
* @package    jelix
* @subpackage db
* @version    $Id:$
* @author      Laurent Jouanneau
* @contributor
* @copyright  2005-2006 Laurent Jouanneau
* @link      http://www.jelix.org
* @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*
* Quelques lignes de codes sont issues de la classe CopixDbConnection
* du framework Copix 2.3dev20050901. http://www.copix.org
* Elles sont sous Copyright 2001-2005 CopixTeam (licence LGPL)
* Auteurs initiaux : Gerald Croes et Laurent Jouanneau
* Adaptée et améliorée pour Jelix par Laurent Jouanneau
*/

/**
 * @package  jelix
 * @subpackage db
 */
abstract class jDbConnection {

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
    * The last error message if any
    * @var string
    */
    public $msgError = '';

    public $lastQuery;

    /**
    * Are we using an automatic commit ?
    * @var boolean
    */
    private $_autocommit = true;

    /**
    * indique si les requètes doivent être envoyée sur le debugger
    * @var boolean
    */
    //protected $_debugQuery = false;

    /**
    * the internal connection.
    */
    protected $_connection = null;



    /**
    * Use a profil to do the connection
    */
    function __construct($profil){
       $this->profil = & $profil;
       $this->dbms = $profil['driver'];
       $this->_connection=$this->_connect();
    }

    function __destruct(){
        if($this->_connection !== null){
           $this->_disconnect ();
        }
    }


    /**
    * Launch a SQL Query which returns rows
    * @param   string   $queryString   the SQL query
    * @return  jDbResultSet  False if the query has failed.
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
    * @return  jDbResultSet  if SQL Select.
    *          boolean if update / insert / delete.
    *          False if the query has failed.
    */
    public function limitQuery ($queryString, $limitOffset, $limitCount){
        $this->lastQuery = $queryString;
        $result = $this->_doLimitQuery ($queryString, intval($limitOffset), intval($limitCount));
        return $result;
    }

    /**
    * Launch a SQL Query (update, delete..)
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
    * @return string escaped string
    */
    public function quote($text, $checknull=true){
        if($checknull)
           return (is_null ($text) ? 'NULL' : "'".$this->_quote($text)."'");
        else
           return "'".$this->_quote ($text)."'";
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
     * begin a transaction. Call after it doQuery, doLimitQuery, doExec. And then commit() or rollback()
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



    public function lastIdInTable($fieldName, $tableName){
      $rs = $this->query ('SELECT MAX('.$fieldName.') as ID FROM '.$tableName);
      if (($rs !== null) && $r = $rs->fetch ()){
         $rs->free();
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
    * do a query
    * @return jDbResultSet/boolean    selon la requete, un recordset/true ou false/null si il y a une erreur
    */
    abstract protected function _doQuery ($queryString);
    abstract protected function _doExec ($queryString);

    /**
    * effectue une requete avec liste de résultats limités
    * @return jDbResultSet/boolean    selon la requete, un recordset/true ou false/null si il y a une erreur
    */
    abstract protected function _doLimitQuery ($queryString, $offset, $number);

    /**
    * renvoi une chaine avec les caractères spéciaux échappés
    * à surcharger pour tenir compte des fonctions propres à la base (mysql_escape_string etC...)
    */
    protected function _quote($text){
        return addslashes($text);
    }
}
?>
