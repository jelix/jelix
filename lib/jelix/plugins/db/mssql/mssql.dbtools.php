<?php
/**
 * @package    jelix
 * @subpackage db_driver
 * @author     Yann Lecommandoux
 * @copyright  2008 Yann Lecommandoux
 * @link      http://jelix.org
 * @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */

/**
 * @experimental
 */
class mssqlDbTools extends jDbTools {

    protected $dbmsStyle = array('/^\s*(#|\-\- )/', '/;\s*$/');

    /**
     * 	List of tables
     * @return   array    $tab[] = $nomDeTable
     */
    function getTableList (){
        $results = array ();
        $sql = "SELECT TABLE_NAME FROM " .$this->_connector->profile['database']. ".INFORMATION_SCHEMA.TABLES
        		WHERE TABLE_NAME NOT LIKE ('sys%') AND TABLE_NAME NOT LIKE ('dt%')";
        $rs = $this->_connector->query ($sql);
        while ($line = $rs->fetch ()){
            $results[] = $line->TABLE_NAME;
        }
        return $results;
    }

    /**
     * return a field list of a table.
     * @return   array    $tab[NomDuChamp] = obj avec prop (type, length, lengthVar, notnull)
     */
    function getFieldList ($tableName){
        $results = array ();

        $pkeys = array();
        // get primary keys informations
        $rs = $this->_connector->query('EXEC sp_pkeys ' . $tableName);
        while ($line = $rs->fetch()){
            $pkeys[] = $line->COLUMN_NAME;
        }
        // get table informations
        unset($line);
        $rs = $this->_connector->query ('EXEC sp_columns ' . $tableName);
        while ($line = $rs->fetch ()){
            $field = new jDbFieldProperties();
            $field->name = $line->COLUMN_NAME;
            $field->type = $line->TYPE_NAME;
            $field->length = $line->LENGTH;
            if ($field->type == 'int identity'){
                $field->type = 'int';
                $field->autoIncrement = true;
            }
            if ($field->type == 'bit'){
                $field->type = 'int';
            }
            if ($line->IS_NULLABLE == 'No'){
                $field->notNull = false;
            }
            $field->hasDefault = false;
            $field->default = '';
            if(in_array($field->name, $pkeys)){
                $field->primary = true;
            }
            $results[$line->COLUMN_NAME] = $field;
        }
        return $results;
    }
}
?>