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

class jFormsDataContainer {
   private $datas = array();
   private $id;
   private $formSelector;

   function __construct($id, $formSelector){
      $this->id = $id;
      $this->formSelector =$formSelector;
   }

   function getId(){
      return $id;
   }
   
   function getFormSelector(){
      return $id;
   }
   
   function setDatas($datas){
      $this->datas = $datas;
   }

   function getDatas($datas){
      return $this->datas;
   }
   function get($name){
      return $this->datas[$name];
   }
   function set($name,$value){
      $this->datas[$name]=$value;
   }

   function unset($name){
      unset($this->datas[$name]);
   }

   function clear(){
      $this->datas=array();
   }


}

?>