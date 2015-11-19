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

    protected $parameterNames = array();

    function __construct ($resultSet, $stmt = null, $parameterNames = array()) {
        parent::__construct($resultSet);

        $this->_stmt = $stmt;
        if ($stmt) {
            $this->_usesMysqlnd = is_callable (array($stmt, 'get_result'));
        }
        $this->parameterNames = $parameterNames;
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

    public function bindValue($parameter, $value, $dataType=PDO::PARAM_STR) {
        if (!$this->_stmt) {
            throw new Exception('Not a prepared statement');
        }
        $this->addParamType($parameter, $dataType);
        $this->boundParameters[$parameter] = $value;
        return true;
    }

    protected $boundParameterTypes = array();

    /** 
     * 
     */
    protected function addParamType ($parameter, $dataType) {
        if (is_integer($dataType)) {
            $types = array(
                  PDO::PARAM_INT => "i",
                  PDO::PARAM_STR => "s",
                  PDO::PARAM_LOB => "b",
            );
            if (isset($types[$dataType])) {
                $dataType = $types[$dataType];
            }
            else {
                $dataType = 's';
            }
        }
        else {
            $dataType = 's';
        }
        $this->boundParameterTypes[$parameter] = $dataType;
    }

    protected $boundParameters = array();

    /**
     * @param string $parameter
     */
    public function bindParam($parameter, &$variable, $dataType=PDO::PARAM_STR, $length=null, $driverOptions=null) {
        if (!$this->_stmt) {
            throw new Exception('Not a prepared statement');
        }
        $this->boundParameters[$parameter] = &$variable;
        $this->addParamType($parameter, $dataType);
        return true;
    }

    /** 
     * 
     */
    public function execute($parameters=null) {
        if (!$this->_stmt) {
            throw new Exception('Not a prepared statement');
        }

        $types = $this->boundParameterTypes;
        if ($parameters !== null) {
            $types = array_fill(0, count($parameters), 's');
        }
        else if (count($this->boundParameters)) {
            $parameters = & $this->boundParameters;
        }

        if (count($parameters) != count($this->parameterNames)) {
            throw new Exception('Execute: number of parameters should equals number of parameters declared in the query');
        }

        $allParams = array(implode("", $types));
        foreach($this->parameterNames as $k=>$name) {
            if (!isset($parameters[$name])) {
                throw new Exception("Execute: parameter '$name' is missing from parameters");
            }
            $allParams[] = &$parameters[$name];
        }

        $method = new ReflectionMethod('mysqli_stmt', 'bind_param');
        $method->invokeArgs($this->_stmt, $allParams);

        $this->_stmt->execute();

        $this->boundParameters = array();
        $this->boundParameterTypes = array();
        $this->boundValues = array();

        if ($this->_stmt->result_metadata()) {
            //the query prodeces a result
            try {
                if ($this->_usesMysqlnd) {
                    //with the MySQL native driver - mysqlnd (by default in php 5.3.0)
                    $this->_idResult = $this->_stmt->get_result();
                }
                else {
                    $this->_idResult = $this->_stmt;
                    $this->deprecatedBindResults($this->_stmt);
                }
            }
            catch(Exception $e) {
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
