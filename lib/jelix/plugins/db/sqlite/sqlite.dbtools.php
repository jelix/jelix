<?php
/**
* @package    jelix
* @subpackage db_driver
* @author     Loic Mathaud
* @contributor
* @copyright  2006 Loic Mathaud
* @link      http://www.jelix.org
* @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*/

/**
 * classe d'outils pour gérer une base de données
 * @package    jelix
 * @subpackage db_driver
 */
class sqliteDbTools extends jDbTools {
    function __construct($connector){
        parent::__construct($connector);
    }

    /**
    * retourne la liste des tables
    * @return   array    $tab[] = $nomDeTable
    */
    function _getTableList (){
        $results = array ();

        $rs = $this->_connector->query('SELECT name FROM sqlite_master WHERE type="table"');

        while ($line = $rs->fetch ()){
            $results[] = $line->name;
        }

        return $results;
    }

    /**
    * récupère la liste des champs pour une base donnée.
    * @return   array    $tab[NomDuChamp] = obj avec prop (tye, length, lengthVar, notnull)
    */
    function _getFieldList ($tableName){
        $results = array ();

        $query = "SELECT sql FROM sqlite_master WHERE tbl_name= '". $tableName ."'";
        $rs = $this->_connector->query($query);
        $rs_line = $rs->fetch();
        $create_table = $rs_line->sql;

        $query = "PRAGMA table_info(". sqlite_escape_string($tableName) .")";
        $rs = $this->_connector->query($query);

        while ($result_line = $rs->fetch()){
            $field = new jDbFieldProperties();

            $type = $result_line->type;

            /**
            * récupéré depuis phpMyAdmin
            */
            // set or enum types: slashes single quotes inside options
            $type   = str_replace('BINARY', '', $type);
            $type   = str_replace('ZEROFILL', '', $type);
            $type   = str_replace('UNSIGNED', '', $type);
            /*
            if (eregi('^(set|enum)\((.+)\)$', $type, $tmp)){
                $type   = $tmp[1];
                $length = substr(ereg_replace('([^,])\'\'', '\\1\\\'', ',' . $tmp[2]), 1);
            }else{
                $length = $type;
                $type   = chop(eregi_replace('\\(.*\\)', '', $type));
                if (!empty($type)){
                    $length = eregi_replace("^$type\(", '', $length);
                    $length = eregi_replace('\)$', '', trim($length));
                }
                if ($length == $type){
                    $length = '';
                }
            }*/

            preg_match('/^(\w+).*$/',$type,$m);

            $field->type      = strtolower($m[1]);
            $field->name = $result_line->name;
            //$p_result_line->length    = $length;
            $field->not_null   = ($result_line->notnull != 1);
            $field->primary  = ($result_line->pk == 1);

            if (preg_match('/^int/i', $field->type)) {
                if ($field->primary) {
                    $field->auto_increment = true;
                } else {
                    $str = stristr($create_table, $field->name);
                    $array = explode(',', $str);
                    if (preg_match('/autoincrement/i', $array[0])) {
                        $field->auto_increment = true;
                    } else {
                        $field->auto_increment = false;
                    }
                }
            } else {
                $field->auto_increment = false;
            }
            $results[$result_line->name] = $field;
        }
        return $results;
    }
}
?>
