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
      // création d'un formulaire vierge
      $form = jForms::create('sample');
      $rep= $this->getResponse("redirect");
      $rep->action="forms_show";
      $rep->params['fid']= $form->id();
      return $rep;
  }


  function edit(){
     $form = jForms::create('sample', $this->param('newsid'));
     // remplissage...
     $rep= $this->getResponse("redirect");
     $rep->action="forms_show";
     $rep->params['id']= $form->id();
     return $rep;
  }

  function show(){
      // recupère les données du formulaire dont l'id est dans le paramètre id
      $form = jForms::get('sample','fid');

      $rep = $this->getResponse('html');
      $rep->title = 'Edition d\'un formulaire';

      $tpl = new jTpl();
      $tpl->assign('form', $form->getContainer());
      $rep->body->assign('MAIN',$tpl->fetch('sampleform'));
      $rep->body->assign('page_title','formulaires');

      return $rep;
   }

   function save(){
      // récuper le formulaire dont l'id est dans le paramètre id
      // et le rempli avec les données reçues de la requête
      $form = jForms::fill('sample','fid');

      $rep= $this->getResponse("redirect");
      $rep->action="forms_ok";
      return $rep;
   }

   function ok(){
      $form = jForms::get('sample','id');
      $datas=$form->getContainer()->datas;

      $rep = $this->getResponse('html');
      $rep->title = 'Edition d\'un formulaire';
      $tpl = new jTpl();
      $tpl->assign('nom', $datas['nom']);
      $tpl->assign('prenom', $datas['prenom']);

      $rep->body->assign('page_title','formulaires');
      $rep->body->assign('MAIN',$tpl->fetch('sampleformresult'));
      return $rep;
   }

}

?>