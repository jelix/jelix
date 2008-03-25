<?php
/**
* @package    jelix
* @subpackage dao
* @author     Croes Gérald, Laurent Jouanneau
* @contributor Laurent Jouanneau
* @contributor Bastien Jaillot (bug fix)
* @contributor Julien Issler
* @copyright  2001-2005 CopixTeam, 2005-2006 Laurent Jouanneau
* @copyright  2007 Julien Issler
* This class was get originally from the Copix project (CopixDAOGeneratorV1, Copix 2.3dev20050901, http://www.copix.org)
* Few lines of code are still copyrighted 2001-2005 CopixTeam (LGPL licence).
* Initial authors of this Copix class are Gerald Croes and Laurent Jouanneau,
* and this class was rewrited for Jelix by Laurent Jouanneau
*
* @link        http://www.jelix.org
* @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*/

/**
* This is a generator which creates php class from dao xml file.
*
* It is called by jDaoCompiler
* @package  jelix
* @subpackage dao
* @see jDaoCompiler
*/
class jDaoGenerator {

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

    protected $trueValue = 1;
    protected $falseValue = 0;

    /**
    * constructor
    * @param jDaoParser $daoDefinition
    */
    function __construct($factoryClassName, $recordClassName, $daoDefinition){
        $this->_dataParser = $daoDefinition;
        $this->_DaoClassName = $factoryClassName;
        $this->_DaoRecordClassName = $recordClassName;
    }

