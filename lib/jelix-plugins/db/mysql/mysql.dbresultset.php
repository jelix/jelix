<?php
/**
* @package    jelix
* @subpackage db
* @version    $Id:$
* @author     Croes Gérald, Laurent Jouanneau
* @contributor Laurent Jouanneau
* @copyright  2001-2005 CopixTeam, 2005-2006 Laurent Jouanneau
* @link      http://www.jelix.org
* @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*
* Classe orginellement issue du framework Copix 2.3dev20050901. http://www.copix.org (CopixDbResultsetMysql)
* Une partie du code est sous Copyright 2001-2005 CopixTeam (licence LGPL)
* Auteurs initiaux : Gerald Croes et Laurent Jouanneau
* Adaptée pour Jelix par Laurent Jouanneau
*/

/**
 *
 * Couche d'encapsulation des resultset mysql.
 * @package    jelix
 * @subpackage db
 */
class mysqlDbResultSet extends jDbResultSet {

    protected function  _fetch (){
        $ret =  mysql_fetch_object ($this->_idResult);
        return $ret;
    }
    protected function _free (){
        return mysql_free_result ($this->_idResult);
    }
    protected function _rewind (){
        return @mysql_data_seek ( $this->_idResult, 0);
    }

    public function rowCount(){
        return mysql_num_rows($this->_idResult);
    }

    public function bindColumn($column, &$param , $type=null )
      {throw new jException('jelix~db.error.feature.unsupported', array('mysql','bindColumn')); }
    public function bindParam($parameter, &$variable , $data_type =null, $length=null,  $driver_options=null)
      {throw new jException('jelix~db.error.feature.unsupported', array('mysql','bindParam')); }
    public function bindValue($parameter, $value, $data_type)
      {throw new jException('jelix~db.error.feature.unsupported', array('mysql','bindValue')); }
    public function columnCount()
      { return mysql_num_fields($this->_idResult); }
    public function execute($parameters=null)
      {throw new jException('jelix~db.error.feature.unsupported', array('mysql','bindColumn')); }
}
?>
