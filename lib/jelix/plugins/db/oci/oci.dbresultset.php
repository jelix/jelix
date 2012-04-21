<?php
/**
* @package    jelix
* @subpackage db_driver
* @author     Gérald Croes, Laurent Jouanneau
* @contributor Laurent Jouanneau
* @copyright  2001-2005 CopixTeam, 2005-2010 Laurent Jouanneau
* This class was get originally from the Copix project (CopixDBResultSetPostgreSQL, Copix 2.3dev20050901, http://www.copix.org)
* Few lines of code are still copyrighted 2001-2005 CopixTeam (LGPL licence).
* Initial authors of this Copix class are Gerald Croes and Laurent Jouanneau,
* and this class was adapted/improved for Jelix by Laurent Jouanneau
*
* @link        http://www.jelix.org
* @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*/

/**
 * @package    jelix
 * @subpackage db_driver
 */
class ociDbResultSet extends jDbResultSet {
    protected $_stmt;

    function __construct ($stmt) {
        $this->_stmt = $stmt;
    }

    public function fetch() {
		$res = false;
		if(!$this->execute()) {
			return $res;
		}
        if ($this->_fetchMode == jDbConnection::FETCH_CLASS) {
			$res = oci_fetch_object($this->_stmt);
        }
        else if ($this->_fetchMode == jDbConnection::FETCH_INTO) {
            $res = oci_fetch_object ($this->_stmt);
            $values = get_object_vars ($res);
            $res = $this->_fetchModeParam;
            foreach ($values as $k=>$value) {
                $res->$k = $value;
            }
        }
        else {
            $res = oci_fetch_object ($this->_stmt);
        }

        if ($res && count($this->modifier)) {
            foreach($this->modifier as $m)
                call_user_func_array($m, array($res, $this));
        }
        return $res;
    }

    public function statement() { return $this->_stmt; }

    protected function _fetch(){ }

    protected function _free (){
        return oci_free_statement ($this->_stmt);
    }

    public  function rowCount(){
        return oci_num_rows($this->_stmt);
    }

    public function bindColumn($column, &$param , $type=null )
      {throw new jException('jelix~db.error.feature.unsupported', array('oci','bindColumn')); }
    public function bindParam($parameter, &$variable , $data_type =null, $length=null,  $driver_options=null)
       {throw new jException('jelix~db.error.feature.unsupported', array('oci','bindParam')); }
    public function bindValue($parameter, $value, $data_type)
       {throw new jException('jelix~db.error.feature.unsupported', array('oci','bindValue')); }

    public function columnCount() {
        return pg_num_fields($this->_idResult);
    }

    public function execute($parameters=array()) {
        return oci_execute($this->_stmt);
    }
}
