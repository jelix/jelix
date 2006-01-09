<?php
/**
* @package    jelix
* @subpackage dao
* @version    $Id:$
* @author     Croes Gérald, Laurent Jouanneau
* @contributor Laurent Jouanneau
* @copyright  2001-2005 CopixTeam, 2005-2006 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*
* Une partie du code est issue de la classe CopixDAOGeneratorV1
* du framework Copix 2.3dev20050901. http://www.copix.org
* il est sous Copyright 2001-2005 CopixTeam (licence LGPL)
* Auteurs initiaux : Gerald Croes et Laurent Jouanneau
* Adaptée et améliorée pour Jelix par Laurent Jouanneau
*/

/**
* Générateur d'une classe PHP correspondant à un objet DAO définit dans une fichier xml
* de définition
*/

class jDAOGenerator {

   /**
   * The compiler object
   * @var jDAOCompiler
   */
   private $_compiler=null;

   /**
   * the dao definition.
   * @var jDAOParser
   */
   private $_datasParser = null;

   /**
   * The DAORecord ClassName
   * @var string
   */
   private $_DAORecordClassName = null;

   /**
   * the DAO classname
   * @var string
   */
   private $_DAOClassName=null;

   /**
   * constructor
   * @param jDAOCompiler $compiler
   * @param jDAOParser $daoDefinition
   */
   function __construct($compiler, $daoDefinition){
      $this->_compiler = $compiler;
      $this->_datasParser = $daoDefinition;
      $this->_DAOClassName = $compiler->getSelector()->getDAOClass();
      $this->_DAORecordClassName = $compiler->getSelector()->getDAORecordClass();
   }

   /**
   * build all classes
   */
   public function buildClasses () {

      $src = array();
      $src[] = ' require_once ( JELIX_LIB_DAO_PATH .\'jDAOBase.class.php\');';
      $src[] = ' require_once ( JELIX_LIB_DB_PATH .\'jDbWidget.class.php\');';

      //-----------------------
      // Build the record class
      //-----------------------

      $src[] = "\nclass ".$this->_DAORecordClassName.' extends jDAORecordBase {';

      $properties=array();

      foreach ($this->_datasParser->getProperties() as $id=>$field){
          $properties[$id] = get_object_vars($field);
          $src[] =' public $'.$id.';';
      }

      $src[] = ' protected $_properties = '.var_export($properties, true).';';

      $src[] = '}';

      //--------------------
      // Build the dao class
      //--------------------

      // prepare some values to generate methods

      list($sqlFromClause, $sqlWhereClause)= $this->_getFromClause();
      $tables            = $this->_datasParser->getTables();
      $sqlSelectClause   = $this->_getSelectClause();
      $pkFields          = $this->_getPropertiesBy('PkFields');
      $pTableRealName    = $tables[$this->_datasParser->getPrimaryTable()]['realname'];
      $driverName        = $this->_compiler->getDbDriver();
      $pkai              = $this->_getAutoIncrementField();
      $sqlPkCondition    = $this->_buildSimpleConditions($pkFields);
      if($sqlPkCondition != ''){
         $sqlPkCondition= ($sqlWhereClause !='' ? ' AND ':' WHERE ').$sqlPkCondition;
      }

      $src[] = "\nclass ".$this->_DAOClassName.' extends jDAOFactoryBase {';
      $src[] ='   protected $_tables = '.var_export($tables, true).';';
      $src[] ='   protected $_primaryTable = \''.$this->_datasParser->getPrimaryTable().'\';';
      $src[] ='   protected $_selectClause=\''.$sqlSelectClause.'\';';
      $src[] ='   protected $_fromClause=\''.$sqlFromClause.'\';';
      $src[] ='   protected $_whereClause=\''.$sqlWhereClause.'\';';
      $src[] ='   protected $_DAORecordClassName=\''.$this->_DAORecordClassName.'\';';
      $src[] ='   protected $_pkFields = array('.$this->_writeFieldNamesWith ($start = '\'', $end='\'', $beetween = ',', $pkFields).');';


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
         if (($driverName=='mysql') || ($driverName=='sqlserver')) {
            $fields = $this->_getPropertiesBy('PrimaryFieldsExcludeAutoIncrement');
         }elseif ($pkai->sequenceName != ''){
            $src[] = '     $record->'.$pkai->name.'= $this->_conn->lastInsertId(\''.$pkai->sequenceName.'\');';
            $fields = $this->_getPropertiesBy('All');
         }else{
            $fields = $this->_getPropertiesBy('All');
         }
      }else{
         $fields = $this->_getPropertiesBy('PrimaryFieldsExcludeAutoIncrement');
      }

