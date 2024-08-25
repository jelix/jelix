<?php

/**
 * @package      jelix
 * @subpackage   controllers
 *
 * @author       Laurent Jouanneau
 * @contributor  Bastien Jaillot
 * @contributor  Thibault Piront (nuKs)
 * @contributor  Mickael Fradin, Bruno Perles
 * @contributor  Vincent Morel
 *
 * @copyright    2007-2024 Laurent Jouanneau
 * @copyright    2007 Thibault Piront
 * @copyright    2007,2008 Bastien Jaillot
 * @copyright    2009 Mickael Fradin, 2011 Bruno Perles
 * @copyright    2012 Vincent Morel
 *
 * @see         http://www.jelix.org
 * @licence      http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */

use Jelix\Forms\FormInstance;
use Jelix\Forms\Forms;

/**
 * a base class for crud controllers.
 *
 * @package    jelix
 * @subpackage controllers
 *
 * @since 1.0b3
 */
class jControllerDaoCrud extends jController
{
    /**
     * selector of the dao to use for the crud.
     * It should be filled by child controller.
     *
     * @var string
     */
    protected $dao = '';

    /**
     * selector of the form to use to edit and display a record
     * It should be filled by child controller.
     *
     * @var string
     */
    protected $form = '';

    /**
     * list of properties to show in the list page
     * if empty list (default), it shows all properties.
     * this property is only usefull when you use the default "list" template.
     *
     * @var string[]
     */
    protected $propertiesForList = array();

    /**
     * list of properties which serve to order the record list.
     * if empty list (default), the list is in a natural order.
     * keys are properties name, and values are "asc" or "desc".
     * Values can be changed by the user if showPropertiesOrderLinks is true.
     * In this case, '' means undetermined.
     *
     * @var string[]
     */
    protected $propertiesForRecordsOrder = array();

    /**
     * indicate if we show links to allow the user to order records list.
     *
     * @var bool
     */
    protected $showPropertiesOrderLinks = true;

    /**
     * template to display the list of records.
     *
     * @var string
     */
    protected $listTemplate = 'jelix~crud_list';

    /**
     * template to display the form.
     *
     * @var string
     */
    protected $editTemplate = 'jelix~crud_edit';

    /**
     * template to display a record.
     *
     * @var string
     */
    protected $viewTemplate = 'jelix~crud_view';

    /**
     * template to show error when a record is not found.
     *
     * @var string
     *
     * @since 1.6.17
     */
    protected $viewErrorTemplate = 'jelix~404.html';

    /**
     * number of record to display in the list page.
     *
     * @var int
     */
    protected $listPageSize = 20;

    /**
     * the template variable name to display a CRUD content in the main template
     * of the html response.
     *
     * @var string
     */
    protected $templateAssign = 'MAIN';

    /**
     * name of the parameter which contains the page offset, for the index action.
     *
     * @var string
     */
    protected $offsetParameterName = 'offset';

    /**
     * id for the "pseudo" form used to show a record. You can change it if the default one corresponds to
     * a possible id in your dao.
     *
     * @var string
     */
    protected $pseudoFormId = 'jelix_crud_roxor';

    /**
     * full path to the directory where uploaded files will be stored
     * automatically by jForms.
     * Set it to false if you want to handle yourself the uploaded files.
     * Set it with an empty string if you want to stored files in the default
     * var/uploads directory.
     *
     * @var false|string
     */
    protected $uploadsDirectory = '';

    /**
     * the jDb profile to use with the dao.
     */
    protected $dbProfile = '';

    /**
     * Returned a simple html response to display CRUD contents. You can redefine this
     * method to return a personnalized response.
     *
     * @return jResponseHtml the response
     */
    protected function _getResponse()
    {
        return $this->getResponse('html');
    }

    /**
     * create the form. You can redefine this method to modify dynamically the form
     * Typically, you call jForms::create and then you can call addControl or whatever.
     * Don't do a jForms::get or jForms::fill in this method !
     * called in methods: index, precreate, create, preupdate, view.
     *
     * @param int|string $formId
     *
     * @return FormInstance the form
     *
     * @since 1.1
     */
    protected function _createForm($formId = null)
    {
        return Forms::create($this->form, $formId);
    }

