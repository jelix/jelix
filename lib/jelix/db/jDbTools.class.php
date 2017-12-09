<?php
/**
* @package    jelix
* @subpackage db
* @author     Gérald Croes, Laurent Jouanneau
* @contributor Laurent Jouanneau, Gwendal Jouannic, Julien Issler
* @copyright  2001-2005 CopixTeam, 2005-2017 Laurent Jouanneau
* @copyright  2008 Gwendal Jouannic
* @copyright  2008 Julien Issler
*
* This class was get originally from the Copix project (CopixDbTools, CopixDbConnection, Copix 2.3dev20050901, http://www.copix.org)
* Some lines of code are still copyrighted 2001-2005 CopixTeam (LGPL licence).
* Initial authors of this Copix classes are Gerald Croes and Laurent Jouanneau,
* and this class was adapted for Jelix by Laurent Jouanneau
*
* @link        http://www.jelix.org
* @licence     http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*/

/**
 * Description of a field of a table
 * @package  jelix
 * @subpackage db
 * @see jDbTools::getFieldList
 */
 class jDbFieldProperties {
 
    /**
     * native type of the field
     * @var string
     */
    public $type;

    /**
     * unified type of the field
     * @var string
     */
    public $unifiedtype;

    /**
     * field name
     * @var string
     */
    public $name;

    /**
     * says if the field can be null or not
     * @var boolean
     */
    public $notNull=true;

    /**
     * says if the field is the primary key
     * @var boolean
     */
    public $primary=false;

    /**
     * says if the field is auto incremented
     * @var boolean
     */
    public $autoIncrement=false;

    /**
     * default value
     * @var string
     */
    public $default='';

    /**
     * says if there is a default value
     * @var boolean
     */
    public $hasDefault = false;

    public $length = 0;
    
     /**
     * if there is a sequence
     * @var string
     */
    public $sequence = false;
    
    public $unsigned = false;
    
    public $minLength = null;
    
    public $maxLength = null;
    
    public $minValue = null;
    
    public $maxValue = null;

    /**
     * dao and form use this feature
     */
     public $comment;
}

/**
 * Provides utilities methods for a database
 * @package  jelix
 * @subpackage db
 */
abstract class jDbTools {

    public $trueValue = '1';

    public $falseValue = '0';
    
    /**
    * the database connector
    * @var jDbConnection
    */
    protected $_conn;

    /**
    * @param jDbConnection $connector the connection to a database
    */
    function __construct($connector = null){
        $this->_conn = $connector;
    }

    protected $unifiedToPhp = array(
        'boolean'=>'boolean',
        'integer'=>'integer',
        'float'=>'float',
        'double'=>'float',
        'numeric'=>'numeric',
        'decimal'=>'decimal',
        'date'=>'string',
        'time'=>'string',
        'datetime'=>'string',
        'year'=>'string',
        'char'=>'string',
        'varchar'=>'string',
        'text'=>'string',
        'blob'=>'string',
        'binary'=>'string',
        'varbinary'=>'string',
    );
    
    protected $typesInfo = array();


    /**
     * Get informations about the given SQL type
     * @param string $nativeType the SQL type
     * @return array an array which contains characteristics of the type
     *        array ( 'nativetype', 'corresponding unifiedtype', minvalue, maxvalue, minlength, maxlength)
     * minvalue, maxvalue, minlength, maxlength can be null.
     * @since 1.2
    */
    public function getTypeInfo($nativeType) {
        $nativeType = strtolower($nativeType);
        if(isset($this->typesInfo[$nativeType])) {
            $r = $this->typesInfo[$nativeType];
        }
        else {
            $r = $this->typesInfo['varchar'];
        }
        $r[] = ($nativeType == 'serial' || $nativeType == 'bigserial' || $nativeType == 'autoincrement' || $nativeType == 'bigautoincrement');
        return $r;
    }

    /**
     * Return the PHP type corresponding to the given unified type
     * @param string $unifiedType
     * @return string the php type
     * @throws Exception
     * @since 1.2
     */
    public function unifiedToPHPType($unifiedType) {
        if(isset($this->unifiedToPhp[$unifiedType])) {
            return $this->unifiedToPhp[$unifiedType];
        }
        throw new Exception('bad unified type name:' . $unifiedType);
    }

