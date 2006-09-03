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
class jFormsDataContainer {
   public $datas = array();
   public $internalId;
   public $userId;
   public $formSelector;

   function __construct($formSelector,$internalId, $userId){
      $this->internalId = $internalId;
      $this->userId = $userId;
      $this->formSelector =$formSelector;
   }

   function unsetData($name){
      unset($this->datas[$name]);
   }

   function clear(){
      $this->datas=array();
   }

}

?>