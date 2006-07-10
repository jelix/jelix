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
* Classe orginellement issue du framework Copix 2.3dev20050901. http://www.copix.org (CopixDBResultSetPostgreSQL)
* Une partie du code est sous Copyright 2001-2005 CopixTeam (licence LGPL)
* Auteurs initiaux : Gerald Croes et Laurent Jouanneau
* Adaptée et améliorée pour Jelix par Laurent Jouanneau
*/

class jDbResultSetPostgreSQL extends jDbResultSet {
	protected $_stmtId;
    protected $_cnt;

    function __construct (  $idResult, $stmtId = null, $cnt=null){
		$this->_idResult = $idResult;
        $this->_stmtId=$stmtId;
        $this->_cnt = $cnt;
	}

    public function fetch(){
        return $this->_fetch();
    }
    protected function _fetch(){
        if($this->_fetchMode == self::FETCH_CLASS){
            $res = pg_fetch_object ($this->_idResult, -1 , $this->_fetchModeParam);
        }else{
            $res = pg_fetch_object ($this->_idResult);
        }

        return $res;
    }
    protected function _free (){
        return pg_free_result ($this->_idResult);
    }

    protected function _rewind (){
        return pg_result_seek ( $this->_idResult, 0 );
    }

    public  function rowCount(){
        return pg_num_rows($this->_idResult);
    }

    public function bindColumn($column, &$param , $type=null )
      {throw new JException('jelix~db.error.feature.unsupported', array('pgsql','bindColumn')); }
    public function bindParam($parameter, &$variable , $data_type =null, $length=null,  $driver_options=null)
       {throw new JException('jelix~db.error.feature.unsupported', array('pgsql','bindParam')); }
    public function bindValue($parameter, $value, $data_type)
       {throw new JException('jelix~db.error.feature.unsupported', array('pgsql','bindValue')); }

    public function columnCount(){
      return pg_num_fields($this->_idResult);
    }

    public function execute($parameters=array()){
        $this->_idResult= pg_execute($this->_cnt,$this->_stmtId, $parameters);
        return true;
    }
}
?>