    /**
     * get an existing form. You can redefine this method to modify dynamically the form
     * Typically, you call jForms::get and then you can call addControl or whatever.
     * Don't do a jForms::create or jForms::fill in this method !
     * called in methods: create, savecreate, editupdate, saveupdate.
     *
     * @param int|string $formId
     *
     * @return FormInstance the form
     *
     * @since 1.1
     */
    protected function _getForm($formId = null)
    {
        return Forms::get($this->form, $formId);
    }

    /**
     * returned the selector of the action corresponding of the given method of the current controller.
     *
     * @param string $method name of one of method of this controller
     *
     * @return string an action selector
     */
    protected function _getAction($method)
    {
        $act = jApp::coord()->action;

        return $act->module . '~' . $act->controller . ':' . $method;
    }

    /**
     * you can do your own data check of a form by redefining this method.
     * You can also do some other things. It is called only if the $form->check() is ok.
     * and before the save of the data.
     *
     * @param FormInstance $form     the current form
     * @param bool       $calltype true for an update, false for a create
     *
     * @return bool true if it is ok
     */
    protected function _checkData($form, $calltype)
    {
        return true;
    }

    /**
     * @return string[] list of properties on which the list should be ordered
     */
    protected function fillColumnOrderList()
    {
        $keyActionDao = $this->_getAction($this->dao);
        if ($this->showPropertiesOrderLinks && count($this->propertiesForRecordsOrder)) {
            if (!isset($_SESSION['CRUD_LISTORDER'][$keyActionDao])) {
                $_SESSION['CRUD_LISTORDER'][$keyActionDao] = $this->propertiesForRecordsOrder;
            }
            if (($lo = $this->param('listorder'))
                && (array_key_exists($lo, $this->propertiesForRecordsOrder))
            ) {
                $listOrder = $_SESSION['CRUD_LISTORDER'][$keyActionDao];
                if (isset($listOrder[$lo])) {
                    $currentOrder = $listOrder[$lo];
                }
                else {
                    $currentOrder = '';
                }

                if ($currentOrder == 'asc') {
                    $currentOrder = 'desc';
                } elseif ($currentOrder == 'desc') {
                    $currentOrder = '';
                } else {
                    $currentOrder = 'asc';
                }
                $_SESSION['CRUD_LISTORDER'][$keyActionDao] = array($lo => $currentOrder);
            }
            return $_SESSION['CRUD_LISTORDER'][$keyActionDao];
        }

        return $this->propertiesForRecordsOrder;
    }


    /**
     * list all records.
     */
    public function index()
    {
        $offset = $this->intParam($this->offsetParameterName, 0, true);

        $rep = $this->_getResponse();

        $dao = jDao::get($this->dao, $this->dbProfile);

        $propertiesForOrder = $this->fillColumnOrderList();

        $cond = jDao::createConditions();
        $this->_indexSetConditions($cond);

        $results = $dao->findBy($cond, $offset, $this->listPageSize);
        $pk = $dao->getPrimaryKeyNames();

        // we're using a form to have the opportunity to have
        // labels for each column.
        $form = $this->_createForm($this->pseudoFormId);
        $tpl = new jTpl();
        $tpl->assign('list', $results);
        $tpl->assign('primarykey', $pk[0]);

        if (count($this->propertiesForList)) {
            $prop = $this->propertiesForList;
        } else {
            $prop = array_keys($dao->getProperties());
        }

        $tpl->assign('propertiesForListOrder', $this->propertiesForRecordsOrder);
        $tpl->assign('showPropertiesOrderLinks', $this->showPropertiesOrderLinks && count($this->propertiesForRecordsOrder));
        $tpl->assign('sessionForListOrder', $propertiesForOrder);
        $tpl->assign('properties', $prop);
        $tpl->assign('controls', $form->getControls());
        $tpl->assign('editAction', $this->_getAction('preupdate'));
        $tpl->assign('createAction', $this->_getAction('precreate'));
        $tpl->assign('deleteAction', $this->_getAction('delete'));
        $tpl->assign('viewAction', $this->_getAction('view'));
        $tpl->assign('listAction', $this->_getAction('index'));
        $tpl->assign('listPageSize', $this->listPageSize);
        $tpl->assign('page', $offset > 0 ? $offset : null);
        $tpl->assign('recordCount', $dao->countBy($cond));
        $tpl->assign('offsetParameterName', $this->offsetParameterName);
        $tpl->assign('dao', $this->dao);
        $tpl->assign('dbProfile', $this->dbProfile);

        $this->_index($rep, $tpl);
        $rep->body->assign($this->templateAssign, $tpl->fetch($this->listTemplate));
        Forms::destroy($this->form, $this->pseudoFormId);

        return $rep;
    }