    /**
     * @param string $unifiedType  the unified type name
     * @param string $value        the value
     * @return string  the php value corresponding to the type
     * @since 1.2
    */
    public function stringToPhpValue($unifiedType, $value, $checkNull = false) {
        if($checkNull && ($value === null ||strtolower($value)=='null'))
            return null;
        switch($this->unifiedToPHPType($unifiedType)) {
            case 'boolean':
                return ($this->getBooleanValue($value) == $this->trueValue);
            case 'integer':
                return intval($value);
            case 'float':
                return floatval($value);
            case 'numeric':
            case 'decimal':
                if(is_numeric($value))
                    return $value;
                else
                    return floatval($value);
            default:
                return $value;
        }
    }

    /**
     * Parse a SQL type and gives type, length...
     *
     * @param string $type
     * @return array  [$realtype, $length, $precision, $scale, $otherTypeDef]
     */
    public function parseSQLType($type) {
        $length = 0;
        $scale = 0;
        $precision = 0;
        $tail = '';
        if (preg_match('/^(\w+)\s*(\(\s*(\d+)(,(\d+))?\s*\))?(.*)$/',$type, $m)) {
            $type = strtolower($m[1]);
            if (isset($m[3])) {
                $typeInfo = $this->getTypeInfo($type);
                $phpType =  $this->unifiedToPHPType($typeInfo[1]);
                if ($phpType == 'string') {
                    $length = intval($m[3]);
                }
                else {
                    $precision = intval($m[3]);
                }
            }
            if (isset($m[4]) && $m[5]) {
                $precision = $length;
                $length = 0;
                $scale = intval($m[5]);
            }
            if (isset($m[6])) {
                $tail = $m[6];
            }

        }
        return array($type, $length, $precision, $scale, $tail);
    }


    /**
     * @param string $unifiedType  the unified type name
     * @param mixed $value        the value
     * @return string  the value which is ready to include a SQL query string
     * @since 1.2
    */
    public function escapeValue($unifiedType, $value, $checkNull = false, $toPhpSource = false) {
        if($checkNull && ($value === null ||strtolower($value)=='null'))
            return 'NULL';
        switch($this->unifiedToPHPType($unifiedType)) {
            case 'boolean':
                return $this->getBooleanValue($value);
            case 'integer':
                return (string)intval($value);
            case 'float':
            case 'numeric':
            case 'decimal':
               return jDb::floatToStr($value);
            default:
                if ($toPhpSource) {
                    if ($unifiedType == 'varbinary' || $unifiedType == 'binary') {
                        return '\'.$this->_conn->quote2(\''.str_replace('\'','\\\'',$value).'\',true,true).\'';
                    }
                    else if(strpos($value,"'") !== false) {
                        return '\'.$this->_conn->quote(\''.str_replace('\'','\\\'',$value).'\').\'';
                    }
                    else {
                        return "\\'".$value."\\'";
                    }
                }
                elseif ($this->_conn)
                    return $this->_conn->quote($value);
                else
                    return "'".addslashes($value)."'";
        }
    }

    /**
     * @param string|boolean $value a value which is a boolean
     * @return string the string value representing a boolean in SQL
     * @since 1.2
    */
    public function getBooleanValue($value) {
      if(is_string($value))
          $value = strtolower($value);
      if ($value === 'true' || $value === true || intval($value) === 1 || $value === 't' || $value === 'on')
          return $this->trueValue;
      else
          return $this->falseValue;
    }

    /**
     * Enclose the field name
     * @param string $fieldName the field name
     * @return string the enclosed field name
     * @since 1.2
     */
    public function encloseName($fieldName){
        return $fieldName;
    }

    /**
     * returns the list of tables
     * @return array list of table names
     * @throws jException
     */
    public function getTableList () {
        $list = $this->_conn->schema()->getTables();
        return array_keys($list);
    }

    /**
    * Retrieve the list of fields of a table
    * @param string $tableName the name of the table
    * @param string $sequence  the sequence used to auto increment the primary key
    * @param string $schemaName the name of the schema (only for PostgreSQL)
    * @return jDbFieldProperties[]  keys are field names
    */
    abstract public function getFieldList ($tableName, $sequence='', $schemaName='');

    /**
     * regular expression to detect comments and end of query
     */
    protected $dbmsStyle = array('/^\s*#/', '/;\s*$/');

