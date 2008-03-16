<?php
/**
* @package    jelix
* @subpackage db_driver
* @author     Croes Gérald, Laurent Jouanneau
* @contributor Laurent Jouanneau
* @copyright  2001-2005 CopixTeam, 2005-2007 Laurent Jouanneau
* This class was get originally from the Copix project (CopixDbToolsMysql, Copix 2.3dev20050901, http://www.copix.org)
* Some lines of code are copyrighted 2001-2005 CopixTeam (LGPL licence).
* Initial authors of this Copix class are Gerald Croes and Laurent Jouanneau,
* and this class was adapted/improved for Jelix by Laurent Jouanneau
*
* @link      http://www.jelix.org
* @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*/

/**
 * classe d'outils pour gérer une base de données
 * @package    jelix
 * @subpackage db_driver
 */
class mysqlDbTools extends jDbTools {

    protected $dbmsStyle = array('/^\s*(#|\-\- )/', '/;\s*$/');

    /**
    * retourne la liste des tables
    * @return   array    $tab[] = $nomDeTable
    */
    function _getTableList (){
        $results = array ();

        $rs = $this->_connector->query ('SHOW TABLES FROM '.$this->_connector->profil['database']);
        $col_name = 'Tables_in_'.$this->_connector->profil['database'];

        while ($line = $rs->fetch ()){
            $results[] = $line->$col_name;
        }

        return $results;
    }

    /**
    * récupère la liste des champs pour une base donnée.
    * @return   array    $tab[NomDuChamp] = obj avec prop (tye, length, lengthVar, notnull)
    */
    function _getFieldList ($tableName){
        $results = array ();

        $rs = $this->_connector->query ('SHOW FIELDS FROM ' . $tableName);

        while ($line = $rs->fetch ()){
            $field = new jDbFieldProperties();

            if (preg_match('/^(\w+)\s*(\((\d+)\))?.*$/',$line->Type,$m)) {
                $field->type = strtolower($m[1]);
                if ($field->type == 'varchar' && isset($m[3])) {
                    $field->length = intval($m[3]);
                }
            } else {
                $field->type = $line->Type;
            }

            $field->name = $line->Field;
            $field->notNull = ($line->Null == 'NO');
            $field->primary = ($line->Key == 'PRI');
            $field->autoIncrement  = ($line->Extra == 'auto_increment');
            $field->hasDefault = ($line->Default != '' || !($line->Default == null && $field->notNull));
            // to fix a bug in php 5.2.5 or mysql 5.0.51
            if($field->notNull && $line->Default === null && !$field->autoIncrement)
                $field->default ='';
            else
                $field->default = $line->Default;
            $results[$line->Field] = $field;
        }
        return $results;
    }
}
?>