    /**
     * redefine this method if you want to do additional things on the response and on the list template
     * during the index action.
     *
     * @param jResponseHtml $resp the response
     * @param jtpl          $tpl  the template to display the record list
     */
    protected function _index($resp, $tpl)
    {
    }

    /**
     * redefine this method if you want to do additional conditions to the index's select
     * during the index action.
     *
     * @param jDaoConditions $cond the conditions
     */
    protected function _indexSetConditions($cond)
    {
        $keyActionDao = $this->_getAction($this->dao);
        if (isset($_SESSION['CRUD_LISTORDER'][$keyActionDao])) {
            $itemsOrder = $_SESSION['CRUD_LISTORDER'][$keyActionDao];
        } else {
            $itemsOrder = $this->propertiesForRecordsOrder;
        }

        foreach ($itemsOrder as $p => $order) {
            if ($order == '') {
                continue;
            }
            $cond->addItemOrder($p, $order);
        }
    }

    /**
     * prepare a form to create a record.
     */
    public function precreate()
    {
        // we cannot create the form directly in the create action
        // because if the forms already exists, we wouldn't show
        // errors or already filled field. see ticket #292
        $form = $this->_createForm();
        $this->_preCreate($form);

        return $this->redirect($this->_getAction('create'));
    }

    /**
     * redefine this method if you want to do additionnal during the precreate action.
     *
     * @param FormInstance $form the form
     *
     * @since 1.1
     */
    protected function _preCreate($form)
    {
    }

    /**
     * display a form to create a record.
     */
    public function create()
    {
        $form = $this->_getForm();
        if ($form == null) {
            $form = $this->_createForm();
        }
        $rep = $this->_getResponse();

        $tpl = new jTpl();
        $tpl->assign('id', null);
        $tpl->assign('page', null);
        $tpl->assign('offsetParameterName', null);
        $tpl->assign('form', $form);
        $tpl->assign('submitAction', $this->_getAction('savecreate'));
        $tpl->assign('listAction', $this->_getAction('index'));
        $this->_create($form, $rep, $tpl);
        $rep->body->assign($this->templateAssign, $tpl->fetch($this->editTemplate));

        return $rep;
    }

    /**
     * redefine this method if you want to do additionnal things on the response and on the edit template
     * during the create action.
     *
     * @param FormInstance    $form the form
     * @param jResponseHtml $resp the response
     * @param jtpl          $tpl  the template to display the edit form
     */
    protected function _create($form, $resp, $tpl)
    {
    }

    /**
     * redefine this method if you wan to do additionnal things on the dao generated by the
     * FormInstance::prepareDaoFromControls method.
     *
     * @param FormInstance     $form        the form
     * @param jDaoRecordBase $form_daorec
     *
     * @since 1.1
     */
    protected function _beforeSaveCreate($form, $form_daorec)
    {
    }

    /**
     * save data of a form in a new record.
     */
    public function savecreate()
    {
        $form = $this->_getForm();
        if ($form == null) {
            return $this->redirect($this->_getAction('index'));
        }
        $form->initFromRequest();

        if ($form->check() && $this->_checkData($form, false)) {
            $results = $form->prepareDaoFromControls($this->dao, null, $this->dbProfile);
            /* @var \jDaoRecordBase $form_daorec
             * @var \jDaoFactoryBase $form_dao
             * @var boolean $form_toInsert
             */
            extract($results, EXTR_PREFIX_ALL, 'form'); //use a temp variable to avoid notices
            $this->_beforeSaveCreate($form, $form_daorec);
            $form_dao->insert($form_daorec);

            $id = $form_daorec->getPk();
            $rep = $this->redirect($this->_getAction('view'), ['id' => $id]);

            $this->_afterCreate($form, $id, $rep);
            if ($this->uploadsDirectory !== false) {
                $form->saveAllFiles($this->uploadsDirectory);
            }
            Forms::destroy($this->form);

            return $rep;
        }

        return $this->redirect($this->_getAction('create'));
    }

