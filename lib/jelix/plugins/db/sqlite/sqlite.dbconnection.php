<?php
/**
* @package    jelix
* @subpackage db_driver
* @author     Loic Mathaud
* @contributor Laurent Jouanneau
* @copyright  2006 Loic Mathaud, 2007 Laurent Jouanneau
* @link      http://www.jelix.org
* @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*/

/**
 *
 * @package    jelix
 * @subpackage db_driver
 */
class sqliteDbConnection extends jDbConnection {

    function __construct($profil){
        if(!function_exists('sqlite_open')){
            throw new jException('jelix~db.error.nofunction','sqlite');
        }
        parent::__construct($profil);
    }

    /**
    * begin a transaction
    */
    public function beginTransaction (){
        $this->_doExec ('BEGIN');
    }

    /**
    * Commit since the last begin
    */
    public function commit (){
        $this->_doExec ('COMMIT');
    }

    /**
    * Rollback since the last BEGIN
    */
    public function rollback (){
        $this->_doExec ('ROLLBACK');
    }

    /**
    *
    */
    public function prepare ($query){
        throw new jException('jelix~db.error.feature.unsupported', array('sqlite','prepare'));
    }

    public function errorInfo(){
        return array(sqlite_last_error($this->_connection), sqlite_error_string($this->_connection));
    }

    public function errorCode(){
        return sqlite_last_error($this->_connection);
    }

    protected function _connect (){
        $funcconnect= (isset($this->profil['persistent']) && $this->profil['persistent']? 'sqlite_popen':'sqlite_open');
        if ($cnx = @$funcconnect(JELIX_APP_VAR_PATH. 'db/sqlite/'.$this->profil['database'])) {
            return $cnx;
        } else {
            throw new jException('jelix~db.error.connection',$this->profil['database']);
        }
    }

    protected function _disconnect (){
        return sqlite_close($this->_connection);
    }

    protected function _doQuery($query){
        if ($qI = sqlite_query($query, $this->_connection)){
            return new sqliteDbResultSet($qI);
        } else {
            throw new jException('jelix~db.error.query.bad', sqlite_error_string($this->_connection).'('.$query.')');
        }
    }

    protected function _doExec($query){
        if ($qI = sqlite_query($query, $this->_connection)){
            return sqlite_changes($this->_connection);
        } else {
            throw new jException('jelix~db.error.query.bad', sqlite_error_string($this->_connection).'('.$query.')');
        }
    }

    protected function _doLimitQuery ($queryString, $offset, $number){
        $queryString.= ' LIMIT '.$offset.','.$number;
        $result = $this->_doQuery($queryString);
        return $result;
    }

    public function lastInsertId($fromSequence=''){// on n'a pas besoin de l'argument pour mysql
        return sqlite_last_insert_rowid($this->_connection);
    }

    /**
    * tell mysql to be autocommit or not
    * @param boolean state the state of the autocommit value
    * @return void
    */
    protected function _autoCommitNotify ($state){
        $this->query ('SET AUTOCOMMIT='.$state ? '1' : '0');
    }

    /**
    * renvoi une chaine avec les caractères spéciaux échappés
    * @access private
    */
    protected function _quote($text){
        return sqlite_escape_string($text);
    }

}
?>
