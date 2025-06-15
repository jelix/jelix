<?php
/**
 * @package    jelix
 * @subpackage db
 *
 * @author     Laurent Jouanneau
 * @contributor Aurélien Marcel
 *
 * @copyright  2017-2025 Laurent Jouanneau, 2011 Aurélien Marcel
 *
 * @see        http://jelix.org
 * @licence     http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */
require_once JELIX_LIB_PATH.'db/jDbTable.class.php';

require_once JELIX_LIB_PATH.'db/jDbColumn.class.php';

abstract class jDbSchema
{
    /**
     * @var jDbConnection
     */
    protected $conn;

    public function __construct(jDbConnection $conn)
    {
        $this->conn = $conn;
    }

    /**
     * @return jDbConnection
     */
    public function getConn()
    {
        return $this->conn;
    }

    /**
     * create the given table if it does not exist.
     *
     * @param string          $name       the unprefixed table name
     * @param jDbColumn[]     $columns    list of columns
     * @param string|string[] $primaryKey the name of the column which contains the primary key
     * @param array           $attributes some table attributes specific to the database
     *
     * @return jDbTable the object corresponding to the created table
     */
    public function createTable($name, $columns, $primaryKey, $attributes = array())
    {
        $prefixedName = $this->conn->prefixTable($name);
        // be sure list of table is updated
        $this->tables = $this->_getTables();

        if (isset($this->tables[$name])) {
            return null;
        }

        $this->tables[$name] = $this->_createTable($prefixedName, $columns, $primaryKey, $attributes);

        return $this->tables[$name];
    }

    /**
     * load informations of the given.
     *
     * @param string $name the unprefixed table name
     *
     * @return jDbTable ready to make change
     */
    public function getTable($name)
    {
        if ($this->tables === null) {
            $this->tables = $this->_getTables();
        }

        if (isset($this->tables[$name])) {
            return $this->tables[$name];
        }

        return null;
    }

    /**
     * @var null|jDbTable[] key of the array are unprefixed name of tables
     */
    protected $tables;

    /**
     * @return jDbTable[]
     */
    public function getTables()
    {
        // be sure list of table is updated
        $this->tables = $this->_getTables();

        return $this->tables;
    }

    /**
     * @param jDbTable|string $table the table object or the unprefixed table name
     */
    public function dropTable($table)
    {
        // be sure list of table is updated
        $this->tables = $this->_getTables();

        if (is_string($table)) {
            $name = $this->conn->prefixTable($table);
            $unprefixedName = $table;
        } else {
            $name = $table->getName();
            $unprefixedName = $this->conn->unprefixTable($name);
        }
        if (isset($this->tables[$unprefixedName])) {
            $this->_dropTable($name);
            unset($this->tables[$unprefixedName]);
        }
    }

    /**
     * @param string $oldName Unprefixed name of the table to rename
     * @param string $newName The new unprefixed name of the table
     *
     * @return null|jDbTable
     */
    public function renameTable($oldName, $newName)
    {
        if ($this->tables === null) {
            $this->tables = $this->_getTables();
        }

        if (isset($this->tables[$newName])) {
            return $this->tables[$newName];
        }

        if (isset($this->tables[$oldName])) {
            $newPrefixedName = $this->conn->prefixTable($newName);
            $this->_renameTable(
                $this->conn->prefixTable($oldName),
                $newPrefixedName
            );
            unset($this->tables[$oldName]);
            $this->tables[$newName] = $this->_getTableInstance($newPrefixedName);

            return $this->tables[$newName];
        }

        return null;
    }

    /**
     * create the given table into the database.
     *
     * @param string       $name       the table name
     * @param jDbColumn[]  $columns
     * @param array|string $primaryKey the name of the column which contains the primary key
     * @param array        $attributes
     *
     * @return jDbTable the object corresponding to the created table
     */
    abstract protected function _createTable($name, $columns, $primaryKey, $attributes = array());

