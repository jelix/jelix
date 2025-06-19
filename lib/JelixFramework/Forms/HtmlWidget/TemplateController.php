<?php
/**
 * @author      Laurent Jouanneau
 * @copyright   2020-2024 Laurent Jouanneau
 *
 * @see         https://www.jelix.org
 * @licence     http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */

namespace Jelix\Forms\HtmlWidget;

use Jelix\Forms\FormInstance;

class TemplateController {

    protected $form;

    protected $builder;

    protected $formViewMode = false;

    protected $displayedControls = array();

    protected $displayedSubmits = array();

    /**
     * @var string|null
     */
    protected $currentCtrlRef = null;

    /**
     * @var \Jelix\Forms\Controls\AbstractControl|null
     */
    protected $currentCtrl = null;

    /**
     * @var string|null
     */
    protected $currentSubmitRef = null;

    /**
     * @var \Jelix\Forms\Controls\AbstractControl|null
     */
    protected $currentSubmitCtrl = null;

    function __construct(FormInstance $form, $builderName, $builderOptions=array(), $submitActionSelector='', $submitActionParameters=[])
    {
        $this->form = $form;
        $this->builder = $form->getBuilder($builderName);
        $this->builder->setOptions($builderOptions);
        if ($submitActionSelector != '') {
            $this->builder->setAction($submitActionSelector, $submitActionParameters);
        }
        else {
            $this->formViewMode = true;
        }
    }

    function startForm()
    {
        if (!$this->formViewMode) {
            $this->builder->outputHeader();
        }
    }

    function outputAllControls()
    {
        if (!$this->formViewMode) {
            $this->builder->outputAllControls();
        }
    }

    function outputAllControlsValues()
    {
        if ($this->formViewMode) {
            $this->builder->outputAllControlsValues();
        }
    }

    function endForm()
    {
        if (!$this->formViewMode) {
            $this->builder->outputFooter();
        }
        $this->formViewMode = false;
    }

    /**
     * @return bool
     */
    public function isViewMode()
    {
        return $this->formViewMode;
    }

    /**
     * Give the form
     * @return \jFormsBase
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * @param bool $insideRealForm
     * @param array|null $controlsToDisplay
     * @param array|null $controlsToNotDisplay
     * @return \Generator
     */
    function controlsLoop($insideRealForm = true,
                          $controlsToDisplay=null,
                          $controlsToNotDisplay=null
                          )
    {
        $form = $this->form;
        foreach($form->getRootControls() as $ctrlref=>$ctrl) {
            if (!$form->isActivated($ctrlref)) {
                continue;
            }
            if ($ctrl->type == 'reset' || $ctrl->type == 'hidden') {
                continue;
            }
            if ($insideRealForm) {
                if ($ctrl->type == 'submit') {
                    continue;
                };
            }
            else {
                if ($ctrl->type == 'submit' && $ctrl->standalone) {
                    continue;
                }
                if ($ctrl->type == 'captcha' || $ctrl->type == 'secretconfirm') {
                    continue;
                }
            }

            if(!isset($this->displayedControls[$ctrlref])
                && (  ($controlsToDisplay===null && $controlsToNotDisplay === null)
                    || ($controlsToDisplay===null && !in_array($ctrlref, $controlsToNotDisplay))
                    || (is_array($controlsToDisplay) && in_array($ctrlref, $controlsToDisplay) ))
            ) {
                $this->currentCtrlRef = $ctrlref;
                $this->currentCtrl = $ctrl;
                yield $ctrl;
            }
        }
        $this->currentCtrlRef = null;
        $this->currentCtrl = null;
    }

    /**
     * @param array|null $submitToDisplay
     * @return \Generator
     */
    function submitsLoop($submitToDisplay=null)
    {
        $form = $this->form;
        foreach($form->getSubmits() as $ctrlref=>$ctrl) {
            if (!$form->isActivated($ctrlref)) {
                continue;
            }
            if (!isset($this->displayedSubmits[$ctrlref])
                && ($submitToDisplay ===null || in_array($ctrlref, $submitToDisplay))
            ){
                $this->currentSubmitCtrl = $ctrl;
                $this->currentSubmitRef = $ctrlref;
                yield $ctrl;
            }
        }
        $this->currentSubmitCtrl = null;
        $this->currentSubmitRef = null;
    }

    function isCurrentControl(...$refs)
    {
        foreach($refs as $ref) {
            if ($this->currentCtrlRef == $ref) {
                return true;
            }
        }
        return false;
    }

    function isControlValueEqualsTo($value, $ref='')
    {
        if ($ref == '') {
            $ref = $this->currentCtrlRef;
        }
        return ($this->form->getData($ref) == $value);
    }

    function doesControlExist($ref)
    {
        return ($this->form->getControl($ref) !== null);
    }

    function isControlActivated($ref = '')
    {
        if ($ref == '') {
            $ref = $this->currentCtrlRef;
        }
        return $this->form->isActivated($ref);
    }

    function getControlValue($ref, $tplName, $insteadOfDisplay)
    {
        $ctrl = $this->retrieveControl($ref, $tplName);
        if (!$ctrl) {
            return false;
        }
        if ($ctrl->type == 'hidden' || $ctrl->type == 'captcha' || $ctrl->type == 'reset') {
            return false;
        }

        if ($ctrl->type == 'submit' && ($ctrl->standalone || !$this->formViewMode)) {
            return false;
        }

        if ($insteadOfDisplay === null && $this->formViewMode) {
            $insteadOfDisplay = true;
        }

        if ($insteadOfDisplay) {
            $this->displayedControls[$ref] = true;
        }

        return $this->builder->getForm()->getData($ref);
    }

