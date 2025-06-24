<?php

/**
 * @package      jelix
 * @subpackage   controllers
 *
 * @author       Laurent Jouanneau
 * @contributor  Bastien Jaillot
 * @contributor  Thibault Piront (nuKs)
 * @contributor  Bruno Perles (brunto)
 *
 * @copyright    2007-2024 Laurent Jouanneau
 * @copyright    2007 Thibault Piront
 * @copyright    2007,2008 Bastien Jaillot
 * @copyright    2011 Bruno PERLES
 *
 * @see         http://www.jelix.org
 * @licence      http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */

use Jelix\Dao\DaoConditions;
use Jelix\Forms\FormInstance;
use Jelix\Forms\Forms;

/**
 * a base class for crud controllers, for DAO which have a primary key based on
 * two fields, a "static" field (a field that we know the value and which is a criteria to
 * to select all viewed record), and a
 * "dynamic" field (the value of the field is created by the user or autoincremented).
 *
 * @package    jelix
 * @subpackage controllers
 *
 * @since 1.1
 */
class jControllerDaoCrudDfk extends jController
{
    /**
     * name of the request parameter and of the field in the dao, for the dynamic primary key.
     */
    protected $dpkName = 'id';

    /**
     * name of the request parameter and of the field in the dao, for the static primary key.
     */
    protected $spkName = 'spk';

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
    protected $listTemplate = 'jelix~cruddfk_list';

    /**
     * template to display the form.
     *
     * @var string
     */
    protected $editTemplate = 'jelix~cruddfk_edit';