    /**
    * build all classes
    */
    public final function buildClasses () {

        $src = array();
        $src[] = ' require_once ( JELIX_LIB_PATH .\'dao/jDaoRecordBase.class.php\');';
        $src[] = ' require_once ( JELIX_LIB_PATH .\'dao/jDaoFactoryBase.class.php\');';

        // prepare some values to generate properties and methods

        list($sqlFromClause, $sqlWhereClause)= $this->_getFromClause();
        $tables            = $this->_dataParser->getTables();
        $sqlSelectClause   = $this->_getSelectClause();
        $pkFields          = $this->_getPropertiesBy('PkFields');
        $pTableRealName    = $tables[$this->_dataParser->getPrimaryTable()]['realname'];
        $pTableRealNameEsc = $this->_encloseName('\'.$this->_conn->prefixTable(\''.$pTableRealName.'\').\'');
        $pkai              = $this->_getAutoIncrementPKField();
        $sqlPkCondition    = $this->_buildSimpleConditions($pkFields);
        if($sqlPkCondition != ''){
            $sqlPkCondition= ($sqlWhereClause !='' ? ' AND ':' WHERE ').$sqlPkCondition;
        }

        //-----------------------
        // Build the record class
        //-----------------------

        $src[] = "\nclass ".$this->_DaoRecordClassName.' extends jDaoRecordBase {';

        $properties=array();

        foreach ($this->_dataParser->getProperties() as $id=>$field){
            $properties[$id] = get_object_vars($field);
            if($field->defaultValue !== null)
                $src[] =' public $'.$id.'='.var_export($field->defaultValue, true).';';
            else
                $src[] =' public $'.$id.';';
        }

        $src[] = '   public function getProperties() { return '.$this->_DaoClassName.'::$_properties; }';
        $src[] = '   public function getPrimaryKeyNames() { return '.$this->_DaoClassName.'::$_pkFields; }';
        $src[] = '}';

        //--------------------
        // Build the dao class
        //--------------------

        $src[] = "\nclass ".$this->_DaoClassName.' extends jDaoFactoryBase {';
        $src[] = '   protected $_tables = '.var_export($tables, true).';';
        $src[] = '   protected $_primaryTable = \''.$this->_dataParser->getPrimaryTable().'\';';
        $src[] = '   protected $_selectClause=\''.$sqlSelectClause.'\';';
        $src[] = '   protected $_fromClause;';
        $src[] = '   protected $_whereClause=\''.$sqlWhereClause.'\';';
        $src[] = '   protected $_DaoRecordClassName=\''.$this->_DaoRecordClassName.'\';';
        $src[] = '   protected $_daoSelector = \''.jDaoCompiler::$daoId.'\';';

        if($this->trueValue != 1){
            $src[]='   protected $trueValue ='.var_export($this->trueValue,true).';';
            $src[]='   protected $falseValue ='.var_export($this->falseValue,true).';';
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
        $src[] = '   $this->_fromClause = \''.$sqlFromClause.'\';';
        $src[] = '}';

        // cannot put this methods directly into jDaoBase because of a php bug on static methods/properties
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
        $src[] = '   return \' where '.$this->_buildSimpleConditions($pkFields,'',false).'\';';
        $src[] = '}';

        //----- Insert method
        $src[] = 'public function insert ($record){';

        if($pkai !== null){
            // if there is an autoincrement field as primary key

            // if a value is given for the autoincrement field, then with do a full insert
            $src[]=' if($record->'.$pkai->name.' > 0 ){';
            $src[] = '    $query = \'INSERT INTO '.$pTableRealNameEsc.' (';
            $fields = $this->_getPropertiesBy('PrimaryTable');
            list($fields, $values) = $this->_prepareValues($fields,'insertPattern', 'record->');

            $src[] = implode(',',$fields);
            $src[] = ') VALUES (';
            $src[] = implode(', ',$values);
            $src[] = ")';";

            $src[] = '}else{';

            $fields = $this->_getPropertiesBy($this->propertiesListForInsert);
        }else{
            $fields = $this->_getPropertiesBy('PrimaryTable');
        }

        // if there isn't a autoincrement as primary key, then we do a full insert.
        // if there isn't a value for the autoincrement field and if this is a mysql/sqlserver and pgsql, 
        // we do an insert without given primary key. In other case, we do a full insert.

        $src[] = '    $query = \'INSERT INTO '.$pTableRealNameEsc.' (';

        list($fields, $values) = $this->_prepareValues($fields,'insertPattern', 'record->');

        $src[] = implode(',',$fields);
        $src[] = ') VALUES (';
        $src[] = implode(', ',$values);
        $src[] = ")';";

        if($pkai !== null)
            $src[] = '}';

        if($this->_dataParser->hasEvent('insertbefore') || $this->_dataParser->hasEvent('insert')){
            $src[] = '   jEvent::notify("daoInsertBefore", array(\'dao\'=>$this->_daoselector, \'record\'=>$record));';
        }
        $src[] = '   $result = $this->_conn->exec ($query);';

        if($pkai !== null){
            $src[] = '   if(!$result)';
            $src[] = '       return false;';

            $src[] = '   if($record->'.$pkai->name.' < 1 ) ';
            $src[] = $this->genUpdateAutoIncrementPK($pkai, $pTableRealName);
        }

        // we generate a SELECT query to update field on the record object, which are autoincrement or calculated
        $fields = $this->_getPropertiesBy('FieldToUpdate');
        if (count($fields)) {
            $result = array();
            foreach ($fields as $id=>$prop){
                $result[]= $this->genSelectPattern($prop->selectPattern, '', $prop->fieldName, $prop->name);
            }

            $sql = 'SELECT '.(implode (', ',$result)). ' FROM '.$pTableRealNameEsc.' WHERE ';
            $sql.= $this->_buildSimpleConditions($pkFields, 'record->', false);

            $src[] = '  $query =\''.$sql.'\';';
            $src[] = '  $rs  =  $this->_conn->query ($query);';
            $src[] = '  $newrecord =  $rs->fetch ();';
            foreach ($fields as $id=>$prop){
                $src[] = '  $record->'.$prop->name.' = $newrecord->'.$prop->name.';';
            }
        }

        if($this->_dataParser->hasEvent('insertafter') || $this->_dataParser->hasEvent('insert')){
            $src[] = '   jEvent::notify("daoInsertAfter", array(\'dao\'=>$this->_daoselector, \'record\'=>$record));';
        }

        $src[] = '    return $result;';
        $src[] = '}';

        //-----  update method

        $src[] = 'public function update ($record){';
        list($fields, $values) = $this->_prepareValues($this->_getPropertiesBy('PrimaryFieldsExcludePk'),'updatePattern', 'record->');
        if(count($fields)){
            $src[] = '   $query = \'UPDATE '.$pTableRealNameEsc.' SET ';
            $sqlSet='';
            foreach($fields as $k=> $fname){
                $sqlSet.= ', '.$fname. '= '. $values[$k];
            }
            $src[] = substr($sqlSet,1);

            $sqlCondition = $this->_buildSimpleConditions($pkFields, 'record->', false);
            if($sqlCondition!='')
                $src[] = ' where '.$sqlCondition;

            $src[] = "';";
            if($this->_dataParser->hasEvent('updatebefore') || $this->_dataParser->hasEvent('update')){
                $src[] = '   jEvent::notify("daoUpdateBefore", array(\'dao\'=>$this->_daoselector, \'record\'=>$record));';
            }
            if($this->_dataParser->hasEvent('updateafter') || $this->_dataParser->hasEvent('update')){
                $src[] = '   $result = $this->_conn->exec ($query);';
                $src[] = '   jEvent::notify("daoUpdateAfter", array(\'dao\'=>$this->_daoselector, \'record\'=>$record));';
                $src[] = '   return $result;';
            }
            else
                $src[] = '   return $this->_conn->exec ($query);';
            $src[] = " }";//ends the update function
        }else{
            //the dao is mapped on a table which contains only primary key : update is impossible
            // so we will generate an error on update
            $src[] = "     throw new jException('jelix~dao.error.update.impossible',array('".jDaoCompiler::$daoId."','".jDaoCompiler::$daoPath."'));";
            $src[] = " }";
        }

        //----- other user methods

        $allField = $this->_getPropertiesBy('All');
        $primaryFields = $this->_getPropertiesBy('PrimaryTable');
        $ct=null;


        foreach($this->_dataParser->getMethods() as $name=>$method){

            $defval = $method->getParametersDefaultValues();
            if(count($defval)){
                $mparam='';
                foreach($method->getParameters() as $param){
                    $mparam.=', $'.$param;
                    if(isset($defval[$param]))
                        $mparam.='=\''.str_replace("'","\'",$defval[$param]).'\'';
                }
                $mparam = substr($mparam,1);
            }else{
                $mparam=implode(', $',$method->getParameters());
                if($mparam != '') $mparam ='$'.$mparam;
            }

            $src[] = ' function '.$method->name.' ('. $mparam.'){';

            $limit='';

            switch($method->type){
                case 'delete':
                    $src[] = '    $__query = \'DELETE FROM '.$pTableRealNameEsc.' \';';
                    $glueCondition =' WHERE ';
                    break;
                case 'update':
                    $src[] = '    $__query = \'UPDATE '.$pTableRealNameEsc.' SET ';
                    $updatefields = $this->_getPropertiesBy('PrimaryFieldsExcludePk');
                    $sqlSet='';
                    foreach($method->getValues() as $propname=>$value){
                        if($value[1]){
                            foreach($method->getParameters() as $param){
                                $value[0] = str_replace('$'.$param, '\'.'.$this->_preparePHPExpr('$'.$param, $updatefields[$propname],false).'.\'',$value[0]);
                            }
                            $sqlSet.= ', '.$this->_encloseName($updatefields[$propname]->fieldName). '= '. $value[0];
                        }else{
                            $sqlSet.= ', '.$this->_encloseName($updatefields[$propname]->fieldName). '= '.
                                $this->_preparePHPValue($value[0],$updatefields[$propname]->datatype,false);
                        }
                    }
                    $src[] =substr($sqlSet,1).'\';';

                    $glueCondition =' WHERE ';
                    break;

                case 'php':
                    $src[] = $method->getBody();
                    $src[] = '}';
                    break;

                case 'count':
                    if($method->distinct !=''){
                        $prop = $this->_dataParser->getProperties ();
                        $prop = $prop[$method->distinct];
                        $count=' DISTINCT '.$this->_encloseName($tables[$prop->table]['name']) .'.'.$this->_encloseName($prop->fieldName);
                    }else{
                        $count='*';
                    }
                    $src[] = '    $__query = \'SELECT COUNT('.$count.') as c \'.$this->_fromClause.$this->_whereClause;';
                    $glueCondition = ($sqlWhereClause !='' ? ' AND ':' WHERE ');
                    break;
                case 'selectfirst':
                case 'select':
                default:
                    if($method->distinct !=''){
                        $select = '\''.$this->_getSelectClause($method->distinct).'\'';
                    }else{
                        $select=' $this->_selectClause';
                    }
                    $src[] = '    $__query = '.$select.'.$this->_fromClause.$this->_whereClause;';
                    $glueCondition = ($sqlWhereClause !='' ? ' AND ':' WHERE ');
                    if( $method->type == 'select' && ($lim = $method->getLimit ()) !==null){
                        $limit=', '.$lim['offset'].', '.$lim['count'];
                    }
            }

            if($method->type == 'php')
                continue;

            $cond = $method->getConditions();
            $sqlCond = '';
            if($cond !== null){
                if($method->type == 'delete' || $method->type == 'update')
                    $sqlCond = $this->_buildConditions($cond, $primaryFields, $method->getParameters(), false);
                else if($method->type == 'count')
                    $sqlCond = $this->_buildConditions($cond, $allField, $method->getParameters(), true);
                else
                    $sqlCond = $this->_buildConditions($cond, $allField, $method->getParameters(), true, $method->getGroupBy());

            } else if(($method->type == 'select' || $method->type == 'selectfirst')) {
                $sqlCond = $this->_buildConditions(null, $allField, $method->getParameters(), true, $method->getGroupBy());
            }

            if(trim($sqlCond) != '')
                $src[] = '$__query .=\''.$glueCondition.$sqlCond."';";

            switch($method->type){
                case 'delete':
                case 'update' :
                    if ($method->eventBeforeEnabled || $method->eventAfterEnabled) {
                        $methname = ($method->type == 'update'?'Update':'Insert');
                        if ($method->eventBeforeEnabled) {
                        $src[] = '   jEvent::notify("daoSpecific'.$methname.'Before", array(\'dao\'=>$this->_daoselector,\'method\'=>\''.
                            $method->name.'\', \'params\'=>func_get_args()));';
                        }
                        if ($method->eventAfterEnabled) {
                            $src[] = '   $result = $this->_conn->exec ($__query);';
                            $src[] = '   jEvent::notify("daoSpecific'.$methname.'After", array(\'dao\'=>$this->_daoselector,\'method\'=>\''.
                                $method->name.'\', \'params\'=>func_get_args()));';
                            $src[] = '   return $result;';
                        } else {
                            $src[] = '    return $this->_conn->exec ($__query);';
                        }
                    } else {
                        $src[] = '    return $this->_conn->exec ($__query);';
                    }
                    break;
                case 'count':
                    $src[] = '    $__rs = $this->_conn->query($__query);';
                    $src[] = '    $__res = $__rs->fetch();';
                    $src[] = '    return intval($__res->c);';
                    break;
                case 'selectfirst':
                    $src[] = '    $__rs = $this->_conn->limitQuery($__query,0,1);';
                    $src[] = '    $__rs->setFetchMode(8,\''.$this->_DaoRecordClassName.'\');';
                    $src[] = '    return $__rs->fetch();';
                    break;
                case 'select':
                default:
                    if($limit)
                        $src[] = '    $__rs = $this->_conn->limitQuery($__query'.$limit.');';
                    else
                        $src[] = '    $__rs = $this->_conn->query($__query);';
                    $src[] = '    $__rs->setFetchMode(8,\''.$this->_DaoRecordClassName.'\');';
                    $src[] = '    return $__rs;';
            }
            $src[] = '}';
        }

        $src[] = '}';//end of class

        return implode("\n",$src);
    }

    /**
    *  create FROM clause for all SELECT query
    * @return array  FROM string and WHERE string
    */
    final protected function _getFromClause(){

        $tables = $this->_dataParser->getTables();

        foreach($tables as $table_name => $table){
            $tables[$table_name]['realname'] = '\'.$this->_conn->prefixTable(\''.$table['realname'].'\').\'';
        }

        $primarytable = $tables[$this->_dataParser->getPrimaryTable()];
        $ptrealname = $this->_encloseName($primarytable['realname']);
        $ptname = $this->_encloseName($primarytable['name']);

        list($sqlFrom, $sqlWhere) = $this->genOuterJoins($tables, $ptname);

        if($primarytable['name']!=$primarytable['realname'])
            $sqlFrom =$ptrealname.$this->aliasWord.$ptname.$sqlFrom;
        else
            $sqlFrom =$ptrealname.$sqlFrom;

        foreach($this->_dataParser->getInnerJoins() as $tablejoin){
            $table= $tables[$tablejoin];
            $tablename = $this->_encloseName($table['name']);
            if($table['name']!=$table['realname'])
                $sqlFrom .=', '.$this->_encloseName($table['realname']).$this->aliasWord.$tablename;
            else
                $sqlFrom .=', '.$this->_encloseName($table['realname']);

            foreach($table['fk'] as $k => $fk){
                $sqlWhere.=' AND '.$ptname.'.'.$this->_encloseName($fk).'='.$tablename.'.'.$this->_encloseName($table['pk'][$k]);
            }
        }

        $sqlWhere=($sqlWhere !='') ? ' WHERE '.substr($sqlWhere,4) :'';
        return array(' FROM '.$sqlFrom,$sqlWhere);
    }

    protected function genOuterJoins(&$tables, $primaryTableName){
        $sqlFrom = '';
        foreach($this->_dataParser->getOuterJoins() as $tablejoin){
            $table= $tables[$tablejoin[0]];
            $tablename = $this->_encloseName($table['name']);

            if($table['name']!=$table['realname'])
                $r =$this->_encloseName($table['realname']).$this->aliasWord.$tablename;
            else
                $r =$this->_encloseName($table['realname']);

            $fieldjoin='';
            foreach($table['fk'] as $k => $fk){
                $fieldjoin.=' AND '.$primaryTableName.'.'.$this->_encloseName($fk).'='.$tablename.'.'.$this->_encloseName($table['pk'][$k]);
            }
            $fieldjoin=substr($fieldjoin,4);

            if($tablejoin[1] == 0){
                $sqlFrom.=' LEFT JOIN '.$r.' ON ('.$fieldjoin.')';
            }elseif($tablejoin[1] == 1){
                $sqlFrom.=' RIGHT JOIN '.$r.' ON ('.$fieldjoin.')';
            }
        }
        return array($sqlFrom, '');
    }

    /**
    * build SELECT clause for all SELECT queries
    */
    protected function _getSelectClause ($distinct=false){
        $result = array();

        $tables = $this->_dataParser->getTables();
        foreach ($this->_dataParser->getProperties () as $id=>$prop){

            $table = $this->_encloseName($tables[$prop->table]['name']) .'.';

            if ($prop->selectPattern !=''){
                $result[]= $this->genSelectPattern($prop->selectPattern, $table, $prop->fieldName, $prop->name);
            }
        }

        return 'SELECT '.($distinct?'DISTINCT ':'').(implode (', ',$result));
    }

    protected function genSelectPattern ($pattern, $table, $fieldname, $propname ){
        if ($pattern =='%s'){
            if ($fieldname != $propname){
                $field = $table.$this->_encloseName($fieldname).' as '.$this->_encloseName($propname);
            }else{
                $field = $table.$this->_encloseName($fieldname);
            }
        }else{
            $field = sprintf (str_replace("'","\\'",$pattern), $table.$this->_encloseName($fieldname)).' as '.$this->_encloseName($propname);
        }
        return $field;
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

    protected function _capturePkFields(&$field){
        return ($field->table == $this->_dataParser->getPrimaryTable()) && $field->isPK;
    }

    protected function _capturePrimaryFieldsExcludeAutoIncrement(&$field){
        return ($field->table == $this->_dataParser->getPrimaryTable()) &&
        ($field->datatype != 'autoincrement') && ($field->datatype != 'bigautoincrement');
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
                && ( $field->datatype == 'autoincrement' || $field->datatype == 'bigautoincrement'
                    || ($field->insertPattern != '%s' && $field->selectPattern != '')));
    }



    /**
    * get autoincrement PK field
    */
    protected function _getAutoIncrementPKField ($using = null){
        if ($using === null){
            $using = $this->_dataParser->getProperties ();
        }

        $tb = $this->_dataParser->getTables();
        $tb = $tb[$this->_dataParser->getPrimaryTable()]['realname'];

        foreach ($using as $id=>$field) {
            if(!$field->isPK)
                continue;
            if ($field->datatype == 'autoincrement' || $field->datatype == 'bigautoincrement') {
                return $field;
            }
        }
        return null;
    }

    /**
     * build a WHERE clause with conditions on given properties : conditions are 
     * equality between a variable and the field.
     * the variable name is the name of the property, made with an optional prefix
     * given in $fieldPrefix parameter.
     * This method is called to generate WHERE clause for primary keys.
     * @param array $fields  list of jDaoPropery objects
     * @param string $fieldPrefix  an optional prefix to prefix variable names
     * @param boolean $forSelect  if true, the table name or table alias will prefix
     *                            the field name in the query
     * @return string the WHERE clause (without the WHERE keyword)
     * @internal
     */
    protected function _buildSimpleConditions (&$fields, $fieldPrefix='', $forSelect=true){
        $r = ' ';

        $first = true;
        foreach($fields as $field){
            if (!$first){
                $r .= ' AND ';
            }else{
                $first = false;
            }

            if($forSelect){
                $condition = $this->_encloseName($field->table).'.'.$this->_encloseName($field->fieldName);
            }else{
                $condition = $this->_encloseName($field->fieldName);
            }

            $var = '$'.$fieldPrefix.$field->name;
            $value = $this->_preparePHPExpr($var,$field, !$field->requiredInConditions,'=' );

            $r .= $condition.'\'.'.$value.'.\'';
        }

        return $r;
    }


    protected function _prepareValues ($fieldList, $pattern='', $prefixfield=''){
        $values = $fields = array();

        foreach ((array)$fieldList as $fieldName=>$field) {
            if ($pattern != '' && $field->$pattern == ''){
                continue;
            }

            $value = $this->_preparePHPExpr('$'.$prefixfield.$fieldName, $field, true);

            if($pattern != ''){
                $values[$field->name] = sprintf($field->$pattern,'\'.'.$value.'.\'');
            }else{
                $values[$field->name] = '\'.'.$value.'.\'';
            }

            $fields[$field->name] = $this->_encloseName($field->fieldName);
        }
        return array($fields, $values);
    }


    /**
     * build 'where' clause from conditions declared with condition tag in a user method
     * @param jDaoConditions $cond the condition object which contains conditions data
     * @param array $fields  array of jDaoProperty
     * @param array $params  list of parameters name of the method
     * @param boolean $withPrefix true if the field name should be preceded by the table name/table alias
     * @param array $groupby  list of properties to use in a groupby
     * @return string a WHERE clause (without the WHERE keyword) with eventually an ORDER clause
     * @internal
     */
    protected function _buildConditions ($cond, $fields, $params=array(), $withPrefix=true, $groupby=null){
        if($cond)
            $sql = $this->_buildSQLCondition ($cond->condition, $fields, $params, $withPrefix, true);
        else
            $sql = '';

        if($groupby && count($groupby)) {
            if(trim($sql) =='') {
                $sql = ' 1=1 ';
            }
            foreach($groupby as $k=>$f) {
                if ($withPrefix)
                    $groupby[$k]= $this->_encloseName($fields[$f]->table).'.'.$this->_encloseName($fields[$f]->fieldName);
                else
                    $groupby[$k]= $this->_encloseName($fields[$f]->fieldName);
            }
            $sql .= ' GROUP BY '.implode (', ', $groupby);
        }

        $order = array ();
        foreach ($cond->order as $name => $way){
            $ord='';
            if (isset($fields[$name])){
                if ($withPrefix)
                    $ord = $this->_encloseName($fields[$name]->table).'.'.$this->_encloseName($fields[$name]->fieldName);
                else
                    $ord = $this->_encloseName($fields[$name]->fieldName);
            }elseif($name{0} == '$'){
                $ord = '\'.'.$name.'.\'';
            }else{
                continue;
            }
            if($way{0} == '$'){
                $order[]=$ord.' \'.( strtolower('.$way.') ==\'asc\'?\'asc\':\'desc\').\'';
            }else{
                $order[]=$ord.' '.$way;
            }
        }
        if(count ($order) > 0){
            if(trim($sql) =='') {
                $sql = ' 1=1 ';
            }
            $sql.=' ORDER BY '.implode (', ', $order);
        }
        return $sql;
    }

    /**
     * build SQL WHERE clause 
     * Used by _buildConditions. And this method call itself recursively
     * @param jDaoCondition $cond a condition object which contains conditions data
     * @param array $fields  array of jDaoProperty
     * @param array $params  list of parameters name of the method
     * @param boolean $withPrefix true if the field name should be preceded by the table name/table alias
     * @param boolean $principal  should be true for the first call, and false for recursive call
     * @return string a WHERE clause (without the WHERE keyword)
     * @see jDaoGenerator::_buildConditions
     * @internal
     */
    protected function _buildSQLCondition ($condition, $fields, $params, $withPrefix, $principal=false){

        $r = ' ';

        //direct conditions for the group
        $first = true;
        foreach ($condition->conditions as $cond){
            if (!$first){
                $r .= ' '.$condition->glueOp.' ';
            }
            $first = false;

            $prop = $fields[$cond['field_id']];

            if($withPrefix){
                $f = $this->_encloseName($prop->table).'.'.$this->_encloseName($prop->fieldName);
            }else{
                $f = $this->_encloseName($prop->fieldName);
            }

            $r .= $f.' ';

            if($cond['operator'] == 'IN' || $cond['operator'] == 'NOT IN'){
                if($cond['isExpr']){
                    $phpvalue= $this->_preparePHPExpr('$__e', $prop, false);
                    if(strpos($phpvalue,'$this->_conn->quote')===0){
                        $phpvalue = str_replace('$this->_conn->quote(',"'\''.str_replace('\\'','\\\\\\'',",$phpvalue).".'\''";
                        $phpvalue = str_replace('\\','\\\\', $phpvalue);
                        $phpvalue = str_replace('\'','\\\'', $phpvalue);
                    }
                    $phpvalue = 'implode(\',\', array_map( create_function(\'$__e\',\'return '.$phpvalue.';\'), '.$cond['value'].'))';
                    $value= '(\'.'.$phpvalue.'.\')';
                }else{
                    $value= '('.$cond['value'].')';
                }
                $r.=$cond['operator'].' '.$value;
            }elseif($cond['operator'] == 'IS NULL' || $cond['operator'] == 'IS NOT NULL'){
                $r.=$cond['operator'].' ';
            }else{
                if($cond['isExpr']){
                    $value=str_replace("'","\\'",$cond['value']);
                    // we need to know if the expression is like "$foo" (1) or a thing like "concat($foo,'bla')" (2)
                    // because of the nullability of the parameter. If the value of the parameter is null and the operator
                    // is = or <>, then we need to generate a thing like :
                    // - in case 1: ($foo === null ? 'IS NULL' : '='.$this->_conn->quote($foo))
                    // - in case 2: '= concat('.($foo === null ? 'NULL' : $this->_conn->quote($foo)).' ,\'bla\')'
                    if(strpos($value, '$') === 0){
                        $value = '\'.'.$this->_preparePHPExpr($value, $prop, !$prop->requiredInConditions,$cond['operator']).'.\'';
                    }else{
                        foreach($params as $param){
                            $value = str_replace('$'.$param, '\'.'.$this->_preparePHPExpr('$'.$param, $prop, !$prop->requiredInConditions).'.\'',$value);
                        }
                        $value= $cond['operator'].' '.$value;
                    }
                }else{
                    $value= $cond['operator'].' '.$this->_preparePHPValue($cond['value'], $prop->datatype,false);
                }
                $r.=$value;
            }
        }
        //sub conditions
        foreach ($condition->group as $conditionDetail){
            if (!$first){
                $r .= ' '.$condition->glueOp.' ';
            }
            $r .= $this->_buildSQLCondition ($conditionDetail, $fields, $params, $withPrefix);
            $first=false;
        }

        //adds parenthesis around the sql if needed (non empty)
        if (strlen (trim ($r)) > 0 && (!$principal ||($principal && $condition->glueOp != 'AND'))){
            $r = '('.$r.')';
        }
        return $r;
    }



    /**
    * prepare a string ready to be included in a PHP script
    * we assume that if the value is "NULL", all things has been take care of
    *   before the call of this method
    * The method generates something like (including quotes) '.some PHP code.'
    *   (we do break "simple quoted strings")
    */
    protected function _preparePHPValue($value, $fieldType, $checknull=true){
        if($checknull){
            if($value == 'null' || $value == 'NULL' || $value === null)
                return 'NULL';
        }
        switch(strtolower($fieldType)){
            case 'int':
            case 'integer':
            case 'autoincrement':
                return intval($value);
            case 'double':
            case 'float':
                return doubleval($value);
            case 'numeric': //usefull for bigint and stuff
            case 'bigautoincrement':
                if(is_numeric($value))
                    return $value;
                else
                    return intval($value);
            case 'boolean':
                return $this->getBooleanValue($value);
            default:
                if(strpos($value,"'") !== false){
                    return '\'.$this->_conn->quote(\''.str_replace('\'','\\\'',$value).'\').\'';
                }else{
                    return "\\'".$value."\\'";
                }
        }
    }

    protected function _preparePHPExpr($expr, $field, $checknull=true, $forCondition=''){
        $opnull=$opval='';
        if($checknull && $forCondition != ''){
            if($forCondition == '=')
                $opnull = 'IS ';
            elseif($forCondition == '<>')
                $opnull = 'IS NOT ';
            else
                $checknull=false;
        }
        if($forCondition!='')
            $forCondition = '\''.$forCondition.'\'.';

        switch(strtolower($field->datatype)){
            case 'int':
            case 'integer':
                if($checknull){
                    $expr= '('.$expr.' === null ? \''.$opnull.'NULL\' : '.$forCondition.'intval('.$expr.'))';
                }else{
                    $expr= $forCondition.'intval('.$expr.')';
                }
                break;
            case 'autoincrement':
                $expr= $forCondition.'intval('.$expr.')';
                break;
            case 'double':
            case 'float':
                if($checknull){
                    $expr= '('.$expr.' === null ? \''.$opnull.'NULL\' : '.$forCondition.'doubleval('.$expr.'))';
                }else{
                    $expr= $forCondition.'doubleval('.$expr.')';
                }
                break;
            case 'numeric': //usefull for bigint and stuff
                if($checknull){
                    $expr='('.$expr.' === null ? \''.$opnull.'NULL\' : '.$forCondition.'(is_numeric ('.$expr.') ? '.$expr.' : intval('.$expr.')))';
                }else{
                    $expr=$forCondition.'(is_numeric ('.$expr.') ? '.$expr.' : intval('.$expr.'))';
                }
                break;
            case 'bigautoincrement':
                $expr=$forCondition.'(is_numeric ('.$expr.') ? '.$expr.' : intval('.$expr.'))';
                break;
            case 'boolean':
                if($checknull){
                    $expr= '('.$expr.' === null ? \''.$opnull.'NULL\' : '.$forCondition.'('.$expr.'?\''.$this->trueValue.'\':\''.$this->falseValue.'\'))';
                }else{
                    $expr= $forCondition.'('.$expr.'?\''.$this->trueValue.'\':\''.$this->falseValue.'\')';
                }
                break;
            default:
                if($checknull){
                    $expr= '('.$expr.' === null ? \''.$opnull.'NULL\' : '.$forCondition.'$this->_conn->quote('.$expr.',false))';
                }else{
                    $expr= $forCondition.'$this->_conn->quote('.$expr.')';
                }
        }
        return $expr;
    }

    protected function _encloseName($name){
        return $name;
    }

    protected function genUpdateAutoIncrementPK($pkai, $pTableRealName) {
        return '       $record->'.$pkai->name.'= $this->_conn->lastInsertId();';
    }

    protected function getBooleanValue($value){
        return (strtolower($value)=='true'|| $value =='1'|| $value=='t'?$this->trueValue:$this->falseValue);
    }
}
?>