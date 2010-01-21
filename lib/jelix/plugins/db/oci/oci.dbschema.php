<?php
/**
* @package     jelix
* @subpackage  db
* @author      Laurent Jouanneau
* @contributor Gwendal Jouannic
* @copyright   2008 Gwendal Jouannic, 2009-2010 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*/

/**
 * 
 * @package    jelix
 * @subpackage db_driver
 */
class ociDbTable extends jDbTable {

}

/**
 * 
 * @package    jelix
 * @subpackage db_driver
 */
class ociDbSchema extends jDbSchema {

    /**
     *
     */
    function createTable($name, $columns, $primaryKeys, $attributes=array()) {
        
    }

    /**
     * @return jDbTable
     */
    function getTable($name) {
        return  new ociDbTable($this->schema->getConn()->prefixTable($name), $this);
    }

    public function getTables () {
        $results = array ();

        $rs = $this->schema->getConn()->query ('SELECT TABLE_NAME FROM USER_TABLES');

        while ($line = $rs->fetch ()){
            $results[] = new ociDbTable($line->table_name, $this);
        }

        return $results;
    }
}
