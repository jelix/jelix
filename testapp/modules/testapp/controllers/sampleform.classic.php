<?php
/**
* @package     testapp
* @subpackage  testapp module
* @author      Jouanneau Laurent
* @contributor
* @copyright   2005-2007 Jouanneau laurent
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

class sampleFormCtrl extends jController {

  function newform(){
      // création d'un formulaire vierge
      $form = jForms::create('sample');
      $rep= $this->getResponse("redirect");
      $rep->action="sampleform:show";
      return $rep;
  }

  function show(){
      // recupère les données du formulaire
      $form = jForms::get('sample');
      if($form == null){
          $form = jForms::create('sample');
      }
      $rep = $this->getResponse('html');
      $rep->title = 'Edition d\'un formulaire';

      $tpl = new jTpl();
      $tpl->assign('form', $form);
      $rep->body->assign('MAIN',$tpl->fetch('sampleform'));
      $rep->body->assign('page_title','formulaires');

      return $rep;
   }

   function save(){
      // récuper le formulaire
      // et le rempli avec les données reçues de la requête
      $rep= $this->getResponse("redirect");

      $form = jForms::fill('sample');
      if($form->check())
          $rep->action="sampleform:ok";
      else
          $rep->action="sampleform:show";
      return $rep;
   }

   function ok(){
      $form = jForms::get('sample');
      $rep = $this->getResponse('html');
      $rep->title = 'Edition d\'un formulaire';

      if($form){
        $tpl = new jTpl();
        $tpl->assign('form', $form);
        $rep->body->assign('MAIN',$tpl->fetch('sampleformresult'));
      }else{
        $rep->body->assign('MAIN','<p>le formulaire n\'existe pas</p>');
      }
      $rep->body->assign('page_title','formulaires');
      return $rep;
   }

   function destroy(){
      jForms::destroy('sample');
      $rep= $this->getResponse("redirect");
      $rep->action="sampleform:status";
      return $rep;
   }

   function status(){
      $rep = $this->getResponse('html');
      $rep->title = 'Etat des données formulaire';

      $rep->body->assign('page_title','formulaires');

      $content='<h1>Données en session des formulaires</h1>';
      if(isset($_SESSION['JFORMS'])){
          $content.='<pre>'.htmlspecialchars(var_export($_SESSION['JFORMS'],true)).'</pre>';
      }else{
          $content.='<p>Il n\'y a pas de formulaires...</p>';
      }
      $rep->body->assign('MAIN',$content);
      return $rep;
   }

}

?>