    public function execSQLScript ($file) {
        if(!isset($this->_conn->profile['table_prefix']))
            $prefix = '';
        else
            $prefix = $this->_conn->profile['table_prefix'];

        $lines = file($file);
        $cmdSQL = '';
        $nbCmd = 0;

        $style = $this->dbmsStyle;

        foreach ((array)$lines as $key=>$line) {
            if ((!preg_match($style[0],$line))&&(strlen(trim($line))>0)) { // The line isn't empty and isn't a comment
               //$line = str_replace("\\'","''",$line);
               //$line = str_replace($this->scriptReplaceFrom, $this->scriptReplaceBy,$line);
               
                $cmdSQL.=$line;

                if (preg_match($style[1],$line)) {
                    // If at the last line of the command, execute it
                    // Cleanup the command from the ending ";" and execute it
                    $cmdSQL = preg_replace($style[1],'',$cmdSQL);
                    $cmdSQL = str_replace('%%PREFIX%%', $prefix, $cmdSQL);
                    $this->_conn->exec ($cmdSQL);
                    $nbCmd++;
                    $cmdSQL = '';
                }
            }
        }
        return $nbCmd;
   }

    /**
     * @param string[] $columns list of column names
     * @return string the list in SQL
     * @since 1.6.16
     */
    public function getSQLColumnsList($columns) {
        $cols = array();
        foreach($columns as $col) {
            $cols [] = $this->_conn->encloseName($col);
        }
        return implode(',', $cols);
    }

    /**
     * Parse a SQL CREATE TABLE statement and returns all of its components
     * separately.
     *
     * @param $createTableStatement
     * @return array|bool false if parsing has failed. Else an array :
     *          'name' => the schema/table name,
     *          'temporary'=> true if there is the temporary keywork ,
     *          'ifnotexists' => true if there is the IF NOT EXISTS statement,
     *          'columns' => list of columns definitions,
     *          'constraints' => list of table constraints definitions,
     *          'options' => all options at the end of the CREATE TABLE statement.
     * @since 1.6.16
     */
    public function parseCREATETABLE($createTableStatement) {
        $result = array(
            'name' => '',
            'temporary'=>false,
            'ifnotexists' => false,
            'columns' => array(),
            'constraints' => array(),
            'options' => ''
        );

        if (!preg_match("/^\s*CREATE\s+(TEMP(?:ORARY)?\s+)?TABLE\s+(IF\s+NOT\s+EXISTS\s+)?([^(]+)/msi", $createTableStatement, $m)) {
            return false;
        }
        $result['temporary'] = !!($m[1]);
        $result['ifnotexists'] = !!($m[2]);
        $result['name'] = trim($m[3]);

        $posStart = strlen($m[0]);
        $posEnd = strrpos($createTableStatement, ')');
        $result['options'] = trim(substr($createTableStatement, $posEnd+1));

        $def = substr($createTableStatement, $posStart+1, $posEnd-$posStart-1);

        $tokens = preg_split("/([,()])/msi", $def, -1, PREG_SPLIT_DELIM_CAPTURE);

        $regexpConstraint = "/^\s*(CONSTRAINT|CHECK|UNIQUE|PRIMARY|EXCLUDE|FOREIGN|FULLTEXT|SPATIAL|INDEX|KEY)/msi";
        $columns = array();
        $constraints = array();
        $level = 0;
        $currentDef = '';
        foreach($tokens as $token) {
            if ($token == '(') {
                $level ++;
                $currentDef .= $token;
            }
            else if ($token == ')') {
                $level --;
                if ($level < 0) {
                    $level = 0;
                }
                $currentDef .= $token;
            }
            else if ($token == ',') {
                if ($level > 0) {
                    $currentDef .= $token;
                }
                else {
                    // new current definition
                    $currentDef = trim(preg_replace("/\s+/", ' ', $currentDef));
                    if (preg_match($regexpConstraint, $currentDef)) {
                        $constraints[] = $currentDef;
                    }
                    else {
                        $columns[] = $currentDef;
                    }
                    $currentDef = '';
                }
            }
            else {
                $currentDef .= $token;
            }
        }
        if ($currentDef) {
            $currentDef = trim(preg_replace("/\s+/", ' ', $currentDef));
            if (preg_match($regexpConstraint, $currentDef)) {
                $constraints[] = $currentDef;
            }
            else {
                $columns[] = $currentDef;
            }
        }

        $result['columns'] = $columns;
        $result['constraints'] = $constraints;

        return $result;
    }

}
