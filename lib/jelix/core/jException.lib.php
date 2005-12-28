<?php
/**
* @package    jelix
* @subpackage core
* @version    $Id$
* @author     Jouanneau Laurent
* @contributor
* @copyright  2005-2006 Jouanneau laurent
* @link        http://www.jelix.org
* @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/


function jExceptionHandler($exception){
    trigger_error("Exception", E_USER_ERROR);

}



class jException extends Exception {
   public $localeParams = array();

   public function __construct($localekey, $localeParams=array(), $code = 0) {
       parent::__construct($localekey, $code);
       $this->localeParams=$localeParams;

   }

   public function __toString() {
      try{
         return jLocale::get($this->message, $this->localeParams);
      }catch(Exception $e){
         return $this->message;
      }
   }

   public function getLocaleMessage(){
      return jLocale::get($this->message, $this->localeParams);
   }

}



?>