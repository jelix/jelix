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
      $rep= $this->getResponse("redirect");
      $rep->action="forms_show";
      return $rep;
   }


  function edit(){
     $form = jForm::create('sample', $this->param('id'));

     $rep= $this->getResponse("redirect");
     $rep->action="forms_show";
      return $rep;
  }

   function show(){
      $form = jForms::get('sample',$this->param('id'));
      $rep = $this->getResponse('html');
      $rep->title = 'Edition d\'un formulaire';
      $rep->body->assign('MAIN','<p>Ici sera le formulaire</p>');

      return $rep;
   }

   function save(){
      $form = jForms::getFromRequest('sel~form',$this->param('id'));

      $rep= $this->getResponse("redirect");
      $rep->action="forms_ok";
      return $rep;
   }

   function ok(){
      $rep = $this->getResponse('html');
      $rep->title = 'Edition d\'un formulaire';
      $rep->body->assign('MAIN','<p>Fin du formulaire</p>');
      return $rep;
   }

}

?>