<?php
/**
* @package    jelix
* @subpackage db_driver
* @author     Gwendal Jouannic
* @contributor 
* @copyright  2008 Gwendal Jouannic
* @link      http://www.jelix.org
* @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*/


class ociDbTools extends jDbTools {
	
    /**
    * retourne la liste des tables
    * @return   array    $tab[] = $nomDeTable
    */
    function _getTableList (){
        $results = array ();

        $rs = $this->_connector->query ('SELECT TABLE_NAME FROM USER_TABLES');

        while ($line = $rs->fetch ()){
            $results[] = $line->table_name;
        }

        return $results;
    }

    /**
    * récupère la liste des champs pour une base donnée.
    * @return   array    $tab[NomDuChamp] = obj avec prop (tye, length, lengthVar, notnull)
    */
    function _getFieldList ($tableName){
        $results = array ();
        
        $query = 'SELECT COLUMN_NAME, DATA_TYPE, DATA_LENGTH, NULLABLE, DATA_DEFAULT,  
                        (SELECT CONSTRAINT_TYPE 
                         FROM USER_CONSTRAINTS UC, USER_CONS_COLUMNS UCC 
                         WHERE UCC.TABLE_NAME = UTC.TABLE_NAME
                            AND UC.TABLE_NAME = UTC.TABLE_NAME
                            AND UCC.COLUMN_NAME = UTC.COLUMN_NAME
                            AND UC.CONSTRAINT_NAME = UCC.CONSTRAINT_NAME
                            AND UC.CONSTRAINT_TYPE = \'P\') AS CONSTRAINT_TYPE,
                         (SELECT \'Y\' FROM USER_SEQUENCES US
                         WHERE US.SEQUENCE_NAME = concat(\''.$this->_getAISequenceName($tableName,'\', UTC.COLUMN_NAME').')) AS IS_AUTOINCREMENT   
                    FROM USER_TAB_COLUMNS UTC 
                    WHERE UTC.TABLE_NAME = \''.strtoupper($tableName).'\'';

        $rs = $this->_connector->query ($query);

        while ($line = $rs->fetch ()){
        	
            $field = new jDbFieldProperties();

            $field->name = strtolower($line->column_name);
            $field->type = strtolower($line->data_type);
            
            if ($line->data_type == 'VARCHAR2'){
                $field->length = intval($line->data_length);
            }    
            	      
            $field->notNull = ($line->nullable == 'N');
            $field->primary = ($line->constraint_type == 'P');
            
            /**
             * A chaque champ auto increment correspond une sequence
             */
            if ($line->is_autoincrement == 'Y'){
                $field->autoIncrement  = true;
                $field->sequence = $this->_getAISequenceName($tableName, $field->name);
            }
            
            if ($line->data_default !== null || !($line->data_default === null && $field->notNull)){
                $field->hasDefault = true;
                $field->default =  $line->data_default;   
        	}
        	
            $results[$field->name] = $field;
        }
        return $results;
    }

    /**
    * récupère le nom de séquence correspondant à un champ auto_increment
    * @return   string 
    */
    function _getAISequenceName($tbName, $clName){
        return preg_replace(array('/\*tbName\*/', '/\*clName\*/'), array(strtoupper($tbName), strtoupper($clName)), $this->_connector->profil['sequence_AI_pattern']);
    }
}
?>