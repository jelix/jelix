<?php
/**
* @package    jelix
* @subpackage dao
* @author     Gérald Croes, Laurent Jouanneau
* @contributor Laurent Jouanneau
* @contributor Bastien Jaillot (bug fix)
* @contributor Julien Issler
* @copyright  2001-2005 CopixTeam, 2005-2011 Laurent Jouanneau
* @copyright  2007-2008 Julien Issler
* This class was get originally from the Copix project (CopixDAOGeneratorV1, Copix 2.3dev20050901, http://www.copix.org)
* Few lines of code are still copyrighted 2001-2005 CopixTeam (LGPL licence).
* Initial authors of this Copix class are Gerald Croes and Laurent Jouanneau,
* and this class was rewrited for Jelix by Laurent Jouanneau
*
* @link        http://www.jelix.org
* @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*/

/**
* This is a generator which creates php class from dao xml file. It's made for PDO only
*
* It is called by jDaoCompiler
* @package  jelix
* @subpackage dao
* @see jDaoCompiler
*/
class jPdoDaoGenerator {

    /**
    * the dao definition.
    * @var jDaoParser
    */
    protected $_dataParser = null;

    /**
    * The DaoRecord ClassName
    * @var string
    */
    protected $_DaoRecordClassName = null;

    /**
    * the DAO classname
    * @var string
    */
    protected $_DaoClassName = null;

    protected $propertiesListForInsert = 'PrimaryTable';

    protected $aliasWord = ' AS ';

    /**
     * @var jDbTools
    */
    protected $tools;

    protected $_daoId;
    protected $_daoPath;
    protected $_dbType;

    /**
     * the real name of the main table
     */
    protected $tableRealName = '';

    /**
     * the real name of the main table, escaped in SQL
     * so it is ready to include into a SQL query.
     */
    protected $tableRealNameEsc = '';
    
    protected $sqlWhereClause = '';
    
    protected $sqlFromClause = '';
    
    protected $sqlSelectClause = '';

    /**
    * constructor
    * @param jDaoParser $daoDefinition
    */
    function __construct($selector, $tools, $daoParser){
        $this->_daoId = $selector->toString();
        $this->_daoPath = $selector->getPath();
        $this->_dbType = $selector->driver;
        $this->_dataParser = $daoParser;
        $this->_DaoClassName = $selector->getDaoClass();
        $this->_DaoRecordClassName = $selector->getDaoRecordClass();
        $this->tools = $tools;
    }

