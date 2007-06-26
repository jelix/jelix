<?php
/**
* @package     jelix
* @subpackage  forms
* @author      Laurent Jouanneau
* @contributor
* @copyright   2006 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*/

/**
 * EXPERIMENTAL
 * @package     jelix
 * @subpackage  forms
 * @experimental
 */
interface jIFormDatasource {
   public function getDatas();

}

/**
 * EXPERIMENTAL
 * @package     jelix
 * @subpackage  forms
 * @experimental
 */
class jFormStaticDatasource implements jIFormDatasource {
   public $datas = array();
   public function getDatas(){
      return $this->datas;
   }
}


/**
 * EXPERIMENTAL
 * @package     jelix
 * @subpackage  forms
 * @experimental
 */
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
      $found = $dao->{$this->daomethod}();
      $result=array();
      foreach($found as $obj){
          $result[$obj->{$this->daovalue}] = $obj->{$this->daolabel};
      }
      return $result;
   }
}

?>