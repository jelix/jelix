<?php
/**
* @package    jelix
* @subpackage db_driver
* @author     Gérald Croes, Laurent Jouanneau
* @contributor Laurent Jouanneau
* @copyright  2001-2005 CopixTeam, 2005-2010 Laurent Jouanneau
* This class was get originally from the Copix project (CopixDbResultsetMysql, Copix 2.3dev20050901, http://www.copix.org)
* Few lines of code are still copyrighted 2001-2005 CopixTeam (LGPL licence).
* Initial authors of this Copix class are Gerald Croes and Laurent Jouanneau,
* and this class was adapted/improved for Jelix by Laurent Jouanneau
*
* @link      http://www.jelix.org
* @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*/

/**
 * Couche d'encapsulation des resultset mysql.
 * @package    jelix
 * @subpackage db_driver
 */
class mysqliDbResultSet extends jDbResultSet {

    protected function  _fetch () {
        if($this->_fetchMode == jDbConnection::FETCH_CLASS) {
            if ($this->_fetchModeCtoArgs)
                $ret =  $this->_idResult->fetch_object($this->_fetchModeParam, $this->_fetchModeCtoArgs);
            else
                $ret =  $this->_idResult->fetch_object($this->_fetchModeParam);
        }else{
            $ret =  $this->_idResult->fetch_object();
        }
        return $ret;
    }

    protected function _free (){
        return $this->_idResult->close();
    }

    protected function _rewind (){
        return @$this->_idResult->data_seek(0);
    }

    public function rowCount(){
        return $this->_idResult->num_rows;
    }


    public function bindColumn($column, &$param , $type=null )
      {throw new jException('jelix~db.error.feature.unsupported', array('mysql','bindColumn')); }
    public function bindValue($parameter, $value, $data_type)
      {throw new jException('jelix~db.error.feature.unsupported', array('mysql','bindValue')); }

    public function columnCount(){ 
        return $this->_idResult->field_count; 
    }

    public function bindParam($parameter, &$variable , $data_type =null, $length=null,  $driver_options=null){

        throw new jException('jelix~db.error.feature.unsupported', array('mysql','bindParam')); 

        /* TODO
        Incohérence au niveau parametres, comment procéder ?
        mysqli_stmt::bind_param ( string $types , mixed &$var1 [, mixed &$... ] )

        $this->_idResult->bind_param();
        */
    }

    public function execute($parameters=null){
         $this->_idResult = $this->_idResult->execute();
         return true;
    }
}

