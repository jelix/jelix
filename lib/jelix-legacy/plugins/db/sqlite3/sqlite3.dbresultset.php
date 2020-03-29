<?php
/**
 * @package    jelix
 * @subpackage db_driver
 *
 * @author     Loic Mathaud
 * @contributor Laurent Jouanneau
 *
 * @copyright  2006 Loic Mathaud, 2008-2015 Laurent Jouanneau
 *
 * @see      http://www.jelix.org
 * @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */

/**
 * Couche d'encapsulation des resultset sqlite.
 *
 * @package    jelix
 * @subpackage db_driver
 */
class sqlite3DbResultSet extends jDbResultSet
{
    /**
     * number of rows.
     */
    protected $numRows = 0;

    /**
     * when reaching the end of a result set, sqlite3 api do a rewind
     * we don't want this behavior, to mimic the behavior of other drivers
     * this property indicates that we reached the end.
     */
    protected $ended = false;

    /**
     * contains all unreaded records when
     * rowCount() have been called.
     */
    protected $buffer = array();

    protected $_stmt;

    /**
     * @param SQLite3Result $result
     * @param SQLite3Stmt   $stmt
     */
    public function __construct($result, $stmt = null)
    {
        parent::__construct($result);
        $this->_stmt = $stmt;
    }

    protected function _fetch()
    {
        if (count($this->buffer)) {
            return array_shift($this->buffer);
        }
        if ($this->ended) {
            return false;
        }
        $res = $this->_idResult->fetchArray(SQLITE3_ASSOC);
        if ($res === false) {
            $this->ended = true;

            return false;
        }
        ++$this->numRows;

        return (object) $res;
    }

    protected function _free()
    {
        $this->numRows = 0;
        $this->buffer = array();
        $this->ended = false;
        $this->_idResult->finalize();
    }

    protected function _rewind()
    {
        $this->numRows = 0;
        $this->buffer = array();
        $this->ended = false;

        return $this->_idResult->reset();
    }

    public function rowCount()
    {
        // the mysqlite3 api doesn't provide a numrows property like any other
        // database. The only way to now the number of rows, is to
        // fetch all rows :-/
        // let's store it into a buffer
        if ($this->ended) {
            return $this->numRows;
        }

        $res = $this->_idResult->fetchArray(SQLITE3_ASSOC);
        if ($res !== false) {
            while ($res !== false) {
                $this->buffer[] = (object) $res;
                $res = $this->_idResult->fetchArray(SQLITE3_ASSOC);
            }
            $this->numRows += count($this->buffer);
        }
        $this->ended = true;

        return $this->numRows;
    }

    public function bindColumn($column, &$param, $type = null)
    {
        throw new jException('jelix~db.error.feature.unsupported', array('sqlite3', 'bindColumn'));
    }

    protected function getSqliteType($pdoType)
    {
        $type = array(
            PDO::PARAM_INT => SQLITE3_INTEGER,
            PDO::PARAM_STR => SQLITE3_TEXT,
            PDO::PARAM_LOB => SQLITE3_BLOB,
        );
        if (isset($type[$pdoType])) {
            return $type[$pdoType];
        }

        return SQLITE3_TEXT;
    }

    public function bindParam($parameter, &$variable, $data_type = PDO::PARAM_STR, $length = null, $driver_options = null)
    {
        if (!$this->_stmt) {
            throw new Exception('Not a prepared statement');
        }
        return $this->_stmt->bindParam($parameter, $variable, $this->getSqliteType($data_type));
    }

    public function bindValue($parameter, $value, $data_type = PDO::PARAM_STR)
    {
        if (!$this->_stmt) {
            throw new Exception('Not a prepared statement');
        }
        return $this->_stmt->bindValue($parameter, $value, $this->getSqliteType($data_type));
    }

    public function columnCount()
    {
        return $this->_idResult->numColumns();
    }

    public function execute($parameters = null)
    {
        if (!$this->_stmt) {
            throw new Exception('Not a prepared statement');
        }
        if (is_array($parameters)) {
            foreach ($parameters as $name => $val) {
                $type = is_integer($val) ? SQLITE3_INTEGER : SQLITE3_TEXT;
                $this->_stmt->bindValue($name, $val, $type);
            }
        }
        if ($this->_idResult) {
            $this->_free();
            $this->_idResult = null;
        }
        $this->_idResult = $this->_stmt->execute();

        return true;
    }
}
