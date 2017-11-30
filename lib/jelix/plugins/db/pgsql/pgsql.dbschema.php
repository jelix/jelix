<?php
/**
* @package    jelix
* @subpackage db
* @author     Laurent Jouanneau
* @copyright  2010-2017 Laurent Jouanneau
*
* @link        http://www.jelix.org
* @licence     http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*/

/**
 * 
 * @package    jelix
 * @subpackage db_driver
 * @notimplemented
 */
class pgsqlDbTable extends jDbTable {

    public function getPrimaryKey() {
        if ($this->primaryKey === null) {
            $this->_loadColumns();
        }
        return $this->primaryKey;
    }

    protected function _loadColumns(){
        $conn = $this->schema->getConn();
        $tools = $conn->tools();

        $sql = "SELECT a.attname, a.attnotnull, a.atthasdef, a.attlen, a.atttypmod,
                FORMAT_TYPE(a.atttypid, a.atttypmod) AS type,
                d.adsrc, co.contype AS primary
            FROM pg_attribute AS a
            JOIN pg_class AS c ON a.attrelid = c.oid
            LEFT OUTER JOIN pg_constraint AS co
                ON (co.conrelid = c.oid AND a.attnum = ANY(co.conkey) AND co.contype = 'p')
            LEFT OUTER JOIN pg_attrdef AS d
                ON (d.adrelid = c.oid AND d.adnum = a.attnum)
            WHERE a.attnum > 0 AND c.relname = ".$conn->quote($this->name) .
            " ORDER BY a.attnum";

        $rs = $conn->query($sql);
        while ($line = $rs->fetch()) {
            //var_export($line);
            $name = $line->attname;
            list($type, $length, $precision, $scale) = $tools->parseSQLType($line->type);
            $notNull = (bool) ($line->attnotnull);
            $default = $line->adsrc;
            $hasDefault = ($line->atthasdef == 't');

            $col = new jDbColumn($name, $type, $length, $hasDefault, $default, $notNull);

            $typeinfo = $tools->getTypeInfo($type);
            if (preg_match('/^nextval\(([^\)]*)\)$/', $line->adsrc, $m)) {
                $col->autoIncrement = true;
                $col->default = '';
                if ($m[1]) {
                    $pos = strpos($m[1], '::');
                    if ($pos !== false) {
                        $col->sequence = trim(substr($m[1], 0, $pos), "'");
                    }
                    else {
                        $col->sequence = $m[1];
                    }
                }
            }
            else if ($typeinfo[6]) {
                $col->autoIncrement = true;
                $col->default = '';
            }

            //$col->unifiedType = $typeinfo[1];
            $col->maxValue = $typeinfo[3];
            $col->minValue = $typeinfo[2];
            $col->maxLength = $typeinfo[5];
            $col->minLength = $typeinfo[4];
            $col->precision = $precision;
            $col->scale = $scale;
            if($line->attlen == -1 && $line->atttypmod != -1) {
                $col->length = $line->atttypmod - 4;
            }
            if ($col->length !=0) {
                $col->maxLength = $col->length;
            }

            if ($line->primary) {
                if (!$this->primaryKey)
                    $this->primaryKey = new jDbPrimaryKey($name);
                else
                    $this->primaryKey->columns[] = $name;
            }

            $this->columns[$name] = $col;
        }
        if ($this->primaryKey === null) {
            $this->primaryKey = false;
        }
    }

    protected function _alterColumn(jDbColumn $old, jDbColumn $new){
        throw new Exception("_alterColumn Not Implemented");
    }

    protected function _addColumn(jDbColumn $new){
        throw new Exception("_addColumn Not Implemented");
    }

    protected function _loadIndexesAndKeys(){
        throw new Exception("_loadIndexesAndKeys Not Implemented");
    }

    protected function _createIndex(jDbIndex $index){
        throw new Exception("_createIndex Not Implemented");
    }

    protected function _dropIndex(jDbIndex $index){
        throw new Exception("_dropIndex Not Implemented");
    }

    protected function _loadReferences(){
        throw new Exception("_loadReferences Not Implemented");
    }

    protected function _createReference(jDbReference $ref){
        throw new Exception("_createReference Not Implemented");
    }

    protected function _dropReference(jDbReference $ref){
        throw new Exception("_dropReference Not Implemented");
    }
}

/**
 * 
 * @package    jelix
 * @subpackage db_driver
 */
class pgsqlDbSchema extends jDbSchema {

    /**
     *
     */
    function _createTable($name, $columns, $primaryKeys, $attributes=array()) {
        throw new Exception("Not Implemented");
    }

    /**
     * @return jDbTable
     */
    function getTable($name) {
        return  new pgsqlDbTable($this->getConn()->prefixTable($name), $this);
    }

    protected function _getTables () {
        $results = array ();
        $sql = "SELECT tablename FROM pg_tables
                  WHERE schemaname NOT IN ('pg_catalog', 'information_schema')
                  ORDER BY tablename";
        $rs = $this->getConn()->query ($sql);
        while ($line = $rs->fetch()){
            $results[$line->tablename] = new pgsqlDbTable($line->tablename, $this);
        }
        return $results;
    }
}