      $src[] = '    $query = \'INSERT INTO '.$pTableRealName.' (';

      list($fields, $values) = $this->_prepareValues($fields,'insertMotif', 'record->');

      $src[] = implode(',',$fields);
      $src[] = ') VALUES (';
      $src[] = implode(', ',$values);
      $src[] = ")';";
      $src[] = '   $result = $this->_conn->exec ($query);';


      if($pkai !== null){
         $src[] = '   if($result){';
         if ($driverName=='mysql') {
            $src[] = '      $record->'.$pkai->name.'= $this->_conn->lastInsertId();';
         }else if ($driverName=='sqlserver') {
            $src[] = '      $record->'.$pkai->name.'= $this->_conn->lastIdInTable(\''.$pkai->fieldName.'\',\''.$pTableRealName.'\');';
         }
         $src[] = '    return $result;';
         $src[] = ' }else return false;';
      }else{
          $src[] = '  return $result;';
      }
      $src[] = '}';



      //-----  update method

      $src[] = 'public function update ($record){';
      $src[] = '   $query = \'UPDATE '.$pTableRealName.' SET ';

      list($fields, $values) = $this->_prepareValues($this->_getPropertiesBy('PrimaryFieldsExcludePk'),'updateMotif', 'record->');

      $sqlSet='';
      foreach($fields as $k=> $fname){
         $sqlSet.= ', '.$fname. '= '. $values[$k];
      }
      $src[] = substr($sqlSet,1);

      $sqlCondition = $this->_buildSimpleConditions($pkFields, 'record->', false);
      if($sqlCondition!='')
         $src[] = ' where '.$sqlCondition;

      $src[] = "';";
      $src[] = '   return $this->_conn->exec ($query);';
      $src[] = " }";//ends the update function


      //----- other user methods

      $allField = $this->_getPropertiesBy('All');
      $primaryFields = $this->_getPropertiesBy('PrimaryTable');
      $ct=null;


