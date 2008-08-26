<?php
/**
* @package    jelix
* @subpackage db_driver
* @author     Croes Gérald, Laurent Jouanneau
* @contributor Laurent Jouanneau
* @contributor Yannick Le Guédart
* @contributor Laurent Raufaste
* @copyright  2001-2005 CopixTeam, 2005-2007 Laurent Jouanneau, 2007-2008 Laurent Raufaste
* This class was get originally from the Copix project (CopixDBConnectionPostgreSQL, Copix 2.3dev20050901, http://www.copix.org)
* Few lines of code are still copyrighted 2001-2005 CopixTeam (LGPL licence).
* Initial authors of this Copix class are Gerald Croes and Laurent Jouanneau,
* and this class was adapted/improved for Jelix by Laurent Jouanneau
*
* @link        http://www.jelix.org
* @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*/

/**
 *
 * @package    jelix
 * @subpackage db_driver
 */
class pgsqlDbConnection extends jDbConnection {
    protected $_charsets =array( 'UTF-8'=>'UNICODE', 'ISO-8859-1'=>'LATIN1');

    function __construct($profil){
        if(!function_exists('pg_connect')){
            throw new jException('jelix~db.error.nofunction','posgresql');
        }
        parent::__construct($profil);
    }

    public function beginTransaction (){
        return $this->_doExec('BEGIN');
    }

    public function commit (){
        return $this->_doExec('COMMIT');
    }

    public function rollback (){
        return $this->_doExec('ROLLBACK');
    }

    public function prepare ($query){
        $id=(string)mktime();
        $res = pg_prepare($this->_connection, $id, $query);
        if($res){
            $rs= new pgsqlDbResultSet ($res, $id, $this->_connection );
        }else{
            throw new jException('jelix~db.error.query.bad',  pg_last_error($this->_connection).'('.$query.')');
        }
        return $rs;
    }

    public function errorInfo(){
        return array( 'HY000' ,pg_last_error($this->_connection), pg_last_error($this->_connection));
    }

    public function errorCode(){
        return pg_last_error($this->_connection);
    }

    protected function _connect (){
        $funcconnect= (isset($this->profil['persistent']) && $this->profil['persistent'] ? 'pg_pconnect':'pg_connect');

        $str = '';

        // we do a distinction because if the host is given == TCP/IP connection else unix socket
        if($this->profil['host'] != '')
            $str = 'host=\''.$this->profil['host'].'\''.$str;

        if (isset($this->profil['port'])) {
            $str .= ' port=\''.$this->profil['port'].'\'';
        }

        if ($this->profil['database'] != '') {
            $str .= ' dbname=\''.$this->profil['database'].'\'';
        }

        // we do isset instead of equality test against an empty string, to allow to specify
        // that we want to use configuration set in environment variables
        if (isset($this->profil['user'])) {
            $str .= ' user=\''.$this->profil['user'].'\'';
        }

        if (isset($this->profil['password'])) {
            $str .= ' password=\''.$this->profil['password'].'\'';
        }

        if (isset($this->profil['timeout']) && $this->profil['timeout'] != '') {
            $str .= ' connect_timeout=\''.$this->profil['timeout'].'\'';
        }

        if($cnx=@$funcconnect ($str)){
            if(isset($this->profil['force_encoding']) && $this->profil['force_encoding'] == true
               && isset($this->_charsets[$GLOBALS['gJConfig']->charset])){
                pg_set_client_encoding($cnx, $this->_charsets[$GLOBALS['gJConfig']->charset]);
            }
            return $cnx;
        }else{
            throw new jException('jelix~db.error.connection',$this->profil['host']);
        }
    }

    protected function _disconnect (){
        return pg_close ($this->_connection);
    }

    protected function _doQuery ($queryString){
        if ($qI = pg_query ($this->_connection, $queryString)){
            $rs= new pgsqlDbResultSet ($qI);
            $rs->_connector = $this;
        }else{
            $rs = false;
            throw new jException('jelix~db.error.query.bad',  pg_last_error($this->_connection).'('.$queryString.')');
        }
        return $rs;
    }

    protected function _doExec($query){
        if($rs = $this->_doQuery($query)){
            return pg_affected_rows($rs->id());
        }else
            return 0;
    }

    protected function _doLimitQuery ($queryString, $offset, $number){
        if($number < 0)
            $number='ALL';
        $queryString.= ' LIMIT '.$number.' OFFSET '.$offset;
        $result = $this->_doQuery($queryString);
        return $result;
    }




    public function lastInsertId($seqname=''){

        if($seqname == ''){
            trigger_error(get_class($this).'::lastInstertId invalide sequence name',E_USER_WARNING);
            return false;
        }
        $cur=$this->query("select currval('$seqname') as id");
        if($cur){
            $res=$cur->fetch();
            if($res)
                return $res->id;
            else
                return false;
        }else{
            trigger_error(get_class($this).'::lastInstertId invalide sequence name',E_USER_WARNING);
            return false;
        }
    }

    protected function _autoCommitNotify ($state){

        $this->query ('SET AUTOCOMMIT='.$state ? 'on' : 'off');
    }

    protected function _quote($text){
        return pg_escape_string($text);
    }
}

