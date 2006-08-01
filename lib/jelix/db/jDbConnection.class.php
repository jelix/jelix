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

abstract class jDbConnection {

    /**
    * the profil the connection is using
    * @var array
    */
    public $profil;

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
    * @constructor
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
    * Launch a SQL Query
    * @param   string   $queryString   the SQL query
    * @return  jDbResultSet  if SQL Select.
    *          boolean if update / insert / delete.
    *          False if the query has failed.
    */
    public function query ($queryString){
        $this->lastQuery = $queryString;
        $result = $this->_doQuery ($queryString);
        return $result;
    }

    public function limitQuery ($queryString, $limitOffset, $limitCount){
        $this->lastQuery = $queryString;
        $result = $this->_doLimitQuery ($queryString, intval($limitOffset), intval($limitCount));
        return $result;
    }

    public function exec ($query){
        $this->lastQuery = $query;
        $result = $this->_doExec ($query);
        return $result;
    }



    /**
    * Escape and quotes strings. if null, will only return the text "NULL"
    * @param string $text   string to quote
    * @return string
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

    abstract public function beginTransaction ();
    abstract public function commit ();
    abstract public function rollback ();
    abstract public function prepare ($query);
    abstract public function errorInfo();
    abstract public function errorCode();
    abstract public function lastInsertId($fromSequence='');

    /**
     * @param integer $id
     */
    public function getAttribute($id){ return '';}
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
    * renvoi la connection, ou false/null si erreur
    * @abstract
    */
    abstract protected function _connect ();

    /**
    * effectue la deconnection (pas besoin de faire le test sur l'id de connection)
    * @abstract
    */
    abstract protected function _disconnect ();

    /**
    * effectue la requete
    * @return jDbResultSet/boolean    selon la requete, un recordset/true ou false/null si il y a une erreur
    * @abstract
    */
    abstract protected function _doQuery ($queryString);
    abstract protected function _doExec ($queryString);

    /**
    * effectue une requete avec liste de résultats limités
    * @return jDbResultSet/boolean    selon la requete, un recordset/true ou false/null si il y a une erreur
    * @abstract
    */
    abstract protected function _doLimitQuery ($queryString, $offset, $number);




    /**
    * renvoi une chaine avec les caractères spéciaux échappés
    * à surcharger pour tenir compte des fonctions propres à la base (mysql_escape_string etC...)
    * @abstract
    * @access private
    */
    protected function _quote($text){
        return addslashes($text);
    }
}
?>
