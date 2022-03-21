<?php
/**
 * @package    jelix
 * @subpackage db_driver
 *
 * @author     Philippe Villiers
 * @contributor Laurent Jouanneau
 *
 * @copyright  2013 Philippe Villiers
 * @copyright 2018 Laurent Jouanneau
 *
 * @see        http://www.jelix.org
 * @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */

/**
 * @package    jelix
 * @subpackage db_driver
 */
class ociDbResultSet extends jDbResultSet
{
    protected $_cnt;

    public function __construct($stmtId, $cnt = null)
    {
        $this->_cnt = $cnt;
        parent::__construct($stmtId);
    }

    protected function _free()
    {
        return oci_free_statement($this->_idResult);
    }

    protected function _fetch()
    {
    }

    protected function _fetchAssoc()
    {
        return oci_fetch_assoc($this->_idResult);
    }

    protected function _rewind()
    {
    }

    public function fetch()
    {
        if ($this->_fetchMode == jDbConnection::FETCH_CLASS || $this->_fetchMode == jDbConnection::FETCH_INTO) {
            $res = oci_fetch_object($this->_idResult);
            if ($res) {
                $values = get_object_vars($res);
                $classObj = new $this->_fetchModeParam();
                foreach ($values as $k => $value) {
                    $attrName = strtolower($k);
                    $ociClassName = 'OCI-Lob';
                    // Check if we have a Lob, to read it correctly
                    if ($value instanceof $ociClassName) {
                        $classObj->{$attrName} = $value->read($value->size());
                    } else {
                        $classObj->{$attrName} = $value;
                    }
                }
                $res = $classObj;
            }
        } else {
            $res = oci_fetch_object($this->_idResult);
        }

        if ($res) {
            $this->applyModifiers($res);
        }

        return $res;
    }

    /**
     * Return all results in an array. Each result is an object.
     *
     * @return array
     */
    public function fetchAll()
    {
        $result = array();
        while ($res = $this->fetch()) {
            $result[] = $res;
        }

        return $result;
    }

    public function rowCount()
    {
        return oci_num_rows($this->_idResult);
    }

    protected function getOCIType($pdoType)
    {
        $type = array(
            PDO::PARAM_INT => SQLT_INT,
            PDO::PARAM_STR => SQLT_CHR,
            PDO::PARAM_LOB => SQLT_BLOB,
            PDO::PARAM_BOOL => SQLT_BOL,
        );
        if (isset($type[$pdoType])) {
            return $type[$pdoType];
        }

        return SQLT_CHR;
    }

    public function bindColumn($column, &$param, $type = null)
    {
        throw new jException('jelix~db.error.feature.unsupported', array('oci', 'bindColumn'));
    }

    public function bindParam($parameter, &$variable, $data_type = PDO::PARAM_STR, $length = -1, $driver_options = null)
    {
        return oci_bind_by_name(
            $this->_idResult,
            $parameter,
            $variable,
            $length,
            $this->getOCIType($data_type)
        );
    }

    protected $boundValues = array();

    public function bindValue($parameter, $value, $data_type = PDO::PARAM_STR)
    {
        $this->boundValues[$parameter] = $value;

        return oci_bind_by_name(
            $this->_idResult,
            $parameter,
            $this->boundValues[$parameter],
            -1,
            $this->getOCIType($data_type)
        );
    }

    public function columnCount()
    {
        return oci_num_fields($this->_idResult);
    }

    public function execute($parameters = array())
    {
        return oci_execute($this->_idResult);
    }
}
