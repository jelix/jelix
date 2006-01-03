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

class jDbPDOResultSet extends PDOStatement {

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
         $this->setFetchMode( PDO::FETCH_CLASS, $object );
         return $this->fetch (PDO::FETCH_CLASS);
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
       parent::_construct($profil['dsn'], $profil['user'], $profil['password'], $prof);
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