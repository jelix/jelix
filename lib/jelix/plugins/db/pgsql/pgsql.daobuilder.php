<?php
/**
* @package    jelix
* @subpackage db_driver
* @author     Laurent Jouanneau
* @contributor
* @copyright  2007 Laurent Jouanneau
* @link      http://www.jelix.org
* @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*/

/**
 * driver for jDaoCompiler
 * @package    jelix
 * @subpackage db_driver
 */
class pgsqlDaoBuilder extends jDaoGenerator {

    protected $propertiesListForInsert = 'PrimaryFieldsExcludeAutoIncrement';
    
    protected $trueValue = 'TRUE';
    
    protected $falseValue = 'FALSE';
    
    function __construct($factoryClassName, $recordClassName, $daoDefinition){
        parent::__construct($factoryClassName, $recordClassName, $daoDefinition);

    }

    protected function genUpdateAutoIncrementPK($pkai, $pTableRealName) {
        return '          $record->'.$pkai->name.'= $this->_conn->lastInsertId(\''.$pkai->sequenceName.'\');';
    }

    protected function _encloseName($name){
        return '"'.$name.'"';
    }

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
               if(!strlen($field->sequenceName)){
                  $field->sequenceName = $tb.'_'.$field->name.'_seq';
               }
               return $field;
            }
        }
        return null;
    }

}
?>