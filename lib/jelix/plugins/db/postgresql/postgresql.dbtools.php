<?php
/**
* @package    jelix
* @subpackage db_driver
* @author     Croes Gérald, Ferlet Patrice, Laurent Jouanneau
* @contributor Laurent Jouanneau
* @contributor Nicolas Jeudy (patch ticket #99)
* @copyright  2001-2005 CopixTeam, 2005-2006 Laurent Jouanneau
* This class was get originally from the Copix project (CopixDBToolsPostgreSQL, Copix 2.3dev20050901, http://www.copix.org)
* Few lines of code are still copyrighted 2001-2005 CopixTeam (LGPL licence).
* Initial authors of this Copix class are Gerald Croes, Ferlet Patrice  and Laurent Jouanneau,
* and this class was adapted for Jelix by Laurent Jouanneau
*
* @link        http://www.jelix.org
* @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*/

/**
 *
 * @package    jelix
 * @subpackage db_driver
 */
class postgresqlDbTools extends jDbTools {

   /*
   * retourne la liste des tables
   * @return   array    $tab[] = $nomDeTable
   */
   protected function _getTableList (){
      $results = array ();
      $sql = "SELECT tablename FROM pg_tables WHERE tablename NOT LIKE 'pg_%' ORDER BY tablename";
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
        $sql_get_fields = "SELECT
        a.attname as Field, t.typname as type, a.attlen as length, a.atttypmod,
        case when a.attnotnull  then 1 else 0 end as notnull,
        a.atthasdef,
        (SELECT adsrc FROM pg_attrdef adef WHERE a.attrelid=adef.adrelid AND a.attnum=adef.adnum) AS adsrc
        FROM
            pg_attribute a,
            pg_class c,
            pg_type t
        WHERE
          c.relname = '{$tableName}' AND a.attnum > 0 AND a.attrelid = c.oid AND a.atttypid = t.oid
        ORDER BY a.attnum";

        $rs = $this->_connector->query ($sql_get_fields);
        $toReturn=array();
        while ($result_line = $rs->fetch ()){
            $field = new jDbFieldProperties();
            if(preg_match('/nextval\(\'(.*?)\.'.$tableName.'_'.$result_line->field.'_seq\'::text\)/',
            $result_line->adsrc)){
                $field->auto_increment=true;
            }

            $field->notnull = ($result_line->notnull==1)  ? true:false;
            $field->type = preg_replace('/(\D*)\d*/','\\1',$result_line->type);
            if($result_line->length<0)
                $field->length=null;
            else
                $field->length=$result_line->length;
            $field->name = $result_line->field;
             $field->primary = ($result_line->atthasdef == 't');

            $toReturn[$result_line->field]=$field;
        }

        return $toReturn;
    }
}
?>
