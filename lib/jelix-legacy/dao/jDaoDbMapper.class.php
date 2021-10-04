<?php
/**
 * @package     jelix
 * @subpackage  dao
 *
 * @author      Laurent Jouanneau
 * @copyright   2017-2021 Laurent Jouanneau
 *
 * @see        http://www.jelix.org
 * @licence     http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */


/**
 * It allows to create tables corresponding to a dao file.
 *
 * @since 1.6.16
 */
class jDaoDbMapper
{
    /**
     * @var \Jelix\Database\ConnectionInterface
     */
    protected $connection;

    protected $profile;

    /**
     * jDaoDbMapper constructor.
     *
     * @param string $profile the jdb profile
     */
    public function __construct($profile = '')
    {
        $this->connection = jDb::getConnection($profile);
        $this->profile = $profile;
    }

    /**
     * Create a table from a jDao file.
     *
     * @param string $selector    the selector of the DAO file
     * @param mixed  $selectorStr
     *
     * @return \Jelix\Database\Schema\TableInterface
     */
    public function createTableFromDao($selectorStr)
    {
        $selector = new jSelectorDao($selectorStr, $this->profile);
        $parser = $this->getParser($selector);

        $schema = $this->connection->schema();

        $tables = $parser->getTables();
        $properties = $parser->getProperties();
        $tableInfo = $tables[$parser->getPrimaryTable()];

        // create the columns and the table
        $columns = array();
        foreach ($tableInfo['fields'] as $propertyName) {
            $property = $properties[$propertyName];
            $columns[] = $this->createColumnFromProperty($property);
        }
        $table = $schema->createTable($tableInfo['realname'], $columns, $tableInfo['pk']);
        if (!$table) {
            $table = $schema->getTable($tableInfo['realname']);
            foreach ($columns as $column) {
                $table->alterColumn($column);
            }
        }

        // create foreign keys
        foreach ($tables as $tableName => $info) {
            if ($tableName == $tableInfo['realname']) {
                continue;
            }
            if (isset($info['fk'])) {
                $ref = new Jelix\Database\Schema\Reference('', $info['fk'], $info['realname'], $info['pk']);
                $table->addReference($ref);
            }
        }

        return $table;
    }

    /**
     * @param string    $selectorStr the dao for which we want to insert data
     * @param string[]  $properties  list of properties for which data are given
     * @param mixed[][] $data        the data. each row is an array of values.
     *                               Values are in the same order as $properties
     * @param int       $option      one of \Jelix\Database\Schema\SqlToolsInterface::IBD_* const
     *
     * @return int number of records inserted/updated
     */
    public function insertDaoData($selectorStr, $properties, $data, $option)
    {
        $selector = new jSelectorDao($selectorStr, $this->profile);
        $parser = $this->getParser($selector);
        $tools = $this->connection->tools();
        $allProperties = $parser->getProperties();
        $tables = $parser->getTables();
        $columns = array();
        $primaryKey = array();
        foreach ($properties as $name) {
            if (!isset($allProperties[$name])) {
                throw new Exception("insertDaoData: Unknown property {$name}");
            }
            $columns[] = $allProperties[$name]->fieldName;
            if ($allProperties[$name]->isPK) {
                $primaryKey[] = $allProperties[$name]->fieldName;
            }
        }
        if (count($primaryKey) == 0) {
            $primaryKey = null;
        }

        return $tools->insertBulkData(
            $tables[$parser->getPrimaryTable()]['realname'],
            $columns,
            $data,
            $primaryKey,
            $option
        );
    }

    protected function getParser(jSelectorDao $selector)
    {
        $cnt = jDb::getConnection($selector->profile);
        $context = new jDaoContext($selector->profile, $cnt);
        $compiler = new \Jelix\Dao\Generator\Compiler();
        return $compiler->parse($selector, $context);
    }

    protected function createColumnFromProperty(\Jelix\Dao\Parser\DaoProperty $property)
    {
        if ($property->autoIncrement) {
            // it should match properties as readed by jDbSchema
            $hasDefault = true;
            $default = '';
            $notNull = true;
        } else {
            $hasDefault = $property->defaultValue !== null || !$property->required;
            $default = $hasDefault ? $property->defaultValue : null;
            $notNull = $property->required;
        }

        $column = new \Jelix\Database\Schema\Column(
            $property->fieldName,
            $property->datatype,
            0,
            $hasDefault,
            $default,
            $notNull
        );
        $column->autoIncrement = $property->autoIncrement;
        $column->sequence = $property->sequenceName ? $property->sequenceName : false;
        if ($property->maxlength !== null) {
            $column->maxLength = $column->length = $property->maxlength;
        }
        if ($property->minlength !== null) {
            $column->minLength = $property->minlength;
        }

        return $column;
    }
}
