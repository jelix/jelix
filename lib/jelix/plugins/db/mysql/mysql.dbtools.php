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
            preg_match('/^(\w+).*$/',$line->Type,$m);
            $field->type = $m[1];
            $field->name = $line->Field;
            $field->notNull = ($line->Null == 'NO');
            $field->primary = ($line->Key == 'PRI');
            $field->hasDefault = ($line->Default != '' || !($line->Default == null && $field->notNull));
            $field->default = $line->Default;
            $field->autoIncrement  = ($line->Extra == 'auto_increment');
            $results[$line->Field] = $field;
        }
        return $results;
    }
}
?>