    /**
     * redefine this method if you wan to do additionnal things after the creation of
     * a record. For example, you can handle here the uploaded files. If you do
     * such handling, set the uploadsDirectory property to false, to prevent
     * the default behavior on uploaded files in the controller.
     *
     * @param FormInstance    $form the form object
     * @param mixed         $id   the new id of the inserted record
     * @param jResponseHtml $resp the response
     */
    protected function _afterCreate($form, $id, $resp)
    {
    }

    /**
     * prepare a form in order to edit an existing record, and redirect to the editupdate action.
     */
    public function preupdate()
    {
        $id = $this->param('id');
        $page = $this->param($this->offsetParameterName);

        if ($id === null) {

            return $this->redirect($this->_getAction('index'));
        }

        $form = $this->_createForm($id);

        try {
            $rec = $form->initFromDao($this->dao, null, $this->dbProfile);
            foreach ($rec->getPrimaryKeyNames() as $pkn) {
                $c = $form->getControl($pkn);
                if ($c !== null) {
                    $c->setReadOnly(true);
                }
            }
        } catch (Exception $e) {

            return $this->redirect($this->_getAction('index'));
        }
        $this->_preUpdate($form);

        return $this->redirect(
            $this->_getAction('editupdate'),
            ['id' => $id, $this->offsetParameterName => $page]
        );
    }

    /**
     * redefine this method if you want to do additionnal things during preupdate action.
     *
     * @param FormInstance $form the form object
     *
     * @since 1.1
     */
    protected function _preUpdate($form)
    {
    }

    /**
     * displays a forms to edit an existing record. The form should be
     * prepared with the preupdate before, so a refresh of the page
     * won't cause a reset of the form.
     */
    public function editupdate()
    {
        $id = $this->param('id');
        $page = $this->param($this->offsetParameterName);
        $form = $this->_getForm($id);
        if ($form === null || $id === null) {

            return $this->redirect($this->_getAction('index'));
        }
        $rep = $this->_getResponse();

        $tpl = new jTpl();
        $tpl->assign('id', $id);
        $tpl->assign('form', $form);
        $tpl->assign('page', $page);
        $tpl->assign('offsetParameterName', $this->offsetParameterName);
        $tpl->assign('submitAction', $this->_getAction('saveupdate'));
        $tpl->assign('listAction', $this->_getAction('index'));
        $tpl->assign('viewAction', $this->_getAction('view'));
        $this->_editUpdate($form, $rep, $tpl);
        $rep->body->assign($this->templateAssign, $tpl->fetch($this->editTemplate));

        return $rep;
    }

    /**
     * redefine this method if you wan to do additionnal things on the response and on the edit template
     * during the editupdate action.
     *
     * @param FormInstance    $form the form
     * @param jResponseHtml $resp the response
     * @param jtpl          $tpl  the template to display the edit form
     */
    protected function _editUpdate($form, $resp, $tpl)
    {
    }

    /**
     * redefine this method if you wan to do additionnal things on the dao generated by the
     * FormInstance::prepareDaoFromControls method.
     *
     * @param FormInstance     $form        the form
     * @param jDaoRecordBase $form_daorec
     * @param mixed          $id          the new id of the updated record
     *
     * @since 1.1
     */
    protected function _beforeSaveUpdate($form, $form_daorec, $id)
    {
    }

