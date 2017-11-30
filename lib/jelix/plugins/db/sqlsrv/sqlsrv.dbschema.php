<?php
/**
* @package    jelix
* @subpackage db
* @author     Laurent Jouanneau
* @copyright  2010-2017 Laurent Jouanneau
*
* @link        http://www.jelix.org
* @licence     http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*/

/**
 * 
 * @package    jelix
 * @subpackage db_driver
 */
class sqlsrvDbTable extends jDbTable {
    protected function _loadColumns() {
        throw new Exception ('Not Implemented');
    }

    protected function _alterColumn(jDbColumn $old, jDbColumn $new) {
        throw new Exception ('Not Implemented');
    }

    protected function _addColumn(jDbColumn $new) {
        throw new Exception ('Not Implemented');
    }

    protected function _loadIndexesAndKeys() {
        throw new Exception ('Not Implemented');
    }

    protected function _createIndex(jDbIndex $index) {
        throw new Exception ('Not Implemented');
    }

    protected function _dropIndex(jDbIndex $index) {
        throw new Exception ('Not Implemented');
    }

    protected function _loadReferences() {
        throw new Exception ('Not Implemented');
    }

    protected function _createReference(jDbReference $ref) {
        throw new Exception ('Not Implemented');
    }

    protected function _dropReference(jDbReference $ref) {
        throw new Exception ('Not Implemented');
    }

}

/**
 * 
 * @package    jelix
 * @subpackage db_driver
 */
class sqlsrvDbSchema extends jDbSchema {
    protected function _createTable($name, $columns, $primaryKey, $attributes = array()) {
        throw new Exception ('Not Implemented');
    }

    protected function _getTables() {
        $results = array ();
        $sql = "SELECT TABLE_NAME FROM " .
            $this->conn->profile['database']. ".INFORMATION_SCHEMA.TABLES
                WHERE TABLE_TYPE = 'BASE TABLE' AND
                TABLE_NAME NOT LIKE ('sys%') AND
                TABLE_NAME NOT LIKE ('dt%')";
        $rs = $this->conn->query ($sql);
        while ($line = $rs->fetch()){
            $results[$line->TABLE_NAME] = new sqlsrvDbTable($line->TABLE_NAME, $this);
        }
        return $results;
    }
}