      foreach($this->_datasParser->getMethods() as $name=>$method){

         $mparam=implode(', $',$method->getParameters());
         if($mparam != '') $mparam ='$'.$mparam;

         $src[] = ' function '.$method->name.' ('. $mparam.'){';

         $limit='';

         switch($method->type){
               case 'delete':
                  $src[] = '    $query = \'DELETE FROM '.$pTableRealName.' \';';
                  $glueCondition =' WHERE ';
                  break;
               case 'update':
                  $src[] = '    $query = \'UPDATE '.$pTableRealName.' SET ';
                  $updatefields = $this->_getPropertiesBy('PrimaryFieldsExcludePk');
                  $sqlSet='';
                  foreach($method->getValues() as $propname=>$value){
                     if($value[1]){
                        foreach($method->getParameters() as $param){
                           $value[0] = str_replace('$'.$param, '\'.'.$this->_preparePHPValue('$'.$param, $updatefields[$propname]->datatype,false).'.\'',$value[0]);
                        }
                        $sqlSet.= ', '.$updatefields[$propname]->fieldName. '= '. $value[0];
                     }else{
                        $sqlSet.= ', '.$updatefields[$propname]->fieldName. '= '. $value[0];
                     }
                  }
                  $src[] =substr($sqlSet,1).'\';';

                  $glueCondition =' WHERE ';
                  break;

               case 'php':
                  $src[] = $method->getBody();
                  $src[] = '}';
                  break;


               case 'selectfirst':
               case 'select':
               default:
                  $src[] = '    $query = $this->_selectClause.$this->_fromClause.$this->_whereClause;';
                  $glueCondition = ($sqlWhereClause !='' ? ' AND ':' WHERE ');
                  if( ($lim = $method->getLimit ()) !==null){
                     $limit=', '.$lim['offset'].', '.$lim['count'];
                  }

               break;
         }

         if($method->type == 'php')
            continue;

         $cond = $method->getConditions();

         if($cond !== null){
            if($method->type == 'delete' || $method->type == 'update')
               $sqlCond = $this->_buildConditions($cond, $primaryFields, $method->getParameters(), false);
            else
               $sqlCond = $this->_buildConditions($cond, $allField,$method->getParameters(),true);

            if(trim($sqlCond) != '')
               $src[] = '$query .=\''.$glueCondition.$sqlCond."';";
            else
               $src[] =";";
         }else
            $src[] =";";

         switch($method->type){
               case 'delete':
               case 'update' :
                  $src[] = '    return $this->_conn->exec ($query);';
               break;
               case 'selectfirst':
                  $src[] = '    $dbw = new jDbWidget ($this->_conn);';
                  $src[] = '    return $dbw->fetchFirst ($query, \''.$this->_compiler->_DAOid.'\');';
                  break;
               case 'select':
               default:
                  $src[] = '    $dbw = new jDbWidget ($this->_conn);';
                  $src[] = '    return $dbw->fetchAllInto ($query, \''.$this->_DAORecordClassName.'\''.$limit.');';
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
    private function _getFromClause(){

      $driverName = $this->_compiler->getDbDriver();
      $aliaslink = ($driverName == 'oci8'?' ':' AS ');

      $sqlWhere = '';
      $tables = $this->_datasParser->getTables();

      $primarytable = $tables[$this->_datasParser->getPrimaryTable()];
      if($primarytable['name']!=$primarytable['realname'])
         $sqlFrom =$primarytable['realname'].$aliaslink.$primarytable['name'];
      else
         $sqlFrom =$primarytable['realname'];

      foreach($this->_datasParser->getOuterJoins() as $tablejoin){
         $table= $tables[$tablejoin[0]];

         if($table['name']!=$table['realname'])
            $r =$table['realname'].$aliaslink.$table['name'];
         else
            $r =$table['realname'];

         if ($driverName == 'oci8') {
            if($tablejoin[1] == 0){
               $fieldjoin=$primarytable['name'].'.'.$table['onforeignkey'].'='.$table['name'].'.'.$table['primarykey'].'(+)';
            }elseif($tablejoin[1] == 1){
               $fieldjoin=$primarytable['name'].'.'.$table['onforeignkey'].'(+)='.$table['name'].'.'.$table['primarykey'];
            }
            $sqlFrom.=', '.$r;
            $sqlWhere.=' AND '.$fieldjoin;
         }else{
            $fieldjoin=$primarytable['name'].'.'.$table['onforeignkey'].'='.$table['name'].'.'.$table['primarykey'];
            if($tablejoin[1] == 0){
               $sqlFrom.=' LEFT JOIN '.$r.' ON ('.$fieldjoin.')';
            }elseif($tablejoin[1] == 1){
               $sqlFrom.=' RIGHT JOIN '.$r.' ON ('.$fieldjoin.')';
            }
         }
      }

      foreach($this->_datasParser->getInnerJoins() as $tablejoin){
         $table= $tables[$tablejoin];
         if($table['name']!=$table['realname'])
            $sqlFrom .=', '.$table['realname'].$aliaslink.$table['name'];
        else
            $sqlFrom .=', '.$table['realname'];
         $sqlWhere.=' AND '.$primarytable['name'].'.'.$table['onforeignkey'].'='.$table['name'].'.'.$table['primarykey'];
      }

      $sqlWhere=($sqlWhere !='') ? ' WHERE '.substr($sqlWhere,4) :'';
      return array(' FROM '.$sqlFrom,$sqlWhere);
   }

    /**
    * build SELECT clause for all SELECT queries
    */
   private function _getSelectClause (){
      $result = array();

      $driverName = $this->_compiler->getDbDriver();

      $tables = $this->_datasParser->getTables();
      foreach ($this->_datasParser->getProperties () as $id=>$prop){

         $table = $tables[$prop->table]['name'] .'.';

         if ($prop->selectMotif !=''){
            if ($prop->selectMotif =='%s'){
               if ($prop->fieldName != $prop->name){
                     //in oracle we must escape name
                  if ($driverName == 'oci8') {
                     $result[] = $table.$prop->fieldName.' "'.$prop->name.'"';
                  }else{
                     $result[] = $table.$prop->fieldName.' as '.$prop->name;
                  }
               }else{
                  $result[] = $table.$prop->fieldName;
               }
            }else{
               //in oracle we must escape name
               if ($driverName == 'oci8') {
                  $result[] = sprintf ($prop->selectMotif, $table.$prop->fieldName).' "'.$prop->name.'"';
               }else{
                  $result[] = sprintf ($prop->selectMotif, $table.$prop->fieldName).' as '.$prop->name;
               }
            }
         }
      }

      return 'SELECT '.(implode (', ',$result));
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
    * @see  jDAOProperty
    */
    private function _writeFieldsInfoWith ($info, $start = '', $end='', $beetween = '', $using = null){
        $result = array();
        if ($using === null){
            //if no fields are provided, using _datasParser's as default.
            $using = $this->_datasParser->getProperties ();
        }

        foreach ($using as $id=>$field){
            $result[] = $start . $field->$info . $end;
        }

        return implode ($beetween,$result);;
    }

    /**
    * format field names with start, end and between strings.
    */
    private function _writeFieldNamesWith ($start = '', $end='', $beetween = '', $using = null){
        return $this->_writeFieldsInfoWith ('name', $start, $end, $beetween, $using);
    }


    /**
    * gets fields that match a condition returned by the $captureMethod
    */
    private function _getPropertiesBy ($captureMethod){
        $captureMethod = '_capture'.$captureMethod;
        $result = array ();

        foreach ($this->_datasParser->getProperties() as $field){
            if ( $this->$captureMethod($field)){
                $result[$field->name] = $field;
            }
        }
        return $result;
    }

    private function _capturePkFields(&$field){
        return ($field->table == $this->_datasParser->getPrimaryTable()) && $field->isPK;
    }

    private function _capturePrimaryFieldsExcludeAutoIncrement(&$field){
        return ($field->table == $this->_datasParser->getPrimaryTable()) &&
        ($field->datatype != 'autoincrement') && ($field->datatype != 'bigautoincrement');
    }

    private function _capturePrimaryFieldsExcludePk(&$field){
        return ($field->table == $this->_datasParser->getPrimaryTable()) && !$field->isPK;
    }

    private function _capturePrimaryTable(&$field){
        return ($field->table == $this->_datasParser->getPrimaryTable());
    }
    private function _captureAll(&$field){
        return true;
    }


    /**
    * get autoincrement PK field
    */
    private function _getAutoIncrementField ($using = null){
        if ($using === null){
            $using = $this->_datasParser->getProperties ();
        }

        $driverName = $this->_compiler->getDbDriver();
        $tb = $this->_datasParser->getTables();
        $tb = $tb[$this->_datasParser->getPrimaryTable()]['realname'];

        foreach ($using as $id=>$field) {
            if ($field->datatype == 'autoincrement' || $field->datatype == 'bigautoincrement') {
               if($driverName=="postgresql" && !strlen($field->sequenceName)){
                  $field->sequenceName=$tb.'_'.$field->name.'_seq';
               }
               return $field;
            }
        }
        return null;
    }


   private function _buildSimpleConditions (&$fields, $fieldPrefix='', $forSelect=true){
        $r = ' ';

        $first = true;
        foreach($fields as $field){
            if (!$first){
                $r .= ' AND ';
            }else{
               $first = false;
            }

            if($forSelect){
               $condition = $field->table.'.'.$field->fieldName;
            }else{
               $condition = $field->fieldName;
            }

            $var = '$'.$fieldPrefix.$field->name;
            $value = $this->_preparePHPValue($var,$field->datatype,false);

            $r .= $condition.'\'.('.$var.'===null ? \' IS NULL \' : \' = \'.'.$value.').\'';
        }

        return $r;
    }


   function _prepareValues ($fieldList, $motif='', $prefixfield=''){
      $values = $fields = array();

      foreach ((array)$fieldList as $fieldName=>$field) {
         if ($motif != '' && $field->$motif == ''){
               continue;
         }

         $value = $this->_preparePHPValue('$'.$prefixfield.$fieldName, $field->datatype, true);

         if($motif != ''){
               $values[$field->name] = sprintf($field->$motif,'\'.'.$value.'.\'');
         }else{
               $values[$field->name] = '\'.'.$value.'.\'';
         }

         $fields[$field->name] = $field->fieldName;
      }
      return array($fields, $values);
   }


    /**
     *
     * @param jDAOConditions
     * @param array
     */
    private function _buildConditions ($cond, $fields, $params=array(), $withPrefix=true){
        $sql = $this->_buildSQLCondition ($cond->condition, $fields, $params,$withPrefix, true);

        $order = array ();
        foreach ($cond->order as $name => $way){
            if (isset($fields[$name])){
                if($way{0} == '$'){
                    $order[] = $name.' \'.( strtolower('.$way.') ==\'asc\'?\'asc\':\'desc\').\'';
                }else
                    $order[] = $name.' '.$way;
            }
        }
        if(count ($order) > 0){
            if(trim($sql) =='') {
                $sql.= ' 1=1 ';
            }
            $sql.=' ORDER BY '.implode (', ', $order);
        }
        return $sql;
    }


    /**
     * @param jDAOCondition
     */
    function _buildSQLCondition ($condition, $fields, $params, $withPrefix, $principal=false){
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
               $f = $prop->table.'.'.$prop->fieldName;
            }else{
               $f = $prop->fieldName;
            }

            $r .= $f.' '.$cond['operator'].' ';


            if($cond['expr']){
                $value=$cond['value'];
                foreach($params as $param){
                    $value = str_replace('$'.$param, '\'.'.$this->_preparePHPValue('$'.$param, $prop->datatype, false).'.\'',$value);
                }
            }else{
                $value= '\'.'.$this->_preparePHPValue('\''.$cond['value'].'\'', $prop->datatype,false).'.\'';
            }
            $r.=$value;

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
        if (strlen (trim ($r)) > 0 && !$principal){
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
   function _preparePHPValue($value, $fieldType, $checknull=true){
      switch(strtolower($fieldType)){
         case 'int':
         case 'integer':
         case 'autoincrement':
            if($checknull){
               $value= '('.$value.' === null ? \'NULL\' : intval('.$value.'))';
            }else{
               $value= 'intval('.$value.')';
            }
            break;
         case 'double':
         case 'float':
            if($checknull){
               $value= '('.$value.' === null ? \'NULL\' : doubleval('.$value.'))';
            }else{
               $value= 'doubleval('.$value.')';
            }
            break;
         case 'numeric': //usefull for bigint and stuff
         case 'bigautoincrement':
            if($checknull){
               $value='('.$value.' === null ? \'NULL\' : (is_numeric ('.$value.') ? '.$value.' : intval('.$value.')))';
            }else{
               $value='(is_numeric ('.$value.') ? '.$value.' : intval('.$value.'))';
            }
            break;
         default:
            $value ='$this->_conn->quote ('.$value.')';
      }
      return $value;
   }


}
?>