    /**
     * save data of a form in a new record.
     */
    public function saveupdate()
    {
        $id = $this->param('id');
        $page = $this->param($this->offsetParameterName);
        $form = $this->_getForm($id);
        if ($form === null || $id === null) {

            return $this->redirect($this->_getAction('index'));
        }
        $form->initFromRequest();

        $repParams = [
            'id' => $id,
            $this->offsetParameterName => $page
        ];

        if ($form->check() && $this->_checkData($form, true)) {
            $results = $form->prepareDaoFromControls($this->dao, $id, $this->dbProfile);
            /* @var \jDaoRecordBase $form_daorec
             * @var \jDaoFactoryBase $form_dao
             * @var boolean $form_toInsert
             */
            extract($results, EXTR_PREFIX_ALL, 'form'); //use a temp variable to avoid notices
            $this->_beforeSaveUpdate($form, $form_daorec, $id);
            $form_dao->update($form_daorec);

            $rep = $this->redirect($this->_getAction('view'), $repParams);

            $this->_afterUpdate($form, $id, $rep);
            if ($this->uploadsDirectory !== false) {
                $form->saveAllFiles($this->uploadsDirectory);
            }

            Forms::destroy($this->form, $id);
            return $rep;
        }

        return  $this->redirect($this->_getAction('editupdate'), $repParams);
    }

    /**
     * redefine this method if you wan to do additionnal things after the update of
     * a record. For example, you can handle here the uploaded files. If you do
     * such handling, set the uploadsDirectory property to false, to prevent
     * the default behavior on uploaded files in the controller.
     *
     * @param FormInstance    $form the form object
     * @param mixed         $id   the new id of the updated record
     * @param jResponseHtml $resp the response
     */
    protected function _afterUpdate($form, $id, $resp)
    {
    }

    /**
     * displays a record.
     */
    public function view()
    {
        $id = $this->param('id');
        $page = $this->param($this->offsetParameterName);
        if ($id === null) {

            return $this->redirect($this->_getAction('index'));
        }
        $rep = $this->_getResponse();
        $tpl = new jTpl();

        // we're using a form to display a record, to have the portunity to have
        // labels with each values. We need also him to load easily values of some
        // of controls with initControlFromDao (to use in _view method).
        $form = $this->_createForm($id);

        try {
            $rec = $form->initFromDao($this->dao, $id, $this->dbProfile);
        } catch (\Jelix\Forms\FormException $e) {
            if ($this->viewErrorTemplate) {
                $rep->body->assign($this->templateAssign, $tpl->fetch($this->viewErrorTemplate));
                $rep->setHttpStatus('404', 'Not Found');

                return $rep;
            }
            // for backward compatibility
            throw $e;
        }

        $tpl->assign('id', $id);
        $tpl->assign('form', $form);
        $tpl->assign('page', $page);
        $tpl->assign('record', $rec);
        $tpl->assign('offsetParameterName', $this->offsetParameterName);
        $tpl->assign('editAction', $this->_getAction('preupdate'));
        $tpl->assign('deleteAction', $this->_getAction('delete'));
        $tpl->assign('listAction', $this->_getAction('index'));
        $this->_view($form, $rep, $tpl);
        $rep->body->assign($this->templateAssign, $tpl->fetch($this->viewTemplate));

        return $rep;
    }

    /**
     * redefine this method if you want to do additionnal things on the response and on the view template
     * during the view action.
     *
     * @param FormInstance    $form the form
     * @param jResponseHtml $resp the response
     * @param jtpl          $tpl  the template to display the form content
     */
    protected function _view($form, $resp, $tpl)
    {
    }

    /**
     * delete a record.
     */
    public function delete()
    {
        $id = $this->param('id');
        $page = $this->param($this->offsetParameterName);
        $rep = $this->redirect(
            $this->_getAction('index'),
            array($this->offsetParameterName => $page)
        );

        if ($id !== null && $this->_delete($id, $rep)) {
            $dao = jDao::get($this->dao, $this->dbProfile);
            $dao->delete($id);
        }

        return $rep;
    }

    /**
     * redefine this method if you want to do additional things before the deletion of a record.
     *
     * @param mixed         $id   the id of the record to delete
     * @param jResponseRedirect $resp the response
     *
     * @return bool true if the record can be deleted
     */
    protected function _delete($id, $resp)
    {
        return true;
    }
}
