<?php
/**
* @package    jelix
* @subpackage db
* @version    $Id:$
* @author     Laurent Jouanneau
* @contributor
* @copyright  2005-2006 Laurent Jouanneau
* @link      http://www.jelix.org
* @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*
*/

// les noms des constantes de PDO ont changés entre php 5.0 et 5.1
// on utilise alors les notres
define('JPDO_FETCH_OBJ',5); // PDO::FETCH_OBJ
define('JPDO_FETCH_ORI_NEXT',0); // PDO::FETCH_ORI_NEXT
define('JPDO_FETCH_CLASS',8); // PDO::FETCH_CLASS
define('JPDO_ATTR_STATEMENT_CLASS',13); //PDO::ATTR_STATEMENT_CLASS

class jDbPDOResultSet extends PDOStatement {

   public function fetchAll ( $fetch_style = JPDO_FETCH_OBJ, $column_index=0 ){
      return parent::fetchAll( JPDO_FETCH_OBJ, $column_index);
   }

   public function fetch( $fetch_style= JPDO_FETCH_OBJ, $cur_or=JPDO_FETCH_ORI_NEXT, $cur_offset=0 ){
     return parent::fetch(JPDO_FETCH_OBJ,$cur_or,$cur_offset);
   }
    /**
    * recupere un enregistrement et rempli les propriétes d'un objet existant avec
    * les valeurs récupérées.
    * @param object/string  $object ou nom de la classe
    * @return  boolean  indique si il y a eu des resultats ou pas.
    */
   public function fetchInto ( $object){
      if(is_object($object)){
        if ($result = $this->fetch ()){
                foreach (get_object_vars ($result) as $k=>$value){
                    $object->$k = $value;
                }
                return $object;
            }else{
                return false;
            }

      }else{
         $this->setFetchMode( JPDO_FETCH_CLASS, $object );
         return $this->fetch (JPDO_FETCH_CLASS);
      }
   }

}



class jDbPDOConnection extends PDO {

    /**
    * the profil the connection is using
    * @var array
    */
    public $profil;
    public $dbms;

    /**
    * @constructor
    */
    function __construct($profil){
       $this->profil = $profil;
       $this->dbms=substr($profil['dsn'],0,strpos(':',$profil['dsn']));
       $prof=$profil;
       unset($prof['dsn']);
       unset($prof['user']);
       unset($prof['password']);
       unset($prof['driver']);
       parent::__construct($profil['dsn'], $profil['user'], $profil['password'], $prof);
       $this->setAttribute(PDO::ATTR_STATEMENT_CLASS, array('jDbPDOResultSet'));
    }


    public function limitQuery ($queryString, $limitOffset = null, $limitCount = null){
        if ($limitOffset !== null && $limitCount !== null){
           if($this->dbms == 'mysql'){
               $queryString.= ' LIMIT '.intval($limitOffset).','. intval($limitCount);
           }elseif($this->dbms == 'pgsql'){
               $queryString.= ' LIMIT '.intval($limitCount).' OFFSET '.intval($limitOffset);
           }
        }
        $result = $this->query ($queryString);
        return $result;
    }



    /**
    * sets the autocommit state
    * @param boolean state the status of autocommit
    */
    public function setAutoCommit($state=true){
        $this->setAttribute(PDO::ATTR_AUTOCOMMIT,$state);
    }


    public function lastIdInTable($fieldName, $tableName){
      $rs = $this->query ('SELECT MAX('.$fieldName.') as ID FROM '.$tableName);
      if (($rs !== null) && $r = $rs->fetch ()){
         return $r->ID;
      }
      return 0;
    }

}
?>