    /**
    * build all classes
    */
    public function buildClasses () {

        $src = array();
        $src[] = "\n" . ' require_once ( JELIX_LIB_PATH .\'dao/jDaoRecordBase.class.php\');';
        $src[] = ' require_once ( JELIX_LIB_PATH .\'dao/jDaoFactoryBase.class.php\');';

        // prepare some values to generate properties and methods

        $this->buildFromWhereClause();
        $this->sqlSelectClause   = $this->buildSelectClause();

        $tables            = $this->_dataParser->getTables();
        $pkFields          = $this->_getPrimaryFieldsList();
        $this->tableRealName    = $tables[$this->_dataParser->getPrimaryTable()]['realname'];
        $this->tableRealNameEsc = $this->_encloseName('\'.$this->_conn->prefixTable(\''.$this->tableRealName.'\').\'');

        // $sqlPkCondition    = $this->buildSimpleConditions($pkFields);
        $sqlPkCondition    = '';
        if ($sqlPkCondition != '') {
            $sqlPkCondition= ($this->sqlWhereClause !='' ? ' AND ':' WHERE ').$sqlPkCondition;
        }

        //-----------------------
        // Build the record class
        //-----------------------

        $src[] = "\nclass ".$this->_DaoRecordClassName.' extends jDaoRecordBase {';

        $properties=array();

        foreach ($this->_dataParser->getProperties() as $id=>$field) {
            $properties[$id] = get_object_vars($field);
            if ($field->defaultValue !== null) {
                $src[] = ' public $'.$id.'='.var_export($field->defaultValue, true).';';
            }
            else
                $src[] = ' public $'.$id.';';
        }

        // TODO PHP 5.3 : we could remove that
        $src[] = '   public function getProperties() { return '.$this->_DaoClassName.'::$_properties; }';
        $src[] = '   public function getPrimaryKeyNames() { return '.$this->_DaoClassName.'::$_pkFields; }';
        $src[] = '}';

        //--------------------
        // Build the dao class
        //--------------------

        $src[] = "\nclass ".$this->_DaoClassName.' extends jDaoFactoryBase {';
        $src[] = '   protected $_tables = '.var_export($tables, true).';';
        $src[] = '   protected $_primaryTable = \''.$this->_dataParser->getPrimaryTable().'\';';
        $src[] = '   protected $_selectClause=\''.$this->sqlSelectClause.'\';';
        $src[] = '   protected $_fromClause;';
        $src[] = '   protected $_whereClause=\''.$this->sqlWhereClause.'\';';
        $src[] = '   protected $_DaoRecordClassName=\''.$this->_DaoRecordClassName.'\';';
        $src[] = '   protected $_daoSelector = \''.$this->_daoId.'\';';

        if($this->tools->trueValue != '1'){
            $src[]='   protected $trueValue ='.var_export($this->tools->trueValue, true).';';
            $src[]='   protected $falseValue ='.var_export($this->tools->falseValue, true).';';
        }

        if($this->_dataParser->hasEvent('deletebefore') || $this->_dataParser->hasEvent('delete'))
            $src[] = '   protected $_deleteBeforeEvent = true;';
        if ($this->_dataParser->hasEvent('deleteafter') || $this->_dataParser->hasEvent('delete'))
            $src[] = '   protected $_deleteAfterEvent = true;';
        if ($this->_dataParser->hasEvent('deletebybefore') || $this->_dataParser->hasEvent('deleteby'))
            $src[] = '   protected $_deleteByBeforeEvent = true;';
        if ($this->_dataParser->hasEvent('deletebyafter') || $this->_dataParser->hasEvent('deleteby'))
            $src[] = '   protected $_deleteByAfterEvent = true;';

        $src[] = '   public static $_properties = '.var_export($properties, true).';';
        $src[] = '   public static $_pkFields = array('.$this->_writeFieldNamesWith ($start = '\'', $end='\'', $beetween = ',', $pkFields).');';

        $src[] = ' ';
        $src[] = 'public function __construct($conn){';
        $src[] = '   parent::__construct($conn);';
        $src[] = '   $this->_fromClause = \''.$this->sqlFromClause.'\';';
        $src[] = '}';

        // cannot put this methods directly into jDaoBase because self cannot refer to a child class
        // FIXME PHP53, we could use the static keyword instead of self
        $src[] = '   public function getProperties() { return self::$_properties; }';
        $src[] = '   public function getPrimaryKeyNames() { return self::$_pkFields;}';

        $src[] = ' ';
        $src[] = ' protected function _getPkWhereClauseForSelect($pk){';
        $src[] = '   extract($pk);';
        $src[] = ' return \''.$sqlPkCondition.'\';';
        $src[] = '}';

        $src[] = ' ';
        $src[] = 'protected function _getPkWhereClauseForNonSelect($pk){';
        $src[] = '   extract($pk);';
        // $src[] = '   return \' where '.$this->buildSimpleConditions($pkFields,'',false).'\';';
        $src[] = '}';

        //----- Insert method

        $src[] = $this->buildInsertMethod($pkFields);

        //-----  update method

        $src[] = $this->buildUpdateMethod($pkFields);


        //----- other user methods

        $src[] = $this->buildUserMethods();

        // $src[] = $this->buildEndOfClass();

        $src[] = '}';//end of class

        return implode("\n",$src);
    }

