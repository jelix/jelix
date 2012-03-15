<?php
/**
* @package    jelix
* @subpackage db_driver
* @author     Gérald Croes, Laurent Jouanneau
* @contributor Laurent Jouanneau
* @contributor Sylvain de Vathaire, Julien Issler
* @copyright  2001-2005 CopixTeam, 2005-2012 Laurent Jouanneau
* @copyright  2009 Julien Issler
* This class was get originally from the Copix project (CopixDbConnectionMysql, Copix 2.3dev20050901, http://www.copix.org)
* Few lines of code are still copyrighted 2001-2005 CopixTeam (LGPL licence).
* Initial authors of this Copix class are Gerald Croes and Laurent Jouanneau,
* and this class was adapted for Jelix by Laurent Jouanneau
*
* @link      http://www.jelix.org
* @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*/
require_once(dirname(__FILE__).'/mysqli.dbresultset.php');

/**
 *
 * @package    jelix
 * @subpackage db_driver
 */
class mysqliDbConnection extends jDbConnection {

    protected $_charsets =array( 'UTF-8'=>'utf8', 'ISO-8859-1'=>'latin1');

    function __construct($profile){
        // à cause du @, on est obligé de tester l'existence de mysql, sinon en cas d'absence
        // on a droit à un arret sans erreur
        if(!function_exists('mysql_connect')){
            throw new jException('jelix~db.error.nofunction','mysql');
        }
        parent::__construct($profile);
    }

    /**
     * enclose the field name
     * @param string $fieldName the field name
     * @return string the enclosed field name
     * @since 1.1.1
     */
    public function encloseName($fieldName){
        return '`'.$fieldName.'`';
    }

    /**
    * begin a transaction
    */
    public function beginTransaction (){
        $this->_autoCommitNotify(false);
    }

    /**
    * Commit since the last begin
    */
    public function commit (){
        $this->_connection->commit();
        $this->_autoCommitNotify(true);
    }

    /**
    * Rollback since the last begin
    */
    public function rollback (){
        $this->_connection->rollback();
        $this->_autoCommitNotify(true);
    }

    /**
    * 
    */
    public function prepare ($query){
        $res = $this->_connection->prepare($query);
        if($res){
            $rs= new mysqliDbResultSet ($res);
        }else{
            throw new jException('jelix~db.error.query.bad',  $this->_connection->error.'('.$query.')');
        }
        return $rs;
    }

    public function errorInfo(){
        return array( 'HY000' ,$this->_connection->errno, $this->_connection->error);
    }

    public function errorCode(){
       return $this->_connection->errno;
    }

    protected function _connect (){
        $host = ($this->profile['persistent']) ? 'p:'.$this->profile['host'] : $this->profile['host'];
        $cnx = @new mysqli ($host, $this->profile['user'], $this->profile['password'], $this->profile['database']);
        if ($cnx->connect_errno) {
            throw new jException('jelix~db.error.connection',$this->profile['host']);
        }
        else{
            if(isset($this->profile['force_encoding']) && $this->profile['force_encoding'] == true
              && isset($this->_charsets[jApp::config()->charset])){
                $cnx->set_charset($this->_charsets[jApp::config()->charset]);
            }
            return $cnx;
        }
    }

    protected function _disconnect (){
        return $this->_connection->close();
    }


    protected function _doQuery ($query){


        // ici et non lors du connect, pour le cas où il y a plusieurs connexion active

        /* TODO : doit-on le faire ici ?
        if(!mysql_select_db ($this->profile['database'], $this->_connection)){
            if(mysql_errno($this->_connection))
                throw new jException('jelix~db.error.database.unknown',$this->profile['database']);
            else
                throw new jException('jelix~db.error.connection.closed',$this->profile['name']);
        }*/

        
        if ($qI = $this->_connection->query($query)){
            return new mysqliDbResultSet ($qI);
        }else{
            throw new jException('jelix~db.error.query.bad',  $this->_connection->error.'('.$query.')');
        }
    }

    protected function _doExec($query){
        /* TODO : pareil que pour _doQuery
        if(!mysql_select_db ($this->profile['database'], $this->_connection))
            throw new jException('jelix~db.error.database.unknown',$this->profile['database']);
        */

        if ($qI = mysql_query ($query, $this->_connection)){
            return $this->_connection->affected_rows;
        }else{
            throw new jException('jelix~db.error.query.bad',  $this->_connection->error.'('.$query.')');
        }
    }

    protected function _doLimitQuery ($queryString, $offset, $number){
        $queryString.= ' LIMIT '.$offset.','.$number;
        $this->lastQuery = $queryString;
        $result = $this->_doQuery($queryString);
        return $result;
    }


    public function lastInsertId($fromSequence=''){// on n'a pas besoin de l'argument pour mysqli
        return $this->_connection->insert_id;
    }

    /**
    * tell mysql to be autocommit or not
    * @param boolean $state the state of the autocommit value
    * @return void
    */
    protected function _autoCommitNotify ($state){
        $this->_connection->autocommit($state);
    }

    /**
     * @return string escaped text or binary string
     */
    protected function _quote($text, $binary) {
        return $this->_connection->real_escape_string($text);
    }

    /**
     *
     * @param integer $id the attribut id
     * @return string the attribute value
     * @see PDO::getAttribute()
     */
    public function getAttribute($id) {
        switch($id) {
            case self::ATTR_CLIENT_VERSION:
                return $this->_connection->get_client_info();
            case self::ATTR_SERVER_VERSION:
                return $this->_connection->server_info;
                break;
            case self::ATTR_SERVER_INFO:
                return $this->_connection->host_info;
        }
        return "";
    }

    /**
     * 
     * @param integer $id the attribut id
     * @param string $value the attribute value
     * @see PDO::setAttribute()
     */
    public function setAttribute($id, $value) {
    }

}
