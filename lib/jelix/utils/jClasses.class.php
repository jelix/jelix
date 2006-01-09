<?php
/**
* @package     jelix
* @subpackage  utils
* @version     $Id$
* @author      Jouanneau Laurent
* @contributor
* @copyright   2005-2006 Jouanneau laurent
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

class jClasses {

   static protected $_instances = array();

   private function __construct(){}

   static public function create($selector){

      $sel = new jSelectorClass($selector);
      if($sel->isValid()){
        $p = $sel->getPath();
        require_once ($p);
        $class = $sel->resource;
        return new $class ();
      }else{
         trigger_error(jLocale::get ('jelix~errors.selector.invalid', array ($selector)),E_USER_ERROR);
      }
   }

   static public function createInstance($selector){
        return self::create($selector);
   }

   static public function getService($selector){
       $sel = new jSelectorClass($selector);
       if(isset(self::$_instances[$sel->toString()])){
           return self::$_instances[$sel->toString()];
       }else{
           $o = self::create($selector);
           self::$_instances[$sel->toString()]=$o;
           return $o;
       }
   }
}

?>