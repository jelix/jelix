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

/**
 *
 * @package     jelix
 * @subpackage  forms
 */
interface jIFormDatasource {
   public function getDatas();

}

/**
 *
 * @package     jelix
 * @subpackage  forms
 */
class jFormStaticDatasource implements jIFormDatasource {
   public $datas = array();
   public function getDatas(){
      return $this->datas;
   }
}


/**
 *
 * @package     jelix
 * @subpackage  forms
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
      $found = $dao->${$this->daomethod}();
      $result=array();
      foreach($found as $obj){
          $result[$obj->${$this->daovalue}] = $obj->${$this->daolabel}
      }
      return $result;
   }
}

?>