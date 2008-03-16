<?php
/**
* @package    jelix
* @subpackage db_driver
* @author     Laurent Jouanneau
* @contributor Laurent Jouanneau
* @contributor Nicolas Jeudy (patch ticket #99)
* @copyright  2005-2007 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*/

/**
 *
 * @package    jelix
 * @subpackage db_driver
 */
class pgsqlDbTools extends jDbTools {

   /*
   * retourne la liste des tables
   * @return   array    $tab[] = $nomDeTable
   */
   protected function _getTableList (){
      $results = array ();
      $sql = "SELECT tablename FROM pg_tables WHERE schemaname NOT IN ('pg_catalog', 'information_schema') ORDER BY tablename";
      $rs = $this->_connector->query ($sql);
      while ($line = $rs->fetch()){
         $results[] = $line->tablename;
      }
      return $results;
   }
    /**
    * récupère la liste des champs pour une base donnée.
    * @return    array    $tab[NomDuChamp] = obj avec prop (tye, length, lengthVar, notnull)
    */
    protected function _getFieldList ($tableName){
        $results = array ();
        
        // get table informations
        $sql ='SELECT oid, relhaspkey, relhasindex FROM pg_class WHERE relname = \''.$tableName.'\'';
        $rs = $this->_connector->query ($sql);
        if (! ($table = $rs->fetch())) {
            throw new Exception('dbtools, pgsql: unknow table');
        }

        $pkeys = array();
        // get primary keys informations
        if ($table->relhaspkey == 't') {
            $sql = 'SELECT indkey FROM pg_index WHERE indrelid = '.$table->oid.' and indisprimary = true';
            $rs = $this->_connector->query ($sql);
            $pkeys = preg_split("/[\s]+/", $rs->fetch()->indkey);
        }

        // get field informations
        $sql_get_fields = "SELECT t.typname, a.attname, a.attnotnull, a.attnum, a.attlen, a.atttypmod,
        a.atthasdef, d.adsrc
        FROM pg_type t, pg_attribute a LEFT JOIN pg_attrdef d ON (d.adrelid=a.attrelid AND d.adnum=a.attnum)
        WHERE
          a.attnum > 0 AND a.attrelid = ".$table->oid." AND a.atttypid = t.oid
        ORDER BY a.attnum";

        $toReturn=array();
        $rs = $this->_connector->query ($sql_get_fields);
        while ($line = $rs->fetch ()){
            $field = new jDbFieldProperties();
            $field->name = $line->attname;
            $field->type = preg_replace('/(\D*)\d*/','\\1',$line->typname);
            $field->notNull = ($line->attnotnull=='t');
            $field->hasDefault = ($line->atthasdef == 't');
            $field->default = $line->adsrc;

            if(preg_match('/^nextval\(.*\)$/', $line->adsrc)){
                $field->autoIncrement=true;
                $field->default = '';
            }

            if(in_array($line->attnum, $pkeys))
                $field->primary = true;

            if($line->attlen == -1 && $line->atttypmod != -1)
                $field->length = $line->atttypmod - 4;

            $toReturn[$line->attname]=$field;
        }

        return $toReturn;
    }

    public function execSQLScript ($file) {
        $sqlQueries=file_get_contents($file);
        $this->_connector->query ($sqlQueries);
    }
}
?>