    /**
    * create FROM clause and WHERE clause for all SELECT query
    */
    protected function buildFromWhereClause(){

        $tables = $this->_dataParser->getTables();

        foreach($tables as $table_name => $table){
            $tables[$table_name]['realname'] = '\'.$this->_conn->prefixTable(\''.$table['realname'].'\').\'';
        }

        $primarytable = $tables[$this->_dataParser->getPrimaryTable()];
        $ptrealname = $this->_encloseName($primarytable['realname']);
        $ptname = $this->_encloseName($primarytable['name']);

        list($sqlFrom, $sqlWhere) = $this->buildOuterJoins($tables, $ptname);

        $sqlFrom =$ptrealname.$this->aliasWord.$ptname.$sqlFrom;

        foreach($this->_dataParser->getInnerJoins() as $tablejoin){
            $table= $tables[$tablejoin];
            $tablename = $this->_encloseName($table['name']);
            $sqlFrom .=', '.$this->_encloseName($table['realname']).$this->aliasWord.$tablename;

            foreach($table['fk'] as $k => $fk){
                $sqlWhere.=' AND '.$ptname.'.'.$this->_encloseName($fk).'='.$tablename.'.'.$this->_encloseName($table['pk'][$k]);
            }
        }

        $this->sqlWhereClause = ($sqlWhere !='' ? ' WHERE '.substr($sqlWhere,4) :'');
        $this->sqlFromClause = ' FROM '.$sqlFrom;
    }

    /**
    * build a SELECT clause for all SELECT queries
    * @return string the select clause.
    */
    protected function buildSelectClause ($distinct=false){
        $result = array();

        $tables = $this->_dataParser->getTables();
        foreach ($this->_dataParser->getProperties () as $id=>$prop){

            $table = $this->_encloseName($tables[$prop->table]['name']) .'.';

            if ($prop->selectPattern !=''){
                $result[]= $this->buildSelectPattern($prop->selectPattern, $table, $prop->fieldName, $prop->name);
            }
        }

        return 'SELECT '.($distinct?'DISTINCT ':'').(implode (', ',$result));
    }

    /**
     * build an item for the select clause
    */
    protected function buildSelectPattern ($pattern, $table, $fieldname, $propname ){
        if ($pattern =='%s'){
            $field = $table.$this->_encloseName($fieldname);
            if ($fieldname != $propname){
                $field .= ' as '.$this->_encloseName($propname);    
            }
        }else{
            $field = str_replace(array("'", "%s"), array("\\'",$table.$this->_encloseName($fieldname)),$pattern).' as '.$this->_encloseName($propname);
        }
        return $field;
    }

    protected function _getPrimaryFieldsList() {
        $tables            = $this->_dataParser->getTables();
        $pkFields          = array();

        $primTable = $tables[$this->_dataParser->getPrimaryTable()];
        $props  = $this->_dataParser->getProperties();
        // we want to have primary keys as the same order indicated into primarykey attr
        foreach($primTable['pk'] as $pkname) {
            foreach($primTable['fields'] as $f){
                if ($props[$f]->fieldName == $pkname) {
                    $pkFields[$props[$f]->name] = $props[$f];
                    break;
                }
            }
        }
        return $pkFields;
    }

    /**
    * gets fields that match a condition returned by the $captureMethod
    * @internal
    */
    protected function _getPropertiesBy ($captureMethod){
        $captureMethod = '_capture'.$captureMethod;
        $result = array ();

        foreach ($this->_dataParser->getProperties() as $field){
            if ( $this->$captureMethod($field)){
                $result[$field->name] = $field;
            }
        }
        return $result;
    }

    protected function _capturePrimaryFieldsExcludeAutoIncrement(&$field){
        return ($field->table == $this->_dataParser->getPrimaryTable() && !$field->autoIncrement);
    }

    protected function _capturePrimaryFieldsExcludePk(&$field){
        return ($field->table == $this->_dataParser->getPrimaryTable()) && !$field->isPK;
    }

    protected function _capturePrimaryTable(&$field){
        return ($field->table == $this->_dataParser->getPrimaryTable());
    }

    protected function _captureAll(&$field){
        return true;
    }

    protected function _captureFieldToUpdate(&$field){
        return ($field->table == $this->_dataParser->getPrimaryTable()
                && !$field->isPK
                && !$field->isFK
                && ( $field->autoIncrement || ($field->insertPattern != '%s' && $field->selectPattern != '')));
    }

    protected function _captureFieldToUpdateOnUpdate(&$field){
        return ($field->table == $this->_dataParser->getPrimaryTable()
                && !$field->isPK
                && !$field->isFK
                && ( $field->autoIncrement || ($field->updatePattern != '%s' && $field->selectPattern != '')));
    }

    protected function _captureBinaryField(&$field) {
        return ($field->unifiedType == 'binary' || $field->unifiedType == 'varbinary');
    }

