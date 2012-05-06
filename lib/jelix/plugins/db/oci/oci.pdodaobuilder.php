<?php
/**
* @package    jelix
* @subpackage db_driver
* @author     Laurent Jouanneau
* @contributor Gwendal Jouannic
* @copyright  2007-2009 Laurent Jouanneau
* @link      http://www.jelix.org
* @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*/

/**
 * driver for jDaoCompiler
 * @package    jelix
 * @subpackage db_driver
 */
class ociPdoDaoBuilder extends jPdoDaoGenerator {

    protected $aliasWord = ' ';
    protected $propertiesListForInsert = 'PrimaryFieldsExcludeAutoIncrement';

    protected function buildOuterJoins(&$tables, $primaryTableName){
        $sqlFrom = '';
        $sqlWhere ='';
        foreach($this->_dataParser->getOuterJoins() as $tablejoin){
            $table= $tables[$tablejoin[0]];
            $tablename = $this->_encloseName($table['name']);

            $r = $this->_encloseName($table['realname']).' '.$tablename;

            $fieldjoin='';

            if($tablejoin[1] == 0){
                $operand='='; $opafter='(+)';
            }elseif($tablejoin[1] == 1){
                $operand='(+)='; $opafter='';
            }
            foreach($table['fk'] as $k => $fk){
                $fieldjoin.=' AND '.$primaryTableName.'.'.$this->_encloseName($fk).$operand.$tablename.'.'.$this->_encloseName($table['pk'][$k]).$opafter;
            }
            $sqlFrom.=', '.$r;
            $sqlWhere.=$fieldjoin;
        }
        return array($sqlFrom, $sqlWhere);
    }

    protected function buildSelectPattern ($pattern, $table, $fieldname, $propname ){
        if ($pattern =='%s'){
            if ($fieldname != $propname){
                $field = $table.$this->_encloseName($fieldname).' "'.$propname.'"';
            }else{
                $field = $table.$this->_encloseName($fieldname);
            }
        }else{
            $field = str_replace(array("'", "%s"), array("\\'",$table.$this->_encloseName($fieldname)),$pattern).' "'.$propname.'"';
        }
        return $field;
    }

    /*
     * Replaces the lastInsertId which doesn't work with oci
     */
    protected function buildUpdateAutoIncrementPK($pkai) {
        return '          $record->'.$pkai->name.'= $this->_conn->query(\'SELECT '.$pkai->sequenceName.'.currval as '.$pkai->name.' from dual\')->fetch()->'.$pkai->name.';';
    }

   
     /**
     * build the insert() method in the final class
     * @return string the source of the method
     */
    protected function buildInsertMethod($pkFields) {
        $pkai = $this->getAutoIncrementPKField();
        $src = array();
        $src[] = 'public function insert ($record){';

        $fields = $this->_getPropertiesBy('PrimaryTable');

        if($this->_dataParser->hasEvent('insertbefore') || $this->_dataParser->hasEvent('insert')){
            $src[] = '   jEvent::notify("daoInsertBefore", array(\'dao\'=>$this->_daoSelector, \'record\'=>$record));';
        }
        
        // if there isn't a autoincrement as primary key, then we do a full insert.
        // if there isn't a value for the autoincrement field and if this is a mysql/sqlserver and pgsql,
        // we do an insert without given primary key. In other case, we do a full insert.

        $src[] = '    $query = \'INSERT INTO '.$this->tableRealNameEsc.' (';

        $fieldNames = array ();
        foreach ($fields as $field)
            if (!$field->autoIncrement)
                $fieldNames[] = $field->fieldName;
        $src[] = implode (',', $fieldNames);
        
        $src[] = ') VALUES (';
        
        $returning = array ();
        $returning_bind = '';
        foreach ($fields as $field) {
            if ($field->autoIncrement) {
                $returning['field'][] =  $field->fieldName; 
                $returning['bind'][] = ':' . $field->fieldName;
                $returning_bind .= $this->_codeBindParam ($field) ."\n";
                continue;
            }
            if (strlen ($field->sequenceName)) {
                $values[] = $field->sequenceName . '.nextval';
            }
            else if (strlen ($field->insertPattern) && ($field->insertPattern != '%s')) {
                $values[] = str_replace ("'", "\\'", sprintf($field->insertPattern,  ':' . $field->fieldName)); // Eescape quotes as needed
                $binds[] = $field;
            }
            else {
                $values[] = ':' . $field->fieldName;
                $binds[] = $field;
            }
            if ($field->isPK) {
                $returning['field'][] = $field->fieldName;
                $returning['bind'][] = ':' . $field->fieldName;
                $returning_bind .= $this->_codeBindParam ($field) ."\n";
            }
        }
        
        $src[] = '    ' . implode (',', $values);
        $src[] = '    )';
        // $src[] = '    RETURNING ' . implode (',', $returning['field']) . ' INTO ' . implode (',', $returning['bind']);
        $src[] = '    \';';
        
        $src[] = '    $sth = $this->_conn->prepare ($query);';
        
        $src[] = $returning_bind;
        
        // Bind the variables, at last
        foreach ($binds as $bind) 
            $src[] = $this->_codeBindParam ($bind);

        $src[] = '    $result = $sth->execute ();';

        if($this->_dataParser->hasEvent('insertafter') || $this->_dataParser->hasEvent('insert')){
            $src[] = '   jEvent::notify("daoInsertAfter", array(\'dao\'=>$this->_daoSelector, \'record\'=>$record));';
        }

        $src[] = '    return $result;';
        $src[] = '}';

        
        // Add a callback for CLOB
        $src[] = "\n";
        $src[] = '    protected function finishInitResultSet ($rs) {';
        $src[] = '        parent::finishInitResultSet ($rs);';
        $src[] = '        $rs->setFetchMode(PDO::FETCH_OBJ);';
        $src[] = '        $rs->addModifier(array($this, \'handleLOB\'));';
        $src[] = '    }'."\n";
        
        // And the callback to handle CLOB
        $src[] = '    public function handleLOB ($record, $resultset) {';
        
        $src[] = '        foreach (get_object_vars ($record) as $ski => $value) {';
        $src[] = '            if (is_resource ($value)) {';
        $src[] = '                $blob_content = stream_get_contents ($value);';
        $src[] = '                fclose ($value);';
        $src[] = '                $record->$ski = $blob_content;';
        $src[] = '            }';
        $src[] = '        }';

        $src [] = '    }';
        
        return implode("\n",$src);
    }

