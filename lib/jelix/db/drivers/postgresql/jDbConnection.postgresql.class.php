<?php
/**
* @package    jelix
* @subpackage db
* @version    $Id:$
* @author     Croes Gérald, Laurent Jouanneau
* @contributor Laurent Jouanneau
* @copyright  2001-2005 CopixTeam, 2005-2006 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*
* Classe orginellement issue du framework Copix 2.3dev20050901. http://www.copix.org (CopixDBConnectionPostgreSQL)
* Une partie du code est sous Copyright 2001-2005 CopixTeam (licence LGPL)
* Auteurs initiaux : Gerald Croes et Laurent Jouanneau
* Adaptée et améliorée pour Jelix par Laurent Jouanneau
*/


class jDbConnectionPostgreSQL extends jDbConnection {

    public function beginTransaction (){
        return $this->_doQuery('BEGIN');
    }

    public function commit (){
        return $this->_doQuery('COMMIT');
    }
    
    public function rollBack (){
        return $this->_doQuery('ROLLBACK');
    }
    
    public function prepare ($query){
        $id=(string)mktime();
        $res = pg_prepare($this->_connection, $id, $query);
        if($res){
            $rs= new jDbResultSetPostgreSQL ($res, $id, $this->_connection );
        }else{
            throw new JException('jelix~db.error.query.bad',  pg_last_error($this->_connection).'('.$query.')');
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
        $funcconnect= ($this->profil['persistent'] ? 'pg_pconnect':'pg_connect');
    
        $str = 'dbname='.$this->profil['database'].' user='.$this->profil['user'].' password='.$this->profil['password'];
    
        // on fait une distinction car si host indiqué -> connection TCP/IP, sinon socket unix
        if($this->profil['host'] != '')
            $str = 'host='.$this->profil['host'].' '.$str;
        
        // Si le port est défini on le rajoute à la chaine de connexion
        if (isset($this->profil['port'])) {
            $str .= ' port='.$this->profil['port'];
        }

        return $funcconnect ($str);
    }

    protected function _disconnect (){
        return pg_close ($this->_connection);
    }
    
    protected function _doQuery ($queryString){
        if ($qI = pg_query ($this->_connection, $queryString)){
            $rs= new jDbResultSetPostgreSQL ($qI);
            $rs->_connector = $this;
        }else{
            $rs = false;
            throw new JException('jelix~db.error.query.bad',  pg_last_error($this->_connection).'('.$queryString.')');
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
        $cur=$this->query(" select setval('$seqname',    nextval('$seqname')) as id");
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
?>