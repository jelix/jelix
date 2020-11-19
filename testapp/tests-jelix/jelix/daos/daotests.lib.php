<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Laurent Jouanneau
* @contributor
* @copyright   2009 Laurent Jouanneau
* @link        http://jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

require_once(JELIX_LIB_PATH.'dao/jDaoCompiler.class.php');
require_once(JELIX_LIB_PATH.'plugins/daobuilder/mysql/mysql.daobuilder.php');
include_once (JELIX_LIB_PATH.'db/tools/jDbMysqlTools.php');
include_once (JELIX_LIB_PATH.'db/tools/jDbPgsqlTools.php');


class fakejSelectorDao extends jSelectorDao {
  
  function __construct($module='', $resource='', $driver='mysqli', $dbType='mysql') {
      $this->driver = $driver;
      $this->_compiler = 'jDaoCompiler';
      $this->_compilerPath = JELIX_LIB_PATH.'dao/jDaoCompiler.class.php';
      $this->module = $module;
      $this->resource = $resource;
      $this->_path = '';
      $this->_where =   '';
      $this->dbType = $dbType;
  }
}

class testMysqlDaoGenerator extends mysqlDaoBuilder {

    function GetPkFields() {
        return $this->_getPrimaryFieldsList();
    }

    function GetPropertiesBy ($captureMethod){
        return $this->_getPropertiesBy ($captureMethod);
    }

    function BuildSimpleConditions2 (&$fields, $fieldPrefix='', $forSelect=true){
        return $this->buildSimpleConditions ($fields, $fieldPrefix, $forSelect);
    }

    function BuildConditions2($cond, $fields, $params=array(), $withPrefix=true) {
        return $this->buildConditions ($cond, $fields, $params, $withPrefix);
    }

    function BuildSQLCondition ($condition, $fields, $params, $withPrefix){
        return $this->buildOneSQLCondition ($condition, $fields, $params, $withPrefix, true);
    }

    function GetPreparePHPValue($value, $fieldType, $checknull=true){
        return $this->tools->escapeValue($fieldType, $value, $checknull, true);
    }

    function GetPreparePHPExpr($expr, $fieldType, $checknull=true, $forCondition=''){
        return $this->_preparePHPExpr($expr, $fieldType, $checknull, $forCondition);
    }

    function GetSelectClause ($distinct=false){
        return $this->buildSelectClause ($distinct);
    }

    function GetFromClause(){
        $this->buildFromWhereClause();
        return array($this->sqlFromClause, $this->sqlWhereClause);
    }
    
    function PrepareValues ($fieldList, $pattern, $prefixfield) {
        return $this->_prepareValues($fieldList, $pattern, $prefixfield);
    }

    function GetBuildCountUserQuery($method) {
      $allField = $this->_getPropertiesBy('All');
      $src = array();
      parent::buildCountUserQuery($method, $src, $allField);
      return implode("\n", $src);
    }
    
    function GetBuildUpdateUserQuery($method, &$src, &$primaryFields) {
        $this->buildUpdateUserQuery($method, $src, $primaryFields);
    }
}


class testDaoProperty {
    public $datatype;
    public $unifiedType;
    public $defaultValue=null;
    public $autoIncrement = false;
}


class testjDaoParser extends jDaoParser {
  
  function testParseDatasource($xml) {
      $this->parseDatasource($xml);
  }
  function testParseRecord($xml, $tools) {
      $this->parseRecord($xml, $tools);
  }
  function testParseFactory($xml) {
      $this->parseFactory($xml);
  }
}
