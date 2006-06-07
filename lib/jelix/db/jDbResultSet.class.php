<?php
/**
* @package    jelix
* @subpackage db
* @version    $Id:$
* @author      Laurent Jouanneau
* @contributor
* @copyright  2005-2006 Laurent Jouanneau
* @link      http://www.jelix.org
* @licence    http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*/

abstract class jDbResultSet implements Iterator {

    const FETCH_CLASS = 8;

	protected $_idResult=null;
	protected $_fetchMode = 0;
	protected $_fetchModeParam = '';

	function __construct (  $idResult){
		$this->_idResult = $idResult;
	}

    function __destruct(){
        if ($this->_idResult){
			$this->_free ();
			$this->_idResult = null;
		}
    }

    public function setFetchMode($fetchmode, $param=null){
        $this->_fetchMode = $fetchmode;
        $this->_fetchModeParam =$param;
    }
    /**
     * fetch et renvoi les resultats sous forme d'un objet
     * @return object l'objet contenant les champs récupérés, ou false si le curseur est à la fin
     */
	public function fetch(){
		$result = $this->_fetch ();
		if($result && $this->_fetchMode == self::FETCH_CLASS){
		    $object = $this->_fetchModeParam;
		    $object = new $object();
            foreach (get_object_vars ($result) as $k=>$value){
                $object->$k = $value;
            }
            $result = $object;
		}
		return $result;
	}


    public function fetchAll(){
        $result=array();
        while($res =  $this->fetch ()){
            $result[] = $res;
        }
        return $result;
    }

    public function getAttribute($attr){return null;}
    public function setAttribute($attr, $value){}

    abstract public function bindColumn($column, &$param , $type=null );
    abstract public function bindParam($parameter, &$variable , $data_type =null, $length=null,  $driver_options=null);
    abstract public function bindValue($parameter, $value, $data_type);
    //abstract public function closeCursor();
    abstract public function columnCount();
    //abstract public function errorCode();
    //abstract public function errorInfo();

    abstract public function execute($parameters=null);
    //abstract public function fetchColumn();


    //abstract public function getColumnMeta();
    //abstract public function nextRowset();
    abstract public function rowCount();

    abstract protected function _free ();
    abstract protected function _fetch ();
    abstract protected function _rewind ();

    //--------------- interface Iterator
    protected $_currentRecord = false;
    protected $_recordIndex = 0;

    public function current () {
        return $this->_currentRecord;
    }

 	public function key () {
 	  return $this->_recordIndex;
 	}

 	public function next () {
 	  $this->_currentRecord =  $this->fetch ();
 	  if($this->_currentRecord)
 	      $this->_recordIndex++;
 	}

 	public function rewind () {
 	  $this->_rewind();
 	  $this->_recordIndex = 0;
 	  $this->_currentRecord =  $this->fetch ();
 	}

 	public function valid () {
 	  return ($this->_currentRecord != false);
 	}


}
?>