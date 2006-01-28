<?php
/**
 * @package    jelix
 * @subpackage dao
 * @version    $Id:$
 * @author     Laurent Jouanneau
 * @contributor
 * @copyright  2005-2006 Laurent Jouanneau
 * @link        http://www.jelix.org
 * @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */

abstract class jDAORecordBase {

   const ERROR_REQUIRED=1;
   const ERROR_BAD_TYPE=2;
   const ERROR_BAD_FORMAT=3;
   const ERROR_MAXLENGTH = 4;
   const ERROR_MINLENGTH = 5;


   protected $_properties;

   public function getProperties(){ return $this->_properties; }

   public function check(){
      $errors=array();
      foreach($this->_properties as $prop=>$infos){
         $value = $this->$prop;

         // test required
         if($infos['required'] && $value === null && $infos['datatype'] != 'autoincrement' && $infos['datatype'] != 'bigautoincrement'){
            $errors[$prop][] = self::ERROR_REQUIRED;
            continue;
         }

         if($infos['datatype']=='varchar' || $infos['datatype']=='string'){
            if(!is_string($value) && $value !== null){
               $errors[$prop][] = self::ERROR_BAD_TYPE;
               continue;
            }
            // test regexp
            if ($infos['regExp'] !== null && preg_match ($infos['regExp'], $value) === 0){
               $errors[$prop][] = self::ERROR_BAD_FORMAT;
               continue;
            }

            //  test maxlength et minlength
            $len = strlen($value);
            if($infos['maxlength'] !== null && $len > intval($infos['maxlength'])){
               $errors[$prop][] = self::ERROR_MAXLENGTH;
            }

            if($infos['minlength'] !== null && $len < intval($infos['minlength'])){
               $errors[$prop][] = self::ERROR_MINLENGTH;
            }


         }elseif( in_array($infos['datatype'], array('int','integer','numeric', 'double', 'float'))) {
            // test datatype
            if($value !== null && !is_numeric($value)){
               $errors[$prop][] = self::ERROR_BAD_TYPE;
               continue;
            }
         }elseif( in_array($infos['datatype'], array('datetime', 'time','varchardate', 'date'))) {
            if (jLocale::timestampToDate ($value) === false){
               $errors[$prop][] = self::ERROR_BAD_FORMAT;
               continue;
            }
         }
      }
      return $errors;
   }
}







/**
 */
abstract class jDAOFactoryBase  {

   protected $_tables;
   protected $_primaryTable;
   protected $_fields;
   protected $_conn;
   protected $_selectClause;
   protected $_fromClause;
   protected $_whereClause;
   protected $_DAORecordClassName;
   protected $_pkFields;


   function  __construct($conn){
      $this->_conn = $conn;
   }

   public function findAll(){
      $dbw = new jDbWidget($this->_conn);
      return $dbw->fetchAllInto($this->_selectClause.$this->_fromClause.$this->_whereClause , $this->_DAORecordClassName);
   }

   public function get(){
      $args=func_get_args();
      $keys = array_combine($this->_pkFields,$args );

      if($keys === false){
         throw new jException('jelix~dao.error.keys.missing');
      }

      $dbw =  new jDbWidget ($this->_conn);
      $q = $this->_selectClause.$this->_fromClause.$this->_whereClause;
      $q .= $this->_getPkWhereClauseForSelect($keys);

      $record = $dbw->fetchFirstInto($q, $this->_DAORecordClassName);
      return $record;
   }

   public function delete(){
      $args=func_get_args();
      $keys = array_combine($this->_pkFields, $args);
      if($keys === false){
         throw new jException('jelix~dao.error.keys.missing');
      }
      $q = 'DELETE FROM '.$this->_tables[$this->_primaryTable]['realname'].' where ';
      $q.= $this->_getPkWhereClauseForNonSelect($keys);
      return $this->_conn->exec ($q);
   }

   abstract public function insert ($record);
   abstract public function update ($record);


   /**
    * @param jDAOConditions $searchcond
    */
   public function findBy ($searchcond){
      $query = $this->_selectQuery.$this->_fromClause.$this->_whereClause;
      if (!$searchcond->isEmpty ()){
         $query .= ($this->_whereClause !='' ? ' AND ' : ' WHERE ');
         $query .= $this->_createConditionsClause($searchcond);
      }
      $dbw = new jDBWidget ($this->_conn);
      return $dbw->fetchAllInto ($query, $this->_DAORecordClassName);
   }

   abstract protected function _getPkWhereClauseForSelect($pk);
   abstract protected function _getPkWhereClauseForNonSelect($pk);

   /**
    *
    */
   protected function _createConditionsClause($daocond){


      $sql = $this->_generateCondition ($daocond, true);

      $order = array ();
      foreach ($daocond->order as $name => $way){
         if (isset($fields[$name])){
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


   protected function _generateCondition($condition, $principal=true){
      $r = ' ';
      $first = true;
      foreach ($condition->conditions as $cond){
         if (!$first){
            $r .= ' '.$condition->glueOp.' ';
         }
         $first = false;

         $prop=$this->_fields[$cond['field_id']];

         if(isset($prop[2]) && $prop[2] != ''){
            $prefix = $prop[2].'.'.$prop[0];
         }else{
            $prefix = $prop[0];
         }

         if(isset($prop[3]) && $prop[3] != '' && $prop[3] != '%s'){
            $prefix=sprintf($prop[3], $prefix);
         }

         $prefixNoCondition = $prefix;
         $prefix.=' '.$cond['condition'].' '; // ' ' pour les like..

         if (!is_array ($cond['value'])){
            $value = $this->_prepareValue($cond['value'],$prop[1]);
            if ($value === 'NULL' && $cond['operator'] == '='){
               $r .= $prefixNoCondition.' IS '.$value;
            } else {
               $r .= $prefix.$value;
            }
         }else{
            $r .= ' ( ';
                  $firstCV = true;
                  foreach ($cond['value'] as $conditionValue){
                  if (!$firstCV){
                  $r .= ' or ';
                  }
                  $value = $this->_prepareValue($conditionValue,$prop[1]);
                  if (($value === 'NULL') && ($cond['operator'] == '=')){;
                  $r .= $prefixNoCondition.' IS '.$value;
                  }else{
                  $r .= $prefix.$value;
                  }
                  $firstCV = false;
                  }
                  $r .= ' ) ';
         }
      }
      //sub conditions
      foreach ($condition->group as $conditionDetail){
         if (!$first){
            $r .= ' '.$condition->glueOp.' ';
         }
         $r .= $this->_generateCondition($conditionDetail,false);
         $first=false;
      }

      //adds parenthesis around the sql if needed (non empty)
      if (strlen (trim ($r)) > 0 && !$principal){
         $r = '('.$r.')';
      }
      return $r;
   }
   /**
    * prepare the value ready to be used in a dynamic evaluation
    */
   protected function _prepareValue($value, $fieldType){
      switch(strtolower($fieldType)){
         case 'int':
         case 'integer':
         case 'autoincrement':
            $value = $value === null ? 'NULL' : intval($value);
            break;
         case 'double':
         case 'float':
            $value = $value === null ? 'NULL' : doubleval($value);
            break;
         case 'numeric'://usefull for bigint and stuff
         case 'bigautoincrement':
            if (is_numeric ($value)){
               //was numeric, we can sends it as is
               // no cast with intval else overflow
               return $value === null ? 'NULL' : $value;
            }else{
               //not a numeric, nevermind, casting it
               return $value === null ? 'NULL' : intval ($value);
            }
            break;
         default:
            $value = $this->_conn->quote ($value);
      }
      return $value;
   }

}
?>