    /**
     * @return \jFormsControl|null
     */
    public function getCurrentControl()
    {
        if ($this->currentCtrlRef == '') {
            return null;
        }
        return $this->currentCtrl;
    }

    /**
     * @param $ref
     * @return \jFormsControl|null
     */
    public function getControl($ref)
    {
        return $this->form->getControl($ref);
    }

    /**
     * @param $ref
     * @param $tplName
     * @return false|\jFormsControl|null
     * @throws \jException
     */
    public function retrieveControl(&$ref, $tplName)
    {
        if ($ref == '') {
            if ($this->currentCtrlRef == '') {
                return false;
            }
            $ref = $this->currentCtrlRef;
            return $this->currentCtrl;
        } else {
            $ctrl = $this->form->getControl($ref);
            if (!$ctrl) {
                if ($tplName) {
                    throw new \jException(
                        'jelix~formserr.unknown.control',
                        array($ref, $this->form->getSelector(), $tplName)
                    );
                }
                else {
                    throw new \jException(
                        'jelix~formserr.unknown.control',
                        array($ref, $this->form->getSelector())
                    );
                }
            }
            return $ctrl;
        }
    }

    function outputControl($ref = '', $attributes=[], $tplName='')
    {
        $ctrl = $this->retrieveControl($ref, $tplName);
        if (!$ctrl) {
            return false;
        }

        if ($ctrl->type == 'submit' || $ctrl->type == 'reset' || $ctrl->type == 'hidden') {
            return false;
        }

        if (!empty($attributes['placeholder']) && !empty($ctrl) && $attributes['placeholder'] === true) {
            $attributes['placeholder'] = $ctrl->label;
        }

        $this->displayedControls[$ref] = true;
        if ($this->form->isActivated($ref)) {
            $this->builder->outputControl($ctrl, $attributes);
            return true;
        }
        return false;
    }

    function outputControlLabel($ref = '', $format='', $tplName='')
    {
        $ctrl = $this->retrieveControl($ref, $tplName);
        if (!$ctrl) {
            return false;
        }

        if ($ctrl->type == 'hidden') {
            return false;
        }

        if (!$this->form->isActivated($ref)) {
            return false;
        }

        if ($this->formViewMode) {
            if ($ctrl->type == 'captcha') {
                return false;
            }
        }
        else {
            if ($ctrl->type == 'submit' || $ctrl->type == 'reset') {
                return false;
            }
        }

        $this->builder->outputControlLabel($ctrl, $format);
        return true;
    }

    function outputControlValue($ref = '', $attributes='', $tplName='', $rawValue = false, $contentWhenEmpty = '')
    {
        $ctrl = $this->retrieveControl($ref, $tplName);
        if (!$ctrl) {
            return false;
        }

        if ($ctrl->type == 'hidden' || $ctrl->type == 'captcha' || $ctrl->type == 'reset') {
            return false;
        }

        if ($ctrl->type == 'submit' && ($ctrl->standalone || !$this->formViewMode)) {
            return false;
        }

        $this->displayedControls[$ref] = true;

        if (!$this->form->isActivated($ref)) {
            return false;
        }

        $value = $this->form->getData($ref);

        if ($contentWhenEmpty != ''
            && ((is_array($value) && count($value) == 0) ||
                (!is_array($value) && trim($value) == ''))) {
            echo $contentWhenEmpty;
            return true;
        }

        if ($rawValue) {
            $this->builder->outputControlRawValue($ctrl, $attributes);
        }
        else {
            $this->builder->outputControlValue($ctrl, $attributes);
        }
        return true;
    }

    public function outputReset()
    {
        $ctrl = $this->form->getReset();
        if ($ctrl && $this->form->isActivated($ctrl->ref)) {
            $this->builder->outputControl($ctrl);
        }
    }

    public function outputSubmit($ref = '', $attributes=[], $tplName = '')
    {
        if ($ref == '') {
            if ($this->currentSubmitRef == '') {
                $ctrls = $this->form->getSubmits();
                if (count($ctrls) == 0) {
                    if ($tplName) {
                        throw new \jException(
                            'jelix~formserr.unknown.control',
                            array('submit', $this->form->getSelector(), $tplName)
                        );
                    }
                    else {
                        throw new \jException(
                            'jelix~formserr.unknown.control',
                            array('submit', $this->form->getSelector())
                        );
                    }
                }
                reset($ctrls);
                $ref = key($ctrls);
                $ctrl = current($ctrls);
            }
            else {
                $ref = $this->currentSubmitRef;
                $ctrl = $this->currentSubmitCtrl;
            }
        } else {
            $ctrls = $this->form->getSubmits();
            if (count($ctrls) == 0) {
                if ($tplName) {
                    throw new \jException(
                        'jelix~formserr.unknown.control',
                        array($ref, $this->form->getSelector(), $tplName)
                    );
                }
                else {
                    throw new \jException(
                        'jelix~formserr.unknown.control',
                        array($ref, $this->form->getSelector())
                    );
                }
            }
            $ctrl = $ctrls[$ref];
        }

        if ($this->form->isActivated($ref)) {
            $this->displayedSubmits[$ref] = true;
            $this->builder->outputControl($ctrl, $attributes);
        }
    }
}