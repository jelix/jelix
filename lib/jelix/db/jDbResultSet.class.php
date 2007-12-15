<?php
/**
* @package    jelix
* @subpackage db
* @author      Laurent Jouanneau
* @contributor
* @copyright  2005-2006 Laurent Jouanneau
* @link      http://www.jelix.org
* @licence    http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*/

/**
 * represent a statement
 * @package  jelix
 * @subpackage db
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

    public function id() { return $this->_idResult; }

    public function setFetchMode($fetchmode, $param=null){
        $this->_fetchMode = $fetchmode;
        $this->_fetchModeParam =$param;
    }
    /**
     * fetch a result. The result is returned as an object.
     * @return object|boolean result object or false if ther is no more result
     */
    public function fetch(){
        $result = $this->_fetch ();
        if($result && $this->_fetchMode == self::FETCH_CLASS && !($result instanceof $this->_fetchModeParam) ){
            $values = get_object_vars ($result);
            $o = $this->_fetchModeParam;
            $result = new $o();
            foreach ( $values as $k=>$value){
                $result->$k = $value;
            }
        }
        return $result;
    }

    /**
     * Return all results in an array. Each result is an object.
     * @return array
     */
    public function fetchAll(){
        $result=array();
        while($res =  $this->fetch ()){
            $result[] = $res;
        }
        return $result;
    }

    /**
     * not implemented
     */
    public function getAttribute($attr){return null;}
    /**
     * not implemented
     */
    public function setAttribute($attr, $value){}

    /**
     * not implemented
     */
    abstract public function bindColumn($column, &$param , $type=null );
    /**
     * not implemented
     */
    abstract public function bindParam($parameter, &$variable , $data_type =null, $length=null,  $driver_options=null);
    /**
     * not implemented
     */
    abstract public function bindValue($parameter, $value, $data_type);
    /**
     * not implemented
     */
    abstract public function columnCount();

    /**
     * not implemented
     */
    abstract public function execute($parameters=null);

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
