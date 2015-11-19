<?php
/**
* @package    jelix
* @subpackage db_driver
* @author     Gérald Croes, Laurent Jouanneau
* @contributor Laurent Jouanneau
* @contributor Florian Lonqueu-Brochard
* @copyright  2001-2005 CopixTeam, 2005-2015 Laurent Jouanneau
* @copyright  2012 Florian Lonqueu-Brochard
* @link      http://www.jelix.org
* @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*/

/**
 * Object to fetch result, wrapping the underlaying result object of mysqli
 * @package    jelix
 * @subpackage db_driver
 */
class mysqliDbResultSet extends jDbResultSet {

    protected $_stmt = null;

    private $_usesMysqlnd = true;

    function __construct ($resultSet, $stmt = null) {
        parent::__construct($resultSet);

        $this->_stmt = $stmt;
        if ($stmt) {
            $this->_usesMysqlnd = is_callable (array($stmt, 'get_result'));
        }
    }

    protected function _fetch () {
        if ($this->_stmt && !$this->_usesMysqlnd) {
            if (!$this->_idResult->fetch()) {
                return false;
            }
            $result = clone $this->resultObject;
            return $result;
        }

        if ($this->_fetchMode == jDbConnection::FETCH_CLASS) {
            if ($this->_fetchModeCtoArgs) {
                $ret =  $this->_idResult->fetch_object($this->_fetchModeParam, $this->_fetchModeCtoArgs);
            }
            else {
                $ret =  $this->_idResult->fetch_object($this->_fetchModeParam);
            }
        }
        else {
            $ret =  $this->_idResult->fetch_object();
        }
        return $ret;
    }

    protected function _free (){
        if ($this->_stmt) {
            $this->_stmt->close();
            $this->_stmt = null;
        }

        //free_result may lead to a warning if close() has been called before by dbconnection's _disconnect()
        if ($this->_idResult) {
            @$this->_idResult->free_result();
        }
    }

    protected function _rewind (){
        return @$this->_idResult->data_seek(0);
    }

    public function rowCount(){
        return $this->_idResult->num_rows;
    }

    public function columnCount(){ 
        return $this->_idResult->field_count; 
    }

    public function bindColumn($column, &$param , $type=null ) {
        throw new jException('jelix~db.error.feature.unsupported', array('mysql','bindColumn'));
    }

    protected $boundValues = array();

    public function bindValue($parameter, $value, $dataType) {
        $this->addParamType($parameter, $dataType);
        $this->boundValues[$parameter] = $value;
        return true;
    }

    protected $boundParameterTypes = array();

    protected function addParamType ($parameter, $dataType) {
        if (!is_string($dataType)) {
            $types = array(
                  PDO::PARAM_INT => "i",
                  PDO::PARAM_STR => "s",
                  PDO::PARAM_LOB => "b",
            );
            $dataType = $types[$dataType];
        }
        $this->boundParameterTypes[$parameter] = $dataType;
    }
    
    protected $boundParameters = array();

    public function bindParam($parameter, &$variable, $dataType=null, $length=null, $driverOptions=null) {
        if (!$this->_stmt) {
            throw new Exception('Not a prepared statement');
        }
        $this->boundParameters[$parameter] = &$variable;
        $this->addParamType($parameter, $dataType);
        return true;
    }

    public function execute($parameters=null) {
        if (!$this->_stmt) {
            throw new Exception('Not a prepared statement');
        }

        if (count($this->boundParameters)) {
            $allParams = array(implode("", $this->boundParameterTypes));
            $allParams = array_merge($allParams, $this->boundParameters);
            $method = new ReflectionMethod('mysqli_stmt', 'bind_param');
            $method->invokeArgs($this->_stmt, $allParams);
        }
        else if (count($this->boundValues)) {
            $allParams = array(implode("", $this->boundParameterTypes));
            foreach($this->boundValues as $k => $val) {
                $allParams[$k+1] = & $this->boundValues[$k];
            }
            $method = new ReflectionMethod('mysqli_stmt', 'bind_param');
            $method->invokeArgs($this->_stmt, $allParams); 
        }

        $this->_stmt->execute();

        $this->boundParameters = array();
        $this->boundParameterTypes = array();
        $this->boundValues = array();

        if($this->_stmt->result_metadata()){
            //the query prodeces a result
            try{
                if( $this->_usesMysqlnd ) {
                    //with the MySQL native driver - mysqlnd (by default in php 5.3.0)
                    $this->_idResult = $this->_stmt->get_result();
                } else {
                    $this->_idResult = $this->_stmt;
                    $this->deprecatedBindResults($this->_stmt);
                }
            }
            catch(Exception $e){
                throw new jException('jelix~db.error.query.bad', $this->_stmt->errno);
            }
        }
        else{
            if($this->_stmt->affected_rows > 0) {
                return $this->_stmt->affected_rows;
            }
            elseif ($this->_stmt->affected_rows === null) {
                throw new jException('jelix~db.error.invalid.param');
            }
            else{
                throw new jException('jelix~db.error.query.bad', $this->_stmt->errno);
            }
        }
        return true;
    }

    /**
     * @deprecated it should be removed for PHP54+ support only (no PHP53)
     */
    protected $resultObject = null;

    /**
     * @deprecated it should be removed for PHP54+ support only (no PHP53)
     */
    protected function deprecatedBindResults($stmt) {
        //this call to store_result() will buffer all results but is necessary for num_rows to have
        //its real value and thus for dbresultset's ->rowCount() to work fine :
        $stmt->store_result();

        // we have a statement, so no fetch_object method
        // so we will create results object. We need to bind result.
        $meta = $stmt->result_metadata();

        $this->resultObject = new stdClass();
        
        while($field = $meta->fetch_field()) {
            $this->resultObject->{$field->name} = null;
            $variables[] = & $this->resultObject->{$field->name}; // pass by reference
        }
        call_user_func_array(array($stmt, 'bind_result'), $variables);
        $meta->close();
    }

    public function getAttribute($attr){
        if (!$this->_stmt) {
            throw new Exception('Not a prepared statement');
        }
        return $this->_stmt->get_attr($attr);
    }

    public function setAttribute($attr, $value){
        if (!$this->_stmt) {
            throw new Exception('Not a prepared statement');
        }
        return $this->_stmt->get_attr($attr, $value);
    }

}
