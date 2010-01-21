<?php
/**
* @package    jelix
* @subpackage db
* @author     Laurent Jouanneau
* @contributor     Loic Mathaud
* @copyright  2006 Loic Mathaud, 2007-2010 Laurent Jouanneau
*
* @link        http://www.jelix.org
* @licence     http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*/

/**
 * 
 * @package    jelix
 * @subpackage db_driver
 */
class sqliteDbTable extends jDbTable {

}

/**
 * 
 * @package    jelix
 * @subpackage db_driver
 */
class sqliteDbSchema extends jDbSchema {

    /**
     *
     */
    function createTable($name, $columns, $primaryKeys, $attributes=array()) {
        
    }

    /**
     * @return jDbTable
     */
    function getTable($name) {
        return  new sqliteDbTable($this->schema->getConn()->prefixTable($name), $this);
    }

    function getTables() {
        $results = array ();

        $rs = $this->schema->getConn()->query('SELECT name FROM sqlite_master WHERE type="table"');

        while ($line = $rs->fetch ()){
            $results[] = new sqliteDbTable($line->name, $this);
        }

        return $results;
    }

}