    /**
     * template to display a record.
     *
     * @var string
     */
    protected $viewTemplate = 'jelix~cruddfk_view';

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
    protected $pseudoFormId = 'jelix_cruddf_roxor';

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
     * Returned a simple html response to display CRUD contents. You can override this
     * method to return a personnalized response.
     *
     * @return jResponseHtml the response
     */
    protected function _getResponse()
    {
        return $this->getResponse('html');
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
     * you can do your own data check of a form by overloading this method.
     * You can also do some other things. It is called only if the $form->check() is ok.
     * and before the save of the data.
     *
     * @param string     $spk      the static value of the primary key of the record
     * @param FormInstance $form     the current form
     * @param bool       $calltype true for an update, false for a create
     *
     * @return bool true if it is ok
     */
    protected function _checkData($spk, $form, $calltype)
    {
        return true;
    }

    protected function _isPkAutoIncrement($dao = null)
    {
        if ($dao == null) {
            $dao = jDao::get($this->dao, $this->dbProfile);
        }

        $props = $dao->getProperties();

        return $props[$this->dpkName]['autoIncrement'] == true;
    }

    protected function _getPk($spk, $dpk, $dao = null)
    {
        if ($dao == null) {
            $dao = jDao::get($this->dao, $this->dbProfile);
        }

        $pks = $dao->getPrimaryKeyNames();
        if ($pks[0] == $this->spkName) {
            return array($spk, $dpk);
        }

        return array($dpk, $spk);
    }

    /**
     * list all records.
     */
    public function index()
    {
        $offset = $this->intParam($this->offsetParameterName, 0, true);

        $rep = $this->_getResponse();

        $dao = jDao::get($this->dao, $this->dbProfile);

        $keyActionDao = $this->_getAction($this->dao);
        if ($this->showPropertiesOrderLinks && count($this->propertiesForRecordsOrder)) {
            if (!isset($_SESSION['CRUD_LISTORDER'][$keyActionDao])) {
                $_SESSION['CRUD_LISTORDER'][$keyActionDao] = $this->propertiesForRecordsOrder;
            }
            if (($lo = $this->param('listorder'))
                && (array_key_exists($lo, $this->propertiesForRecordsOrder))
            ) {
                $listOrder = $_SESSION['CRUD_LISTORDER'][$keyActionDao];
                if (isset($listOrder[$lo]) && $listOrder[$lo] == 'asc') {
                    $listOrder[$lo] = 'desc';
                } elseif (isset($listOrder[$lo]) && $listOrder[$lo] == 'desc') {
                    unset($listOrder[$lo]);
                } else {
                    $listOrder[$lo] = 'asc';
                }
                $_SESSION['CRUD_LISTORDER'][$keyActionDao] = $listOrder;
            }
        }

        $cond = jDao::createConditions();
        $cond->addCondition($this->spkName, '=', $this->param($this->spkName));
        $this->_indexSetConditions($cond);

        $results = $dao->findBy($cond, $offset, $this->listPageSize);

        // we're using a form to have the portunity to have
        // labels for each columns.
        $form = Forms::create($this->form, $this->pseudoFormId);
        $tpl = new jTpl();
        $tpl->assign('list', $results);
        $tpl->assign('dpkName', $this->dpkName);
        $tpl->assign('spkName', $this->spkName);
        $tpl->assign('spk', $this->param($this->spkName));

        if (count($this->propertiesForList)) {
            $prop = $this->propertiesForList;
        } else {
            $prop = array_keys($dao->getProperties());
        }

        $tpl->assign('propertiesForListOrder', $this->propertiesForListOrder);
        $tpl->assign('sessionForListOrder', isset($_SESSION['CRUD_LISTORDER'][$keyActionDao]) ? $_SESSION['CRUD_LISTORDER'][$keyActionDao] : $this->propertiesForListOrder);
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

        $this->_index($rep, $tpl);
        $rep->body->assign($this->templateAssign, $tpl->fetch($this->listTemplate));
        Forms::destroy($this->form, $this->pseudoFormId);

        return $rep;
    }

    /**
     * overload this method if you wan to do additionnal things on the response and on the list template
     * during the index action.
     *
     * @param jResponseHtml $resp the response
     * @param jtpl          $tpl  the template to display the record list
     */
    protected function _index($resp, $tpl)
    {
    }

    /**
     * overload this method if you wan to do additionnal conditions to the index's select
     * during the index action.
     *
     * @param DaoConditions $cond the conditions
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
        // first, we cannot create the form directly in the create action
        // because if the forms already exists, we wouldn't show
        // errors or already filled field. see ticket #292
        $form = Forms::create($this->form);
        $this->_preCreate($form);

        return $this->redirect(
            $this->_getAction('create'),
            [$this->spkName => $this->param($this->spkName)]
        );
    }

    /**
     * overload this method if you want to do additionnal during the precreate action.
     *
     * @param FormInstance $form the form
     */
    protected function _preCreate($form)
    {
    }

    /**
     * display a form to create a record.
     */
    public function create()
    {
        $form = Forms::get($this->form);
        if ($form == null) {
            $form = Forms::create($this->form);
        }
        $rep = $this->_getResponse();

        $tpl = new jTpl();
        $tpl->assign('dpk', null);
        $tpl->assign('page', null);
        $tpl->assign('offsetParameterName', null);
        $tpl->assign('dpkName', $this->dpkName);
        $tpl->assign('spkName', $this->spkName);
        $tpl->assign('spk', $this->param($this->spkName));
        $tpl->assign('form', $form);
        $tpl->assign('submitAction', $this->_getAction('savecreate'));
        $tpl->assign('listAction', $this->_getAction('index'));
        $this->_create($form, $rep, $tpl);
        $rep->body->assign($this->templateAssign, $tpl->fetch($this->editTemplate));

        return $rep;
    }

    /**
     * overload this method if you wan to do additionnal things on the response and on the edit template
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
     * save data of a form in a new record.
     */
    public function savecreate()
    {
        $form = Forms::fill($this->form);
        $spk = $this->param($this->spkName);
        $repParams = [$this->spkName => $spk];

        if ($form == null) {

            return $this->redirect(
                $this->_getAction('index'),
                $repParams
            );
        }

        if ($form->check() && $this->_checkData($spk, $form, false)) {
            $results = $form->prepareDaoFromControls($this->dao, null, $this->dbProfile);
            extract($results, EXTR_PREFIX_ALL, 'form');
            $form_daorec->{$this->spkName} = $spk;
            if (!$this->_isPkAutoIncrement($form_dao)) {
                $form_daorec->{$this->dpkName} = $this->param($this->dpkName);
            }
            $this->_beforeSaveCreate($form, $form_daorec);
            $form_dao->insert($form_daorec);

            $id = $form_daorec->getPk();
            $rep = $this->redirect($this->_getAction('view'), $repParams);

            $this->_afterCreate($form, $id, $rep);
            if ($this->uploadsDirectory !== false) {
                $form->saveAllFiles($this->uploadsDirectory);
            }

            Forms::destroy($this->form);

            $pknames = $form_dao->getPrimaryKeyNames();
            if ($pknames[0] == $this->spkName) {
                $rep->params[$this->spkName] = $id[0];
                $rep->params[$this->dpkName] = $id[1];
            } else {
                $rep->params[$this->spkName] = $id[1];
                $rep->params[$this->dpkName] = $id[0];
            }

            return $rep;
        }

        return $this->redirect(
            $this->_getAction('create'),
            $repParams
        );
    }

    /**
     * overload this method if you wan to do additionnal things on the dao generated by the
     * FormInstance::prepareDaoFromControls method.
     *
     * @param FormInstance     $form        the form
     * @param \Jelix\Dao\AbstractDaoRecord $form_daorec
     */
    protected function _beforeSaveCreate($form, $form_daorec)
    {
    }

    /**
     * overload this method if you wan to do additionnal things after the creation of
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
        $spk = $this->param($this->spkName);
        $dpk = $this->param($this->dpkName);
        $page = $this->param($this->offsetParameterName);

        $repParams = [$this->spkName => $spk];

        if ($dpk === null) {

            return $this->redirect($this->_getAction('index'), $repParams);
        }

        $id = $this->_getPk($spk, $dpk);
        $form = Forms::create($this->form, $id);

        try {
            $form->initFromDao($this->dao, $id, $this->dbProfile);
        } catch (Exception $e) {

            return $this->redirect($this->_getAction('index'), $repParams);
        }

        $this->_preUpdate($form);

        $repParams[$this->dpkName] = $dpk;
        $repParams[$this->offsetParameterName] = $page;

        return $this->redirect($this->_getAction('editupdate'), $repParams);
    }

    /**
     * overload this method if you want to do additionnal things during preupdate action.
     *
     * @param FormInstance $form the form object
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
        $spk = $this->param($this->spkName);
        $dpk = $this->param($this->dpkName);
        $page = $this->param($this->offsetParameterName);

        $id = $this->_getPk($spk, $dpk);
        $form = Forms::get($this->form, $id);
        if ($form === null || $dpk === null) {

            return $this->redirect(
                $this->_getAction('index'),
                [$this->spkName => $spk]
            );
        }
        $rep = $this->_getResponse();

        $tpl = new jTpl();
        $tpl->assign('dpk', $dpk);
        $tpl->assign('dpkName', $this->dpkName);
        $tpl->assign('spkName', $this->spkName);
        $tpl->assign('spk', $spk);
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
     * overload this method if you wan to do additionnal things on the response and on the edit template
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
     * save data of a form in a new record.
     */
    public function saveupdate()
    {
        $spk = $this->param($this->spkName);
        $dpk = $this->param($this->dpkName);
        $page = $this->param($this->offsetParameterName);

        $repParams = [$this->spkName => $spk];

        $id = $this->_getPk($spk, $dpk);
        $form = Forms::fill($this->form, $id);
        if ($form === null || $dpk === null) {

            return $this->redirect($this->_getAction('index'), $repParams);
        }

        $repParams[$this->dpkName] = $dpk;
        $repParams[$this->offsetParameterName] = $page;

        if ($form->check() && $this->_checkData($spk, $form, true)) {
            $results = $form->prepareDaoFromControls($this->dao, $id, $this->dbProfile);
            extract($results, EXTR_PREFIX_ALL, 'form');
            $this->_beforeSaveUpdate($form, $form_daorec, $id);
            $form_dao->update($form_daorec);

            $rep = $this->redirect($this->_getAction('view'), $repParams);

            $this->_afterUpdate($form, $id, $rep);
            if ($this->uploadsDirectory !== false) {
                $form->saveAllFiles($this->uploadsDirectory);
            }

            Forms::destroy($this->form, $id);
        } else {
            $rep = $this->redirect($this->_getAction('editupdate'), $repParams);
        }

        return $rep;
    }

    /**
     * overload this method if you wan to do additionnal things on the dao generated by the
     * FormInstance::prepareDaoFromControls method.
     *
     * @param FormInstance     $form        the form
     * @param \Jelix\Dao\AbstractDaoRecord $form_daorec
     * @param mixed          $id          the new id of the updated record
     */
    protected function _beforeSaveUpdate($form, $form_daorec, $id)
    {
    }

    /**
     * overload this method if you wan to do additionnal things after the update of
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
        $spk = $this->param($this->spkName);
        $dpk = $this->param($this->dpkName);
        $page = $this->param($this->offsetParameterName);

        if ($dpk === null) {
            return $this->redirect(
                $this->_getAction('index'),
                [$this->spkName => $spk]
            );
        }

        $rep = $this->_getResponse();

        $id = $this->_getPk($spk, $dpk);

        // we're using a form to display a record, to have the portunity to have
        // labels with each values. We need also him to load easily values of some
        // of controls with initControlFromDao (to use in _view method).
        $form = Forms::create($this->form, $id);
        $form->initFromDao($this->dao, $id, $this->dbProfile);

        $tpl = new jTpl();
        $tpl->assign('dpk', $dpk);
        $tpl->assign('dpkName', $this->dpkName);
        $tpl->assign('spkName', $this->spkName);
        $tpl->assign('spk', $spk);
        $tpl->assign('form', $form);
        $tpl->assign('page', $page);
        $tpl->assign('offsetParameterName', $this->offsetParameterName);
        $tpl->assign('editAction', $this->_getAction('preupdate'));
        $tpl->assign('deleteAction', $this->_getAction('delete'));
        $tpl->assign('listAction', $this->_getAction('index'));
        $this->_view($form, $rep, $tpl);
        $rep->body->assign($this->templateAssign, $tpl->fetch($this->viewTemplate));

        return $rep;
    }

    /**
     * overload this method if you want to do additionnal things on the response and on the view template
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
        $spk = $this->param($this->spkName);
        $dpk = $this->param($this->dpkName);
        $page = $this->param($this->offsetParameterName);

        $rep = $this->redirect(
            $this->_getAction('index'),
            [$this->spkName => $spk, $this->offsetParameterName => $page]
        );

        $dao = jDao::get($this->dao, $this->dbProfile);
        $id = $this->_getPk($spk, $dpk, $dao);
        if ($dpk !== null && $this->_delete($spk, $dpk, $rep)) {
            $dao->delete($id);
        }

        return $rep;
    }

    /**
     * overload this method if you want to do additionnal things before the deletion of a record.
     *
     * @param mixed         $spk  the static value of the primary key of the record to delete
     * @param mixed         $dpk  the dynamic value of the primary key of the record to delete
     * @param jResponseHtml $resp the response
     *
     * @return bool true if the record can be deleted
     */
    protected function _delete($spk, $dpk, $resp)
    {
        return true;
    }
}
