<?php
/**
* @package     testapp
* @subpackage  testapp module
* @author      Laurent Jouanneau
* @contributor
* @copyright   2005-2016 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/
use Jelix\Forms\Forms;

class formsCtrl extends jController {

    function listform(){
        $rep = $this->getResponse('html');
        $rep->title = 'Instances list of forms';
        $rep->body->assign('page_title','Instances list of forms');

        $tpl = new jTpl();
        if(isset($_SESSION['testapp_jforms'])) {
            $list = array_map(function($key) {
                return Forms::get('sample2', $key)->getContainer();
            },$_SESSION['testapp_jforms']);
            $tpl->assign('liste', $list);
        } else {
            $tpl->assign('liste', array());
        }
        $rep->body->assign('MAIN',$tpl->fetch('forms_liste'));
        return $rep;
    }

    /**
     * creation d'un nouveau formulaire vierge
     * et redirection vers le formulaire html
     */
    function newform(){
        // création d'un formulaire vierge
        $form = Forms::create('sample2');
        $rep= $this->getResponse("redirect");
        $rep->action="forms:showform";
        $rep->params['id']= $form->id();
        return $rep;
    }

    /**
     * creation d'un formulaire avec des données initialisé à partir d'un enregistrement (factice)
     * et redirection vers le formulaire html
     */
    function edit(){
        $id = $this->param('id');
        $form = Forms::create('sample2', $this->param('id'));
        // remplissage du formulaire. Ici on le fait à la main, mais ça pourrait
        // être à partir d'un dao
        if($id == 1){
            $form->setData('nom','Dupont');
            $form->setData('prenom','Laurent');
        }elseif($id == 2){
            $form->setData('nom','Durant');
            $form->setData('prenom','George');
        }else{
            $form->setData('nom','inconnu');
            $form->setData('prenom','inconnu');
        }
    
        // redirection vers le formulaire
        $rep= $this->getResponse("redirect");
        $rep->action="forms:showform";
        $rep->params['id']= $form->id(); // ou $id, c'est pareil
        return $rep;
    }



    /**
     * affichage du formulaire html
     */
    function showform(){
        $rep = $this->getResponse('html');
        $rep->title = 'Form editing';
        $rep->body->assign('page_title', 'forms');

        // Get the form which have the given id
        $form = Forms::get('sample2', $this->param('id'));
        if ($form) {
            $tpl = new jTpl();
            $tpl->assign('form', $form->getContainer());
            $tpl->assign('id', $form->id());
            if ($form->securityLevel != \Jelix\Forms\FormInstance::SECURITY_LOW)
              $tpl->assign('token', $form->createNewToken());
            else
              $tpl->assign('token','');
            $rep->body->assign('MAIN',$tpl->fetch('forms_edit'));
        }else{
            $rep->body->assign('MAIN','<p>bad id</p>' );
        }

        return $rep;
    }

    function save(){
        if(!isset($_SESSION['testapp_jforms'])) {
            $_SESSION['testapp_jforms'] = array();
        }

        $id = $this->param('id');
        $newid = $this->param('newid');

        // retrieve the form object and fill it with values coming from the request
        $form = Forms::fill('sample2', $id);

        if($id != $newid){
            $form2 = Forms::create('sample2', $newid);
            $form2->getContainer()->data = $form->getContainer()->data;
            $_SESSION['testapp_jforms'][] = $newid;
        }
        
        if ($id == '0') {
           Forms::destroy('sample2', $id);
            $_SESSION['testapp_jforms'] = array_filter($_SESSION['testapp_jforms'],
                function($val) use ($id) {
                    return $val != $id;
                });
        }

        $rep= $this->getResponse("redirect");
        $rep->action="forms:listform";
        return $rep;
    }

    function view(){
        $form = Forms::get('sample2',$this->param('id'));
        $rep = $this->getResponse('html');
        $rep->title = 'Content of a form';
        $rep->body->assign('page_title','forms');

        if($form){
            $tpl = new jTpl();
            $tpl->assign('nom', $form->getData('nom'));
            $tpl->assign('prenom', $form->getData('prenom'));
            $tpl->assign('id', $this->param('id'));
            $rep->body->assign('MAIN',$tpl->fetch('forms_view'));
        }else{
            $rep->body->assign('MAIN','<p>The form doesn\'t exist.</p>');
        }
        return $rep;
    }

   function destroy(){
       $id = $this->param('id');
       Forms::destroy('sample2',$id);
       $_SESSION['testapp_jforms'] = array_filter($_SESSION['testapp_jforms'],
           function($val) use ($id) {
               return $val != $id;
           });

       $rep= $this->getResponse("redirect");
       $rep->action="forms:listform";
       return $rep;
   }

}

