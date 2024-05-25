<?php
/**
 * @package      jelix
 * @subpackage   controllers
 *
 * @author       Laurent Jouanneau
 * @copyright    2021-2024 Laurent Jouanneau
 *
 * @see          https://www.jelix.org
 * @licence      http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */

/**
 * Implements a CRUD controller, having a filter form to filter the list.
 *
 * The form should be a jForms form, and should have controls having the
 * same name of the DAO properties that are filtered. It must also have a
 * submit button named `_submitFilter`.
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
     * if empty list (default), it uses the same properties as indicated into
     * the filterForm.
     *
     * @var string[]
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
     * @var jFormsBase
     */
    protected $filterFormObj;

    protected function _index($resp, $tpl)
    {
        $tpl->assign('filterForm', $this->filterFormObj);
        $tpl->assign('filterAction', $this->_getAction('index'));
        $tpl->assign('filterFields', $this->propertiesForFilter);
    }

    protected function _indexSetConditions($cond)
    {
        $this->filterFormObj = $this->_filterCreateForm();
        if ($this->filterFormObj) {

            // filter properties. If array is empty we fill it, by taken the
            // same properties of the filterForm
            if (empty($this->propertiesForFilter)) {
                foreach($this->filterFormObj->getControls() as $control) {
                    if ($control->type != 'submit' && $control->type != 'reset') {
                        $this->propertiesForFilter[] = $control->ref;
                    }
                }
            }

            // there is the submit button value: the form have been submit
            // we reset all filters with the content of the form
            if ($this->param('_submitFilter')) {
                $this->filterFormObj->initFromRequest();
            }
            /*else {
                foreach ($this->propertiesForFilter as $property) {
                    if (!is_null($this->param($property, null))) {
                        $this->filterFormObj->setData($property, $this->param($property));
                    }
                }
            }*/

            $this->_filterSetConditions($this->filterFormObj, $cond);
        }
        parent::_indexSetConditions($cond);
    }

    /**
     * this method set conditions according to the form filter submit.
     *
     * @param jFormsBase $form
     * @param jDaoConditions $cond the conditions
     */
    protected function _filterSetConditions($form, $cond)
    {
        // retrieve properties information of the dao
        $dao = jDao::get($this->dao, $this->dbProfile);
        $properties = array_keys($dao->getProperties());

        foreach ($this->propertiesForFilter as $property) {
            $value = $form->getData($property);
            // filter with the value if is set and the property is in the dao
            if ($value !== '' && $value !== null && in_array($property, $properties)) {
                if (is_numeric($value)) {
                    $cond->addCondition($property, '=', $value);
                }
                else {
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

            //Remove required flags in the form
            foreach ($form->getControls() as $control) {
                $control->required = false;
            }

            if (!$form->getControl('_submitFilter')) {
                $submit = new \Jelix\Forms\Controls\SubmitControl('_submitFilter');
                $submit->label = jLocale::get('jelix~ui.buttons.search');
                $form->addControl($submit);
            }

            return $form;
        }

        return null;
    }
}
