<?php
/**
* @package    jelix
* @subpackage db
* @author     Laurent Jouanneau
* @contributor Aurélien Marcel
* @copyright  2010 Laurent Jouanneau, 2011 Aurélien Marcel
*
* @link        http://jelix.org
* @licence     http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*/

require_once(JELIX_LIB_PATH.'db/jDbTable.class.php');
require_once(JELIX_LIB_PATH.'db/jDbColumn.class.php');

/**
 *
 */
abstract class jDbSchema {

    /**
     * @var jDbConnection
     */
    protected $conn;

    function __construct(jDbConnection $conn) {
        $this->conn = $conn;
    }

    /**
     * @return jDbConnection
     */
    public function getConn() {
        return $this->conn;
    }

    /**
     * create the given table if it does not exist
     *
     * @param string $name
     * @param jDbColumn[] $columns list of columns
     * @param string|string[] $primaryKey the name of the column which contains the primary key
     * @param array $attributes  some table attributes specific to the database
     * @return jDbTable the object corresponding to the created table
     */
    function createTable($name, $columns, $primaryKey, $attributes = array()) {
        $name = $this->conn->prefixTable($name);
        if ($this->tables === null) {
            $this->tables = $this->_getTables();
        }

        if (isset($this->tables[$name])) {
            return null;
        }

        $this->tables[$name] = $this->_createTable($name, $columns, $primaryKey, $attributes);

        return $this->tables[$name];
    }

    /**
     * load informations of the given table
     * @return jDbTable ready to make change
     */
    function getTable($name) {
        $name = $this->conn->prefixTable($name);

        if ($this->tables === null) {
            $this->tables = $this->_getTables();
        }

        if (isset($this->tables[$name])) {
            return $this->tables[$name];
        }
        return null;
    }


    protected $tables = null;

    /**
     * @return array of jDbTable
     */
    public function getTables() {
        if ($this->tables === null) {
            $this->tables = $this->_getTables();
        }
        return $this->tables;
    }


    /**
     * @param string|jDbTable $table
     */
    public function dropTable($table) {
        if ($this->tables === null) {
            $this->tables = $this->_getTables();
        }
        if (is_string($table)) {
            $name = $table;
        }
        else {
            $name = $table->getName();
        }
        if (isset($this->tables[$name])) {
            $this->_dropTable($name);
            unset($this->tables[$name]);
        }
    }

    public function renameTable($oldName, $newName) {
        if ($this->tables === null) {
            $this->tables = $this->_getTables();
        }

        if (isset($this->tables[$newName])) {
            return $this->tables[$newName];
        }

        if (isset($this->tables[$oldName])) {
            $this->_renameTable($oldName, $newName);
            unset($this->tables[$oldName]);
            $this->tables[$newName] = $this->_getTableInstance($newName);
            return $this->tables[$newName];
        }
        return null;
    }

    /**
     * create the given table into the database
     * @param string $name
     * @param array $columns list of jDbColumn
     * @param string|array $primaryKey the name of the column which contains the primary key
     * @param array $attributes
     * @return jDbTable the object corresponding to the created table
     */
    abstract protected function _createTable($name, $columns, $primaryKey, $attributes = array());


    protected function _createTableQuery($name, $columns, $primaryKey, $attributes = array()) {
        $cols = array();

        if (is_string($primaryKey)) {
            $primaryKey = array($primaryKey);
        }

        foreach ($columns as $col) {
            $isPk = (in_array($col->name, $primaryKey) && count($primaryKey) == 1);
            $cols[] = $this->_prepareSqlColumn($col, $isPk);
        }

        if (isset($attributes['temporary']) && $attributes['temporary']) {
            $sql = 'CREATE TEMPORARY TABLE ';
        }
        else {
            $sql = 'CREATE TABLE ';
        }

        $sql .= $this->conn->encloseName($name).' ('.implode(", ",$cols);
        if (count($primaryKey) > 1) {
            $pkName = $this->conn->encloseName($name.'_pkey');
            $pkEsc = array();
            foreach($primaryKey as $k) {
                $pkEsc[] = $this->conn->encloseName($k);
            }
            $sql .= ', CONSTRAINT '.$pkName.' PRIMARY KEY ('.implode(',', $pkEsc).')';
        }

        $sql .= ')';
        return $sql;
    }


    abstract protected function _getTables();

    protected function _dropTable($name) {
        $this->conn->exec('DROP TABLE '.$this->conn->encloseName($name));
    }

    protected function _renameTable($oldName, $newName) {
        $this->conn->exec('ALTER TABLE '.$this->conn->encloseName($oldName).
        ' RENAME TO '.$this->conn->encloseName($newName));
    }

    abstract protected function _getTableInstance($name);

    protected $supportAutoIncrement = false;

    /**
     * return the SQL string corresponding to the given column.
     * private method, should be used only by a jDbTable object
     * @param jDbColumn $col  the column
     * @return string the sql string
     * @access private
     */
    function _prepareSqlColumn($col, $isSinglePrimaryKey=false) {
        $this->normalizeColumn($col);
        $colstr = $this->conn->encloseName($col->name).' '.$col->nativeType;

        if ($col->precision) {
            $colstr .= '('.$col->precision;
            if($col->scale) {
                $colstr .= ','.$col->scale;
            }
            $colstr .= ')';
        }
        else if ($col->length) {
            $colstr .= '('.$col->length.')';
        }

        if ($isSinglePrimaryKey && $this->supportAutoIncrement && $col->autoIncrement) {
            $colstr.= ' AUTO_INCREMENT ';
        }

        $colstr.= ($col->notNull?' NOT NULL':'');

        if (!$col->autoIncrement && !$isSinglePrimaryKey) {
            if ($col->hasDefault) {
                if ($col->default === null || strtoupper($col->default) == 'NULL') {
                    if (!$col->notNull) {
                        $colstr .= ' DEFAULT NULL';
                    }
                }
                else {
                    $colstr .= ' DEFAULT ';
                    $ti = $this->conn->tools()->getTypeInfo($col->type);
                    $phpType = $this->conn->tools()->unifiedToPHPType($ti[1]);
                    if ($phpType == 'string') {
                        $colstr .= $this->conn->quote($col->default);
                    } else {
                        $colstr .= $col->default;
                    }
                }
            }
        }
        if ($isSinglePrimaryKey) {
            $colstr .= ' PRIMARY KEY ';
        }
        return $colstr;
    }

    /**
     * fill correctly some properties of the column, depending of its type
     * and other properties
     * @param jDbColumn $col
     */
    function normalizeColumn($col) {
        $type = $this->conn->tools()->getTypeInfo($col->type);

        $col->nativeType = $type[0];
        if (!$col->length && $type[5]) {
            $col->length = $type[5];
        }

        if ($type[6]) {
            $col->autoIncrement = true;
            $col->notNull = true;
        }
    }


}