    protected function buildUpdateMethod($pkFields) {
        $src = array();

        $src[] = 'public function update ($record){';
        list($fields, $values) = $this->_prepareValues($this->_getPropertiesBy('PrimaryFieldsExcludePk'),'updatePattern', 'record->');
        $fields_obj = $this->_getPropertiesBy('PrimaryFieldsExcludePk');
        
        if(count($fields)){

            if($this->_dataParser->hasEvent('updatebefore') || $this->_dataParser->hasEvent('update')){
                $src[] = '   jEvent::notify("daoUpdateBefore", array(\'dao\'=>$this->_daoSelector, \'record\'=>$record));';
            }

            $src[] = '   $query = \'UPDATE '.$this->tableRealNameEsc.' SET ';
            
            foreach ($fields_obj as $field) {

                if (strlen ($field->updatePattern) && ($field->updatePattern != '%s')) {
                    $frags[] = str_replace ("'", "\\'", sprintf($field->updatePattern,  ':' . $field->fieldName)); // Eescape quotes as needed
                    $binds[] = $field;
                } else {
                    $frags[] = $field->fieldName .'=:' . $field->fieldName;
                    $binds[] = $field;
                }
            }
            $src[] = implode (',', $frags);
            
            // Create a where
            $where = array ();
            foreach ($pkFields as $field) {
                $where[] = $field->fieldName .'=:' . $field->fieldName;
                $binds[] = $field;
            }
            $src[] = 'WHERE ' . implode ('AND', $where);
            $src[] = "';";

            $src[] = '    $sth = $this->_conn->prepare ($query);';
            // Bind the variables, at last
            foreach ($binds as $bind)
                $src[] = $this->_codeBindParam ($bind);

            $src[] = '    $result = $sth->execute ();';
            

            // we generate a SELECT query to update field on the record object, which are autoincrement or calculated
            $fields = $this->_getPropertiesBy('FieldToUpdateOnUpdate');
            if (count($fields)) {
                $result = array();
                foreach ($fields as $id=>$prop){
                    $result[]= $this->buildSelectPattern($prop->selectPattern, '', $prop->fieldName, $prop->name);
                }

                $sql = 'SELECT '.(implode (', ',$result)). ' FROM '.$this->tableRealNameEsc.' WHERE ';
                $sql.= $this->buildSimpleConditions($pkFields, 'record->', false);

                $src[] = '  $query =\''.$sql.'\';';
                $src[] = '  $rs  =  $this->_conn->query ($query, jDbConnection::FETCH_INTO, $record);';
                $src[] = '  $record =  $rs->fetch ();';
            }
                
            if($this->_dataParser->hasEvent('updateafter') || $this->_dataParser->hasEvent('update'))
                $src[] = '   jEvent::notify("daoUpdateAfter", array(\'dao\'=>$this->_daoSelector, \'record\'=>$record));';

            $src[] = '   return $result;';
        }else{
            //the dao is mapped on a table which contains only primary key : update is impossible
            // so we will generate an error on update
            $src[] = "     throw new jException('jelix~dao.error.update.impossible',array('".$this->_daoId."','".$this->_daoPath."'));";
        }

        $src[] = ' }';//ends the update function

        return implode("\n",$src);
    }
    
    private function _codeBindParam ($bind) {
        switch ($bind->datatype) {
            case 'clob':
                $src = '    $sth->bindParam (\':' . $bind->fieldName . '\', $record->' . $bind->name . ', PDO::PARAM_STR, strlen ($record->' . $bind->name . '));';
                break;
            case 'varchar':
            case 'varchar2':
            case 'nvarchar2':
            case 'character':
            case 'character varying':
            case 'char':
            case 'nchar':
            case 'name':
            case 'longvarchar':
            case 'string':
                $src = '    $sth->bindValue (\':' . $bind->fieldName . '\', $record->' . $bind->name . ', (is_null($record->' . $bind->name . ') ? PDO::PARAM_NULL : PDO::PARAM_STR));';
                break;
            case 'int':
            case 'integer':
            case 'tinyint':
            case 'smallint':
            case 'mediumint':
            case 'bigint':
                 $src = '    $sth->bindParam (\':' . $bind->fieldName . '\', $record->' . $bind->name . ', (is_null($record->' . $bind->name . ') ? PDO::PARAM_NULL : PDO::PARAM_INT));';
                 break;
            default:
                $src = '    $sth->bindValue (\':' . $bind->fieldName . '\', $record->' . $bind->name . ');';
        }
        return $src;
    }
}
