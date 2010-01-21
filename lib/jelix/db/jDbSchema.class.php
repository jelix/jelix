<?php
/**
* @package    jelix
* @subpackage db
* @author     Laurent Jouanneau
* @contributor 
* @copyright  2010 Laurent Jouanneau
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
     * create the given table
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


    public function dropTable(jDbTable $table) {
        if ($this->tables === null) {
            $this->tables = $this->_getTables();
        }
        $name = $table->getName();
        if (isset($this->tables[$name])) {
            $this->_dropTable($name);
            unset($this->tables[$name]);
        }
    }

    /**
     * create the given table into the database
     * @return jDbTable the object corresponding to the created table
     */
    abstract protected function _createTable($name, $columns, $primaryKey, $attributes = array());

    abstract protected function _getTables();

    abstract protected function _dropTable($name);

}
