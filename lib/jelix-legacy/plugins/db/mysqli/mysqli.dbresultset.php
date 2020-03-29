<?php
/**
 * @package    jelix
 * @subpackage db_driver
 *
 * @author     Gérald Croes, Laurent Jouanneau
 * @contributor Laurent Jouanneau
 * @contributor Florian Lonqueu-Brochard
 *
 * @copyright  2001-2005 CopixTeam, 2005-2015 Laurent Jouanneau
 * @copyright  2012 Florian Lonqueu-Brochard
 *
 * @see      http://www.jelix.org
 * @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */

/**
 * Object to fetch result, wrapping the underlaying result object of mysqli.
 *
 * @package    jelix
 * @subpackage db_driver
 */
class mysqliDbResultSet extends jDbResultSet
{
    protected $_stmt;

    protected $parameterNames = array();

    public function __construct($resultSet, $stmt = null, $parameterNames = array())
    {
        parent::__construct($resultSet);

        $this->_stmt = $stmt;
        $this->parameterNames = $parameterNames;
    }

    protected function _fetch()
    {
        if ($this->_fetchMode == jDbConnection::FETCH_CLASS) {
            if ($this->_fetchModeCtoArgs) {
                $ret = $this->_idResult->fetch_object($this->_fetchModeParam, $this->_fetchModeCtoArgs);
            } else {
                $ret = $this->_idResult->fetch_object($this->_fetchModeParam);
            }
        } else {
            $ret = $this->_idResult->fetch_object();
        }

        return $ret;
    }

    protected function _free()
    {
        if ($this->_stmt) {
            $this->_stmt->close();
            $this->_stmt = null;
        }

        //free_result may lead to a warning if close() has been called before by dbconnection's _disconnect()
        if ($this->_idResult) {
            @$this->_idResult->free_result();
        }
    }

    protected function _rewind()
    {
        return @$this->_idResult->data_seek(0);
    }

    public function rowCount()
    {
        return $this->_idResult->num_rows;
    }

    public function columnCount()
    {
        return $this->_idResult->field_count;
    }

    public function bindColumn($column, &$param, $type = null)
    {
        throw new jException('jelix~db.error.feature.unsupported', array('mysql', 'bindColumn'));
    }

    public function bindValue($parameter, $value, $dataType = PDO::PARAM_STR)
    {
        if (!$this->_stmt) {
            throw new Exception('Not a prepared statement');
        }
        $this->addParamType($parameter, $dataType);
        $this->boundParameters[$parameter] = $value;

        return true;
    }

    protected $boundParameterTypes = array();

    /**
     * @param mixed $parameter
     * @param mixed $dataType
     */
    protected function addParamType($parameter, $dataType)
    {
        if (is_integer($dataType)) {
            $types = array(
                PDO::PARAM_INT => 'i',
                PDO::PARAM_STR => 's',
                PDO::PARAM_LOB => 'b',
            );
            if (isset($types[$dataType])) {
                $dataType = $types[$dataType];
            } else {
                $dataType = 's';
            }
        } else if ($dataType != 'i'  && $dataType != 's' && $dataType != 'b' ) {
            $dataType = 's';
        }
        $this->boundParameterTypes[$parameter] = $dataType;
    }

    protected $boundParameters = array();

    /**
     * @param string     $parameter
     * @param mixed      $variable
     * @param mixed      $dataType
     * @param null|mixed $length
     * @param null|mixed $driverOptions
     */
    public function bindParam($parameter, &$variable, $dataType = PDO::PARAM_STR, $length = null, $driverOptions = null)
    {
        if (!$this->_stmt) {
            throw new Exception('Not a prepared statement');
        }
        $this->boundParameters[$parameter] = &$variable;
        $this->addParamType($parameter, $dataType);

        return true;
    }

    /**
     * @param null|mixed $parameters
     */
    public function execute($parameters = null)
    {
        if (!$this->_stmt) {
            throw new Exception('Not a prepared statement');
        }
        if ($this->_idResult) {
            @$this->_idResult->free_result();
            $this->_idResult = null;
        }
        $types = $this->boundParameterTypes;
        if ($parameters !== null) {
            $types = array_fill(0, count($parameters), 's');
        } elseif (count($this->boundParameters)) {
            $parameters = &$this->boundParameters;
        }

        if (count($parameters) != count($this->parameterNames)) {
            throw new Exception('Execute: number of parameters should equals number of parameters declared in the query');
        }

        $allParams = array('');
        foreach ($this->parameterNames as $k => $name) {
            if (!isset($parameters[$name])) {
                throw new Exception("Execute: parameter '${name}' is missing from parameters");
            }
            $allParams[0] .= $types[$name];
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
                $this->_idResult = $this->_stmt->get_result();
            } catch (Exception $e) {
                throw new jException('jelix~db.error.query.bad', $this->_stmt->errno);
            }
        } else {
            if ($this->_stmt->affected_rows > 0) {
                return $this->_stmt->affected_rows;
            }
            if ($this->_stmt->affected_rows === null) {
                throw new jException('jelix~db.error.invalid.param');
            }

            throw new jException('jelix~db.error.query.bad', $this->_stmt->errno);
        }

        return true;
    }

    public function getAttribute($attr)
    {
        if (!$this->_stmt) {
            throw new Exception('Not a prepared statement');
        }

        return $this->_stmt->get_attr($attr);
    }

    public function setAttribute($attr, $value)
    {
        if (!$this->_stmt) {
            throw new Exception('Not a prepared statement');
        }

        return $this->_stmt->set_attr($attr, $value);
    }
}
