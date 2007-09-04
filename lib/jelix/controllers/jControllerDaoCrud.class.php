<?php
/**
* @package    jelix
* @subpackage controllers
* @author     Laurent Jouanneau
* @contributor
* @copyright  2007 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*
*/

/**
 * a base class for crud controllers
 * @package    jelix
 * @subpackage controllers
 * @since 1.0b3
 */
class jControllerDaoCrud extends jController {

    /**
     * selector of the dao to use for the crud.
     * It should be filled by child controller.
     * @var string
     */
    protected $dao = '';
    
    /**
     * selector of the form to use to edit and display a record
     * It should be filled by child controller.
     * @var string
     */
    protected $form ='';

    /**
     * list of properties to show in the list page
     * if empty list (default), it shows all properties.
     * this property is only usefull when you use the default "list" template
     * @var array
     */
    protected $propertiesForList = array();

    /**
     * template to display the list of records
     * @var string
     */
    protected $listTemplate = 'jelix~crud_list';

    /**
     * template to display the form
     * @var string
     */
    protected $editTemplate = 'jelix~crud_edit';

    /**
     * template to display a record
     * @var string
     */
    protected $viewTemplate = 'jelix~crud_view';

    /**
     * number of record to display in the list page
     * @var integer
     */
    protected $listPageSize = 20;

    /**
     * the template variable name to display a CRUD content in the main template
     * of the html response
     * @var string
     */
    protected $templateAssign = 'MAIN';

    /**
     * name of the parameter which contains the page offset, for the index action
     * @var string
     */
    protected $offsetParameterName = 'offset';

    /**
     * id for the "pseudo" form used to show a record. You can change it if the default one corresponds to
     * a possible id in your dao.
     * @var string
     */
    protected $pseudoFormId = 'jelix_crud_roxor';


    protected $uploadsDirectory ='';

    /**
     * Returned a simple html response to display CRUD contents. You can override this
     * method to return a personnalized response
     * @return jResponseHtml the response
     */
    protected function _getResponse(){
        return $this->getResponse('html');
    }

    /**
     * returned the selector of the action corresponding of the given method of the current controller.
     * @param string $method  name of one of method of this controller
     * @return string an action selector
     */
    protected function _getAction($method){
        global $gJCoord;
        return $gJCoord->action->module.'~'.$gJCoord->action->controller.'_'.$method;
    }

    /**
     * you can do your own datas check of a form. Called only if the $form->check() is ok.
     * @param jFormsBase $form the current form
     * @return boolean true if it is ok.
     */
    protected function _checkDatas($form){
        return true;
    }

    /**
     * list all records
     */
    function index(){
        $offset = $this->intParam($this->offsetParameterName,0,true);

        $rep = $this->_getResponse();

        $dao = jDao::get($this->dao);
        $results = $dao->findBy(jDao::createConditions(),$offset,$this->listPageSize);
        $pk = $dao->getPrimaryKeyNames();

        jForms::destroy($this->form, $this->pseudoFormId);

        $tpl = new jTpl();
        $tpl->assign('list',$results);
        $tpl->assign('primarykey', $pk[0]);
        if(count($this->propertiesForList))
            $tpl->assign('properties', $this->propertiesForList);
        else{
            $tpl->assign('properties', array_keys($dao->getProperties()));
        }
        $tpl->assign('editAction' , $this->_getAction('preupdate'));
        $tpl->assign('createAction' , $this->_getAction('create'));
        $tpl->assign('deleteAction' , $this->_getAction('delete'));
        $tpl->assign('viewAction' , $this->_getAction('view'));
        $tpl->assign('listAction' , $this->_getAction('index'));
        $tpl->assign('listPageSize', $this->listPageSize);
        $tpl->assign('page',$offset);
        $tpl->assign('recordCount',$dao->countAll());
        $tpl->assign('offsetParameterName',$this->offsetParameterName);
        $rep->body->assign($this->templateAssign, $tpl->fetch($this->listTemplate));

        return $rep;
    }

    /**
     * display a form to create a record
     */
    function create(){
        $form = jForms::get($this->form);
        if($form == null){
            $form = jForms::create($this->form);
        }
        $rep = $this->_getResponse();

        $tpl = new jTpl();
        $tpl->assign('id', null);
        $tpl->assign('form',$form);
        $tpl->assign('submitAction', $this->_getAction('savecreate'));
        $tpl->assign('listAction' , $this->_getAction('index'));
        $rep->body->assign($this->templateAssign, $tpl->fetch($this->editTemplate));
        return $rep;

    }