    protected function _createTableQuery($name, $columns, $primaryKey, $attributes = array())
    {
        $cols = array();

        if (is_string($primaryKey)) {
            $primaryKey = array($primaryKey);
        }

        $autoIncrementUniqueKey = null;

        foreach ($columns as $col) {
            $isPk = (in_array($col->name, $primaryKey));
            $isSinglePk = $isPk && (count($primaryKey) == 1);
            $cols[] = $this->_prepareSqlColumn($col, $isPk, $isSinglePk);
            if ($col->autoIncrement && !$isPk) {
                // we should declare it as unique key
                $autoIncrementUniqueKey = $col;
            }
        }

        if (isset($attributes['temporary']) && $attributes['temporary']) {
            $sql = 'CREATE TEMPORARY TABLE ';
        } else {
            $sql = 'CREATE TABLE ';
        }

        $sql .= $this->conn->encloseName($name).' ('.implode(', ', $cols);
        if (count($primaryKey) > 1) {
            $pkName = $this->conn->encloseName($name.'_pkey');
            $pkEsc = array();
            foreach ($primaryKey as $k) {
                $pkEsc[] = $this->conn->encloseName($k);
            }
            $sql .= ', CONSTRAINT '.$pkName.' PRIMARY KEY ('.implode(',', $pkEsc).')';
        }

        if ($autoIncrementUniqueKey) {
            $ukName = $this->conn->encloseName($name.'_'.$autoIncrementUniqueKey->name.'_ukey');
            $sql .= ', CONSTRAINT '.$ukName.' UNIQUE ('.$this->conn->encloseName($autoIncrementUniqueKey->name).')';
        }

        $sql .= ')';

        return $sql;
    }

    abstract protected function _getTables();

    protected function _dropTable($name)
    {
        $this->conn->exec('DROP TABLE '.$this->conn->encloseName($name));
    }

    protected function _renameTable($oldName, $newName)
    {
        $this->conn->exec('ALTER TABLE '.$this->conn->encloseName($oldName).
        ' RENAME TO '.$this->conn->encloseName($newName));
    }

    abstract protected function _getTableInstance($name);

    /**
     * return the SQL string corresponding to the given column.
     * private method, should be used only by a jDbTable object.
     *
     * @param jDbColumn $col                the column
     * @param mixed     $isPrimaryKey
     * @param mixed     $isSinglePrimaryKey
     *
     * @return string the sql string
     */
    public function _prepareSqlColumn($col, $isPrimaryKey = false, $isSinglePrimaryKey = false)
    {
        $this->normalizeColumn($col);
        $colstr = $this->conn->encloseName($col->name).' '.$col->nativeType;
        $ti = $this->conn->tools()->getTypeInfo($col->type);
        if ($col->precision) {
            $colstr .= '('.$col->precision;
            if ($col->scale) {
                $colstr .= ','.$col->scale;
            }
            $colstr .= ')';
        } elseif ($col->length && $ti[1] != 'text' && $ti[1] != 'blob' && $ti[1] != 'json') {
            $colstr .= '('.$col->length.')';
        }

        $colstr .= $this->_getAutoIncrementKeyWord($col, $isPrimaryKey, $isSinglePrimaryKey);

        $colstr .= ($col->notNull ? ' NOT NULL' : '');

        if (!$col->autoIncrement && !$isPrimaryKey) {
            if ($col->hasDefault) {
                if ($col->default === null || strtoupper($col->default) == 'NULL') {
                    if (!$col->notNull) {
                        $colstr .= ' DEFAULT NULL';
                    }
                } else {
                    $colstr .= ' DEFAULT '.$this->conn->tools()->escapeValue($ti[1], $col->default, true);
                }
            }
        }
        if ($isSinglePrimaryKey) {
            $colstr .= ' PRIMARY KEY ';
        }

        return $colstr;
    }

    /**
     * @param jDbColumn $col                the column
     * @param mixed     $isPrimaryKey
     * @param mixed     $isSinglePrimaryKey
     */
    protected function _getAutoIncrementKeyWord($col, $isPrimaryKey, $isSinglePrimaryKey)
    {
        return '';
    }

    /**
     * fill correctly some properties of the column, depending of its type
     * and other properties.
     *
     * @param jDbColumn $col
     */
    public function normalizeColumn($col)
    {
        $type = $this->conn->tools()->getTypeInfo($col->type);

        $col->nativeType = $type[0];

        if ($type[6]) {
            $col->autoIncrement = true;
            $col->notNull = true;
        }
    }
}
