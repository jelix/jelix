<?php
/**
* @package     testapp
* @subpackage  testapp module
* @author      Laurent Jouanneau
* @contributor
* @copyright   2005-2007 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

class sampleFormCtrl extends jController {

  protected function prepareForm($form) {
    $form->deactivate('unwanted');
    $c = $form->getControl('objects_datasource');
    $c->datasource->data = array('ic'=>'ice cream', 'pot'=>'potatoes', 'bean'=>'beans');
    //$form->setReadOnly('listdep2');
  }

  function newform(){
      jForms::destroy('sample');
      // create a new form
      $form = jForms::create('sample');
      $this->prepareForm($form);
      $rep= $this->getResponse("redirect");
      $rep->action="sampleform:show";
      //$form->setReadOnly('conf');
      //$form->setData('conf', 'browser');
      return $rep;
  }

  function show(){
      // retrieve form data
      $form = jForms::get('sample');
      if($form == null){
          $form = jForms::create('sample');
          $form->deactivate('unwanted');
      }
      $this->prepareForm($form);

      $rep = $this->getResponse('html');
      $rep->title = 'Form editing';

      $tpl = new jTpl();
      $tpl->assign('form', $form);

      $tpl->assign('builder', $this->param('builder','html'));
      $tpl->assign('autocompleteurl', jUrl::get('testapp~sampleform:searchAutocomplete'));

      if($this->param('full')) 
          $rep->body->assign('MAIN',$tpl->fetch('sampleformfull'));
      else
          $rep->body->assign('MAIN',$tpl->fetch('sampleform'));
      $rep->body->assign('page_title','forms');

      return $rep;
   }

   function save(){
      // récuper le formulaire
      // et le rempli avec les données reçues de la requête
      $rep= $this->getResponse("redirect");
      
      
      $form = jForms::get('sample');
      $this->prepareForm($form);
      $form->initFromRequest();
      if($form->check())
          $rep->action="sampleform:ok";
      else
          $rep->action="sampleform:show";
      return $rep;
   }

   function ok(){
      $form = jForms::get('sample');

      $rep = $this->getResponse('html');
      $rep->title = 'Form editing';

      if($form){
        $this->prepareForm($form);
        $tpl = new jTpl();
        $tpl->assign('form', $form);
        $rep->body->assign('MAIN',$tpl->fetch('sampleformresult'));
      }else{
        $rep->body->assign('MAIN','<p>The form doesn\'t exist.</p>');
      }
      $rep->body->assign('page_title','formulaires');
      return $rep;
   }

   function destroy(){
      jForms::destroy('sample');
      $rep= $this->getResponse("redirect");
      $rep->action="sampleform:ok";
      return $rep;
   }

  function newajaxform(){
      jForms::destroy('sample');
      // création d'un formulaire vierge
      $form = jForms::create('sample');
      $this->prepareForm($form);
      $rep= $this->getResponse("html");
      $rep->title = 'show ajax form';
      $form->getBuilder('html')->outputMetaContent(null);
      $tpl = new jTpl();
      $rep->body->assign('MAIN', $tpl->fetch('sampleformcontainer'));
      $rep->body->assign('page_title','ajax forms');
      return $rep;
  }

  function showajaxform() {
      // retrieve form data
      $form = jForms::get('sample');
      if($form == null){
          $form = jForms::create('sample');
          $form->deactivate('unwanted');
      }
      $this->prepareForm($form);

      $rep = $this->getResponse('htmlfragment');
      $rep->tpl->assign('form', $form);
      $rep->tplname = 'sampleajaxform';
      return $rep;
  }
    function saveajax(){
        // récupérer le formulaire
        // et le rempli avec les données reçues de la requête
        /** @var jResponseFormJQJson $rep */
        $rep= $this->getResponse("formjq");

        $form = jForms::get('sample');
        $this->prepareForm($form);
        $form->initFromRequest();
        if ($form->check()) {
            $tpl = new jTpl();
            $tpl->assign('form', $form);
            $rep->setCustomData(array(
                'htmlContent' => $tpl->fetch('sampleformajaxresult', 'html')
            ));
        }

        $rep->setForm($form);
        return $rep;
    }

    /**
     * Use for the autocomplete feature on the inputautocompleteajax control
     * @return jResponseJson
     * @throws jException
     */
    function searchAutocomplete()
    {
        $rep = $this->getResponse('json');
        $term = $this->param('term');
        if (strlen($term) < 2) {
            $rep->data = array();
            return $rep;
        }

        $dao = jDao::get('towns');
        $list = $dao->searchTerm('%'.$term.'%');
        $towns = array();
        foreach ($list as $prop) {
            $towns[] = ['label' => $prop->name, 'id' => $prop->postalcode];
        }
        $rep->data = $towns;
        return $rep;
    }
}