    /**
     * save datas of a form in a new record
     */
    function savecreate(){
        $form = jForms::fill($this->form);
        $rep = $this->getResponse('redirect');
        if($form == null){
            $rep->action = $this->_getAction('index');
            return $rep;
        }

        if($form->check() && $this->_checkDatas($form)){
            $id = $form->saveToDao($this->dao);
            $form->saveAllFiles($this->uploadsDirectory);
            $rep->action = $this->_getAction('view');
            jForms::destroy($this->form);
            $rep->params['id'] = $id;
            return $rep;
        } else {
            $rep->action = $this->_getAction('create');
            return $rep;
        }
    }

    /**
     * prepare a form in order to edit an existing record, and redirect to the editupdate action
     */
    function preupdate(){
        $id = $this->param('id');
        $rep = $this->getResponse('redirect');

        if( $id === null ){
            $rep->action = $this->_getAction('index');
            return $rep;
        }

        $form = jForms::create($this->form, $id);

        try {
            $form->initFromDao($this->dao);
        }catch(Exception $e){
            $rep->action = $this->_getAction('index');
            return $rep;
        }

        $rep->action = $this->_getAction('editupdate');
        $rep->params['id'] = $id;
        return $rep;
    }

    /**
     * displays a forms to edit an existing record. The form should be
     * prepared with the preupdate before, so a refresh of the page
     * won't cause a reset of the form
     */
    function editupdate(){
        $id = $this->param('id');
        $form = jForms::get($this->form, $id);
        if( $form === null || $id === null){
            $rep = $this->getResponse('redirect');
            $rep->action = $this->_getAction('index');
            return $rep;
        }
        $rep = $this->_getResponse();

        $tpl = new jTpl();
        $tpl->assign('id', $id);
        $tpl->assign('form',$form);
        $tpl->assign('submitAction', $this->_getAction('saveupdate'));
        $tpl->assign('listAction' , $this->_getAction('index'));
        $tpl->assign('viewAction' , $this->_getAction('view'));
        $rep->body->assign($this->templateAssign, $tpl->fetch($this->editTemplate));
        return $rep;
    }

    /**
     * save datas of a form in a new record
     */
    function saveupdate(){
        $rep = $this->getResponse('redirect');
        $id = $this->param('id');
        $form = jForms::fill($this->form, $id);
        if( $form === null || $id === null){
            $rep->action = $this->_getAction('index');
            return $rep;
        }

        if($form->check() && $this->_checkDatas($form)){
            $id = $form->saveToDao($this->dao);
            $form->saveAllFiles($this->uploadsDirectory);
            $rep->action = $this->_getAction('view');
            jForms::destroy($this->form, $id);
        } else {
            $rep->action = $this->_getAction('editupdate');
        }
        $rep->params['id'] = $id;
        return $rep;
    }

    /**
     * displays a record
     */
    function view(){
        $id = $this->param('id');
        if( $id === null ){
            $rep = $this->getResponse('redirect');
            $rep->action = $this->_getAction('index');
            return $rep;
        }
        $rep = $this->_getResponse();

        // we're using a form to display a record, to have the portunity to have
        // labels with each values.
        $form = jForms::create($this->form, $this->pseudoFormId);
        $form->initFromDao($this->dao, $id);

        $tpl = new jTpl();
        $tpl->assign('id', $id);
        $tpl->assign('form',$form);
        $tpl->assign('editAction' , $this->_getAction('preupdate'));
        $tpl->assign('deleteAction' , $this->_getAction('delete'));
        $tpl->assign('listAction' , $this->_getAction('index'));
        $rep->body->assign($this->templateAssign, $tpl->fetch($this->viewTemplate));
        return $rep;
    }

    /**
     * delete a record
     */
    function delete(){
        $id = $this->param('id');
        if( $id !== null ){
            $dao = jDao::get($this->dao);
            $dao->delete($id);
        }
        $rep = $this->getResponse('redirect');
        $rep->action = $this->_getAction('index');
        return $rep;
    }
}


?>