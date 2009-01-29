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
 * Provides utilities methods for a mysql database
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

        $rs = $this->_connector->query ('SHOW TABLES FROM '.$this->_connector->profile['database']);
        $col_name = 'Tables_in_'.$this->_connector->profile['database'];

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

        $rs = $this->_connector->query ('SHOW FIELDS FROM `'.$tableName.'`');

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
    
    public function execSQLScript ($file) {
        $queries = $this->parseSQLScript(file_get_contents($file));
        foreach($queries as $query)
            $this->_connector->exec($query);
        return count($queries);
    }

    /**
     *
     */
    protected function parseSQLScript($script) {

        $delimiters = array();
        $distinctDelimiters = array(';');
        if(preg_match_all("/DELIMITER ([^\n]*)/i", $script, $d, PREG_SET_ORDER)) {
            $delimiters = $d[1];
            $distinctDelimiters = array_unique(array_merge($distinctDelimiters,$delimiters));
        }
        $preg= '';
        foreach($distinctDelimiters as $dd) {
            $preg.='|'.preg_quote($dd);
        }

        $tokens = preg_split('!(\'|"|\\\\|`|DELIMITER |#|/\\*|\\*/|\\-\\- |'."\n".$preg.')!i', $script, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

        $currentDelimiter = ';';
        $context = 0;
        $queries = array();
        $query = '';
        $previousToken = '';
        foreach ($tokens as $k=>$token) {
            switch ($context) {
            // 0 : statement
            case 0:
                $previousToken = $token;
                switch($token) {
                case $currentDelimiter:
                    $queries[] = trim($query);
                    $query = '';
                    break;
                case '\'':
                    $context = 1;
                    $previousToken = '';
                    $query.=$token;
                    break;
                case '"':
                    $context = 2;
                    $previousToken = '';
                    $query.=$token;
                    break;
                case '`':
                    $context = 3;
                    $query.=$token;
                    $previousToken = '';
                    break;
                case 'DELIMITER ':
                    $context = 6;
                    break;
                case '#':
                case '-- ':
                    $context = 4;
                    break;
                case '/*':
                    $context = 5;
                    break;
                case "\n":
                default :
                    $query.=$token;
                }
                break;
            // 1 : string '
            case 1:
                if ($token =="'") {
                    if ($previousToken != "\\" && $previousToken != "'") {
                        if(isset($tokens[$k+1])) {
                            if ($tokens[$k+1] != "'") {
                                $context = 0;
                            }
                        }
                        else
                            $context = 0;
                    }
                }
                $previousToken = $token;
                $query.=$token;
                break;
            // 2 : string "
            case 2:
                if ($token =='"') {
                    if ($previousToken != "\\" && $previousToken != '"') {
                        if(isset($tokens[$k+1])) {
                            if ($tokens[$k+1] != '"') {
                                $context = 0;
                            }
                        }
                        else
                            $context = 0;
                    }
                }
                $previousToken = $token;
                $query.=$token;
                break;
            // 3 : name with `
            case 3:
                if ($token =='`') {
                    if ($previousToken != "\\" && $previousToken != '`') {
                        if(isset($tokens[$k+1])) {
                            if ($tokens[$k+1] != '`') {
                                $context = 0;
                            }
                        }
                        else
                            $context = 0;
                    }
                }
                $previousToken = $token;
                $query.=$token;
                break;
            // 4 : comment single line
            case 4:
                if ($token == "\n") {
                    $query.=$token;
                    $context = 0;
                }
                break;
            // 5 : comment multi line
            case 5:
                if ($token == "*/") {
                    $context = 0;
                }
                break;
            // 6 : delimiter definition
            case 6:
                $currentDelimiter = $token;
                $context = 0;
                break;
            }
            
        }
        if (trim($query) != '')
            $queries[] = trim($query);
        return $queries;
    }
}


