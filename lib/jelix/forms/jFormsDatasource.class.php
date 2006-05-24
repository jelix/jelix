<?php
/**
* @package     jelix
* @subpackage  forms
* @version     $Id:$
* @author      Laurent Jouanneau
* @contributor
* @copyright   2006 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*/

interface jIFormDatasource {
   public function getDatas();

}

class jFormStaticDatasource implements jIFormDatasource {
   public $datas = array();
   public function getDatas(){
      return $this->datas;
   }
}


class jFormDaoDatasource implements jIFormDatasource {

   protected $daoselector;
   protected $daomethod;
   protected $daolabel;
   protected $daovalue;

   function __construct ($daoselector ,$daomethod , $daolabel, $daovalue){
        $this->daoselector  = $daoselector;
        $this->daomethod = $daomethod ;
        $this->daolabel = $daolabel;
        $this->daovalue = $daovalue;
   }

   public function getDatas(){
      $dao = jDao::get($this->daoselector);
      $found = $dao->${$this->daomethod}();
      $result=array();
      foreach($found as $obj){
          $result[$obj->${$this->daovalue}] = $obj->${$this->daolabel}
      }
      return $result;
   }
}

?>