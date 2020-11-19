<?php
/**
 * @package    jelix
 * @subpackage db
 *
 * @author      Laurent Jouanneau
 * @copyright  2005-2018 Laurent Jouanneau
 *
 * @see      http://www.jelix.org
 * @licence    http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */

/**
 * represent a statement result set or a prepared statement result set.
 *
 * @package  jelix
 * @subpackage db
 */
abstract class jDbResultSet implements Iterator
{
    protected $_idResult;
    protected $_fetchMode = 0;
    protected $_fetchModeParam;
    protected $_fetchModeCtoArgs;

    public function __construct($idResult)
    {
        $this->_idResult = $idResult;
    }

    public function __destruct()
    {
        if ($this->_idResult) {
            $this->_free();
            $this->_idResult = null;
        }
    }

    public function id()
    {
        return $this->_idResult;
    }

    /**
     * @param string $text a binary string to unescape
     *
     * @return string the unescaped string
     *
     * @since 1.1.6
     */
    public function unescapeBin($text)
    {
        return $text;
    }

    /**
     * a callback function which will modify on the fly record's value.
     *
     * @var callable[]
     *
     * @since 1.1.6
     */
    protected $modifier = array();

    /**
     * @param callable $function a callback function
     *                           the function should accept in parameter the record,
     *                           and the resulset object
     *
     * @since 1.1.6
     */
    public function addModifier($function)
    {
        $this->modifier[] = $function;
    }

    /**
     * set the fetch mode.
     *
     * @param int           $fetchmode FETCH_OBJ, FETCH_CLASS or FETCH_INTO
     * @param object|string $param     class name if FETCH_CLASS, an object if FETCH_INTO. else null.
     * @param array         $ctoargs   arguments for the constructor if FETCH_CLASS
     */
    public function setFetchMode($fetchmode, $param = null, $ctoargs = null)
    {
        $this->_fetchMode = $fetchmode;
        $this->_fetchModeParam = $param;
        $this->_fetchModeCtoArgs = $ctoargs;
    }

    /**
     * fetch a result. The result is returned as an object.
     *
     * @return bool|object result object or false if there is no more result
     */
    public function fetch()
    {
        $result = $this->_fetch();

        if (!$result) {
            return $result;
        }

        if ($this->_fetchMode == jDbConnection::FETCH_OBJ) {
            $this->applyModifiers($result);

            return $result;
        }

        if ($this->_fetchMode == jDbConnection::FETCH_CLASS) {
            if ($result instanceof $this->_fetchModeParam) {
                $this->applyModifiers($result);

                return $result;
            }
            $values = get_object_vars($result);
            $o = $this->_fetchModeParam;
            $result = new $o();
            foreach ($values as $k => $value) {
                $result->{$k} = $value;
            }
        } elseif ($this->_fetchMode == jDbConnection::FETCH_INTO) {
            $values = get_object_vars($result);
            $result = $this->_fetchModeParam;
            foreach ($values as $k => $value) {
                $result->{$k} = $value;
            }
        }
        $this->applyModifiers($result);

        return $result;
    }

    protected function applyModifiers($result)
    {
        if (count($this->modifier)) {
            foreach ($this->modifier as $m) {
                call_user_func_array($m, array($result, $this));
            }
        }
    }

    /**
     * Return all results in an array. Each result is an object.
     *
     * @return object[]
     */
    public function fetchAll()
    {
        $result = array();
        while ($res = $this->fetch()) {
            $result[] = $res;
        }

        return $result;
    }

    /**
     * Retrieve a statement attribute.
     *
     * @param int $attr
     */
    public function getAttribute($attr)
    {
        return null;
    }

    /**
     * Set a statement attribute.
     *
     * @param int   $attr
     * @param mixed $value
     */
    public function setAttribute($attr, $value)
    {
    }

    /**
     *  Bind a column to a PHP variable.
     *
     * @param mixed      $column
     * @param mixed      $param
     * @param null|mixed $type
     */
    abstract public function bindColumn($column, &$param, $type = null);

    /**
     * Binds a parameter to the specified variable name.
     *
     * @param mixed      $parameterName
     * @param mixed      $variable
     * @param mixed      $data_type
     * @param null|mixed $length
     * @param null|mixed $driver_options
     */
    abstract public function bindParam($parameterName, &$variable, $data_type = PDO::PARAM_STR, $length = null, $driver_options = null);

    /**
     *  Binds a value to a parameter.
     *
     * @param mixed $parameterName
     * @param mixed $value
     * @param mixed $data_type
     */
    abstract public function bindValue($parameterName, $value, $data_type = PDO::PARAM_STR);

    /**
     * Returns the number of columns in the result set.
     */
    abstract public function columnCount();

    /**
     * execute a prepared statement
     * It may accepted an array of named parameters and their value, if bindValue
     * or bindParam() did not called.
     *
     * @param array $parameters
     */
    abstract public function execute($parameters = null);

    /**
     *  Returns the number of rows affected by the last SQL statement.
     */
    abstract public function rowCount();

    /**
     * method responsible to free resources. called by the destructor.
     */
    abstract protected function _free();

    /**
     * deep implementation of fetch().
     *
     * @return bool|object
     */
    abstract protected function _fetch();

    /**
     * move the cursor to the first record.
     */
    abstract protected function _rewind();

    //--------------- interface Iterator
    protected $_currentRecord = false;
    protected $_recordIndex = 0;

    public function current()
    {
        return $this->_currentRecord;
    }

    public function key()
    {
        return $this->_recordIndex;
    }

    public function next()
    {
        $this->_currentRecord = $this->fetch();
        if ($this->_currentRecord) {
            ++$this->_recordIndex;
        }
    }

    public function rewind()
    {
        $this->_rewind();
        $this->_recordIndex = 0;
        $this->_currentRecord = $this->fetch();
    }

    public function valid()
    {
        return $this->_currentRecord != false;
    }
}
