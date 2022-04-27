<?php
/**
 * @package      jelix
 * @subpackage   controllers
 *
 * @author       Laurent Jouanneau
 * @copyright    2021 Laurent Jouanneau
 *
 * @see         http://www.jelix.org
 * @licence      http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */
class jControllerDaoCrudFilter extends jControllerDaoCrud
{
    /**
     * selector of the form to use to filter the results on the "list" template if the filter is enabled
     * It should be filled by child controller.
     *
     * @var string
     */
    protected $filterForm = '';

    /**
     * list of properties to enable the filtering on
     * if empty list (default), it use the same properties as $propertiesForList
     * this property is only usefull when you use the default "list" template.
     *
     * @var array
     */
    protected $propertiesForFilter = array();

    /**
     * template to display the list of records.
     *
     * @var string
     */
    protected $listTemplate = 'jelix~crud_list_filter';

    /**
     * id for the "pseudo" form used to filter results. You can change it if the default one corresponds to
     * a possible id in your dao.
     *
     * @var string
     */
    protected $pseudoFilterFormId = 'jelix_crud_filter';

    /**
     * list all records.
     */
    public function index()
    {
        $offset = $this->intParam($this->offsetParameterName, 0, true);

        $rep = $this->_getResponse();

        $dao = jDao::get($this->dao, $this->dbProfile);

        if (count($this->propertiesForList)) {
            $prop = $this->propertiesForList;
        } else {
            $prop = array_keys($dao->getProperties());
        }
        // filter properties. If array is empty we take the same properties as the display
        if (empty($this->propertiesForFilter)) {
            $this->propertiesForFilter = $prop;
        }

        $cond = jDao::createConditions();
        $filterForm = $this->_filterCreateForm();
        if ($filterForm) {
            $this->_filterSetConditions($cond);
        }
        $this->_indexSetConditions($cond);

        $results = $dao->findBy($cond, $offset, $this->listPageSize);
        $pk = $dao->getPrimaryKeyNames();

        // we're using a form to have the portunity to have
        // labels for each columns.
        $form = $this->_createForm($this->pseudoFormId);
        $tpl = new jTpl();
        $tpl->assign('list', $results);
        $tpl->assign('primarykey', $pk[0]);

        // filter
        $tpl->assign('filterForm', $filterForm);
        $tpl->assign('filterAction', $this->_getAction('index'));
        $tpl->assign('filterFields', $this->propertiesForFilter);

        $tpl->assign('properties', $prop);
        $tpl->assign('controls', $form->getControls());
        $tpl->assign('editAction', $this->_getAction('preupdate'));
        $tpl->assign('createAction', $this->_getAction('precreate'));
        $tpl->assign('deleteAction', $this->_getAction('delete'));
        $tpl->assign('viewAction', $this->_getAction('view'));
        $tpl->assign('listAction', $this->_getAction('index'));
        $tpl->assign('listPageSize', $this->listPageSize);
        $tpl->assign('page', $offset);
        $tpl->assign('recordCount', $dao->countBy($cond));
        $tpl->assign('offsetParameterName', $this->offsetParameterName);

        $this->_index($rep, $tpl);
        $rep->body->assign($this->templateAssign, $tpl->fetch($this->listTemplate));
        jForms::destroy($this->form, $this->pseudoFormId);

        return $rep;
    }

    /**
     * this method set conditions according to the form filter submit.
     *
     * @param jDaoConditions $cond the conditions
     */
    protected function _filterSetConditions($cond)
    {
        if ($this->filterForm) {
            if ($this->param('_submit')) {
                $form = jForms::fill($this->filterForm, $this->pseudoFilterFormId);
            } else {
                $form = jForms::get($this->filterForm, $this->pseudoFilterFormId);
            }

            if (!$form) {
                $form = jForms::create($this->filterForm, $this->pseudoFilterFormId);
            }

            // retrieve properties information of the dao
            $dao = jDao::get($this->dao, $this->dbProfile);
            $properties = array_keys($dao->getProperties());

            foreach ($this->propertiesForFilter as $property) {
                if (!is_null($this->param($property, null))) {
                    $form->setData($property, $this->param($property));
                }
                $value = $form->getData($property);
                // filter with the value if is set and the property is in the dao
                if ((!empty($value) || is_numeric($value)) && in_array($property, $properties)) {
                    $cond->addCondition($property, 'LIKE', $value.'%');
                }
            }
        }
    }

    /**
     * Get or Create the filter form if we use filter.
     *
     * @return null|jFormsBase the form if filter is enable
     */
    protected function _filterCreateForm()
    {
        if ($this->filterForm) {
            $form = jForms::get($this->filterForm, $this->pseudoFilterFormId);
            if (!$form) {
                $form = jForms::create($this->filterForm, $this->pseudoFilterFormId);
            }
            $form->securityLevel = 0;
            // for each submits groups
            foreach ($form->getSubmits() as $key => $submit) {
                $submit->label = jLocale::get('jelix~ui.buttons.search');

                break;
            }
            //Remove required flags in the form
            foreach ($form->getControls() as $control) {
                $control->required = false;
            }

            return $form;
        }

        return null;
    }
}
