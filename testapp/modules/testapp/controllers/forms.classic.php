<?php
/**
* @package     testapp
* @subpackage  testapp module
* @version     $Id$
* @author      Jouanneau Laurent
* @contributor
* @copyright   2005-2006 Jouanneau laurent
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

class CTForms extends jController {

  function newform(){
      $form = jForm::create('sample');

      return $this->getResponse();
   }



   function showform(){

      /*$rep = $this->getResponse('');
      $rep->title = '';
      $rep->bodyTpl = '';
      */
      return $rep;
   }

   function resultform(){

      return $rep;


   }
}

?>