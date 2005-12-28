<?php
/**
* @package    jelix
* @subpackage db
* @version    $Id:$
* @author      Laurent Jouanneau
* @contributor
* @copyright  2005-2006 Laurent Jouanneau
* @link      http://www.jelix.org
* @licence    http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*/

abstract class jDbResultSet {

	protected $_idResult=null;

	function __construct (  $idResult){
		$this->_idResult = $idResult;
	}

    function __destruct(){
        if ($this->_idResult){
			$this->_free ();
			$this->_idResult = null;
		}
    }

  /**
    * fetch et renvoi les resultats sous forme d'un objet
    * @return object l'objet contenant les champs récupérés, ou false si le curseur est à la fin
    */
	public function fetch(){
		$result = $this->_fetch ();
		return $result;
	}


    public function fetchAll(){
        $result=array();
        while($res =  $this->_fetch ()){
            $result[] = $res;
        }
        return $result;
    }

	/**
    * recupere un enregistrement et rempli les propriétes d'un objet existant avec
    * les valeurs récupérées.
    * @param object/string  $object ou nom de la classe
    * @return  boolean  indique si il y a eu des resultats ou pas.
    */
	public function fetchInto ( $object){

      if ($result = $this->_fetch ()){
         if(is_string($object)){
            $object = new $object();
         }
         foreach (get_object_vars ($result) as $k=>$value){
            $object->$k = $value;
         }
			return $object;
		}else{
			return false;
		}
	}

    public function getAttribute($attr){return null;}
    public function setAttribute($attr, $value){}

    abstract public function bindColumn($column, &$param , $type=null );
    abstract public function bindParam($parameter, &$variable , $data_type =null, $length=null,  $driver_options=null);
    abstract public function bindValue($parameter, $value, $data_type);
    //abstract public function closeCursor();
    abstract public function columnCount();
    //abstract public function errorCode();
    //abstract public function errorInfo();

    abstract public function execute($parameters=null);
    //abstract public function fetchColumn();


    //abstract public function getColumnMeta();
    //abstract public function nextRowset();
    abstract public function rowCount();

    abstract protected function _free ();
    abstract protected function _fetch ();

}
?>