    /**
    * format field names with start, end and between strings.
    *   will write the field named info.
    *   eg info == name
    *   echo $field->name
    * @param string   $info    property to get from objects in $using
    * @param string   $start   string to add before the info
    * @param string   $end     string to add after the info
    * @param string   $beetween string to add between each info
    * @param array    $using     list of CopixPropertiesForDAO object. if null, get default fields list
    * @see  jDaoProperty
    */
    protected function _writeFieldsInfoWith ($info, $start = '', $end='', $beetween = '', $using = null){
        $result = array();
        if ($using === null){
            //if no fields are provided, using _dataParser's as default.
            $using = $this->_dataParser->getProperties ();
        }

        foreach ($using as $id=>$field){
            $result[] = $start . $field->$info . $end;
        }

        return implode ($beetween,$result);;
    }

    /**
    * format field names with start, end and between strings.
    */
    protected function _writeFieldNamesWith ($start = '', $end='', $beetween = '', $using = null){
        return $this->_writeFieldsInfoWith ('name', $start, $end, $beetween, $using);
    }

    protected function _encloseName($name){
        return $this->tools->encloseName($name);
    }


    /**
     * build the insert() method in the final class
     * @return string the source of the method
     */
    protected function buildInsertMethod($pkFields) {
        $pkai = $this->getAutoIncrementPKField();
        $src = array();
        $src[] = 'public function insert ($record){';

        if($pkai !== null){
            // if there is an autoincrement field as primary key
			$fields = $this->_getPropertiesBy($this->propertiesListForInsert);
        } else {
            $fields = $this->_getPropertiesBy('PrimaryTable');
        }
        if($this->_dataParser->hasEvent('insertbefore') || $this->_dataParser->hasEvent('insert')){
            $src[] = '   jEvent::notify("daoInsertBefore", array(\'dao\'=>$this->_daoSelector, \'record\'=>$record));';
        }

        if($this->_dataParser->hasEvent('insertafter') || $this->_dataParser->hasEvent('insert')){
            $src[] = '   jEvent::notify("daoInsertAfter", array(\'dao\'=>$this->_daoSelector, \'record\'=>$record));';
        }

        $src[] = '    return $result;';
        $src[] = '}';

        return implode("\n",$src);
    }

    /**
     * build the update() method for the final class
     * @return string the source of the method
     */
    protected function buildUpdateMethod($pkFields) {
        $src = array();
        
        $src[] = 'public function update ($record){';

        $src[] = ' }';//ends the update function
        return implode("\n",$src);
    }


    /**
     * build all methods defined by the developer in the dao file
     * @return string the source of the methods
     */
    protected function buildUserMethods() {
        
        $allField = $this->_getPropertiesBy('All');
        $primaryFields = $this->_getPropertiesBy('PrimaryTable');
        $src = array();

        foreach($this->_dataParser->getMethods() as $name => $method){

            $defval = $method->getParametersDefaultValues();
            if(count($defval)) {
                $mparam = '';
                foreach($method->getParameters() as $param){
                    $mparam .= ', $' . $param;
                    if(isset($defval[$param]))
                        $mparam .= '=\'' . str_replace("'", "\'", $defval[$param]) . '\'';
                }
                // Remove the first ","
                $mparam = substr($mparam, 1);
            } else {
                $mparam = implode(', $', $method->getParameters());
                if($mparam != '') $mparam = '$' . $mparam;
            }

            $src[] = ' function ' . $method->name . ' (' . $mparam . '){';

            $src[] = '}';
        }
        return implode("\n", $src);
    }

    /**
    * get autoincrement PK field
    */
    protected function getAutoIncrementPKField ($using = null){
        if ($using === null){
            $using = $this->_dataParser->getProperties ();
        }

        $tb = $this->_dataParser->getTables();
        $tb = $tb[$this->_dataParser->getPrimaryTable()]['realname'];

        foreach ($using as $id=>$field) {
            if(!$field->isPK)
                continue;
            if ($field->autoIncrement) {
                return $field;
            }
        }
        return null;
    }

    protected function buildUpdateAutoIncrementPK($pkai) {
        return '       $record->'.$pkai->name.'= $this->_conn->lastInsertId();';
    }
}
