<?php
/**
 * @package    jelix
 * @subpackage db_driver
 * @author     Yann Lecommandoux
 * @copyright  2008 Yann Lecommandoux
 * @link     http://www.jelix.org
 * @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */

/**
 * @experimental
 */
class mssqlDbConnection extends jDbConnection {

    /**
     * Default constructor
     * @param array $profile profile de connexion
     * @return unknown_type
     */
    function __construct($profile){
        if(!function_exists('mssql_connect')){
            throw new jException('jelix~db.error.nofunction','mssql');
        }
        parent::__construct($profile);
    }

    /**
     * begin a transaction
     */
    public function beginTransaction (){
        $this->_doExec ('SET IMPLICIT_TRANSACTIONS OFF');
        $this->_doExec ('BEGIN TRANSACTION');
    }

    /**
     * Commit since the last begin
     */
    public function commit (){
        $this->_doExec ('COMMIT TRANSACTION');
        $this->_doExec ('SET IMPLICIT_TRANSACTIONS ON');
    }

    /**
     * Rollback since the last BEGIN
     */
    public function rollback (){
        $this->_doExec ('ROLLBACK TRANSACTION');
        $this->_doExec ('SET IMPLICIT_TRANSACTIONS ON');
    }

    /**
     *
     */
    public function prepare ($query){
        throw new jException('jelix~db.error.feature.unsupported', array('mssql','prepare'));
    }

    public function errorInfo(){
        return array( 'HY000', mssql_get_last_message());
    }

    public function errorCode(){
        return mssql_get_last_message();
    }
     
    /**
     * (non-PHPdoc)
     * initialize the connection to the database
     * @see lib/jelix/db/jDbConnection#_connect()
     */
    protected function _connect (){
        $funcconnect = ($this->profile['persistent']? 'mssql_pconnect':'mssql_connect');
        if($cnx = @$funcconnect ($this->profile['host'], $this->profile['user'], $this->profile['password'])){
            /*if(isset($this->profile['force_encoding']) && $this->profile['force_encoding'] == true
            && isset($this->_charsets[$GLOBALS['gJConfig']->charset])){
                mssql_query("SET ANSI_DEFAULTS ON", $cnx);
            }*/
            return $cnx;
        }else{
            throw new jException('jelix~db.error.connection',$this->profile['host']);
        }
    }

    /**
     * (non-PHPdoc)
     * 	close the connection to the database
     * @see lib/jelix/db/jDbConnection#_disconnect()
     */
    protected function _disconnect (){
        return mssql_close ($this->_connection);
    }

    /**
     * (non-PHPdoc)
     * 	execute an SQL instruction
     * @see lib/jelix/db/jDbConnection#_doQuery()
     */
    protected function _doQuery ($query){
        if(!mssql_select_db ($this->profile['database'], $this->_connection)){
            if(mssql_get_last_message()){
                throw new jException('jelix~db.error.database.unknown',$this->profile['database']);
            } else {
                throw new jException('jelix~db.error.connection.closed',$this->profile['name']);
            }
        }

        if ($qI = mssql_query ($query, $this->_connection)){
            return new mssqlDbResultSet ($qI);
        } else{
            throw new jException('jelix~db.error.query.bad',  mssql_get_last_message());
        }
    }
     
    /**
     * (non-PHPdoc)
     * @see lib/jelix/db/jDbConnection#_doExec()
     */
    protected function _doExec($query){
        if(!mssql_select_db ($this->profile['database'], $this->_connection))
        throw new jException('jelix~db.error.database.unknown',$this->profile['database']);

        if ($qI = mssql_query ($query, $this->_connection)){
            return mssql_rows_affected($this->_connection);
        }else{
            throw new jException('jelix~db.error.query.bad', mssql_get_last_message());
        }
    }
    /**
     * WARNING: it doesn't take care about offset and number.
     * @notimplemented
     * @see lib/jelix/db/jDbConnection#_doLimitQuery()
     */
    protected function _doLimitQuery ($queryString, $offset, $number){
        $result = $this->_doQuery($queryString);
        return $result;
    }

    /**
     * (non-PHPdoc)
     * 	return the last inserted ID incremented in database
     * @see lib/jelix/db/jDbConnection#lastInsertId()
     */
    public function lastInsertId($fromSequence=''){
        $queryString = 'SELECT @@IDENTITY AS id';
        $result = $this->_doQuery($queryString);
        return $result;
    }

    /**
     * tell mssql to be implicit commit or not
     * @param boolean $state the state of the autocommit value
     * @return void
     */
    protected function _autoCommitNotify ($state){
        if ($state == 1 ){
            $this->query ('SET IMPLICIT_TRANSACTIONS ON');
        } else {
            $this->query ('SET IMPLICIT_TRANSACTIONS OFF');
        }
    }

    /**
     * escape special characters
     * @todo support of binary strings
     */
    protected function _quote($text, $binary){
        return str_replace( "'", "''", $text );
    }
}
