<?php
/**
 * @package     jelix
 * @subpackage  forms_widget_plugin
 *
 * @author      Claudio Bernardes
 * @contributor Laurent Jouanneau, Julien Issler, Dominique Papin
 *
 * @copyright   2012 Claudio Bernardes
 * @copyright   2006-2014 Laurent Jouanneau, 2008-2011 Julien Issler, 2008 Dominique Papin
 *
 * @see        http://www.jelix.org
 * @licence     http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */

/**
 * HTML form builder.
 *
 * @package     jelix
 * @subpackage  forms_widget_plugin
 *
 * @see http://developer.jelix.org/wiki/rfc/jforms-controls-plugins
 */
class group_htmlFormWidget extends \jelix\forms\HtmlWidget\WidgetBase implements \jelix\forms\HtmlWidget\ParentWidgetInterface
{
    //------ ParentWidgetInterface

    public function addJs($js)
    {
        $this->parentWidget->addJs($js);
    }

    public function addFinalJs($js)
    {
        $this->parentWidget->addFinalJs($js);
    }

    public function controlJsChild()
    {
        return $this->ctrl->hasCheckbox;
    }

    //------- WidgetInterface

    public function outputMetaContent($resp)
    {
        foreach ($this->ctrl->getChildControls() as $ctrlref => $c) {
            if ($c->type == 'hidden') {
                continue;
            }
            $widget = $this->builder->getWidget($c, $this);
            $widget->outputMetaContent($resp);
        }
    }

    protected function jsGroupInternal($ctrl)
    {
        $jFormsJsVarName = $this->builder->getjFormsJsVarName();

        $this->parentWidget->addJs('c = new '.$jFormsJsVarName."ControlGroup('".$this->ctrl->ref."', ".$this->escJsStr($this->ctrl->label).");\n");
        $this->commonJs();
        if ($this->ctrl->hasCheckbox) {
            $this->parentWidget->addJs("c.hasCheckbox = true;\n");
        }
        $this->parentWidget->addJs("(function(gr){let c;\n");
    }

    public function outputLabel($format = '', $editMode = true)
    {
        /*if ($editMode || !$this->ctrl->hasCheckbox) {
            return;
        }
        if ($this->getValue($this->ctrl) == $this->ctrl->valueOnCheck) {
            return;
        }

        $ctrl = $this->ctrl;
        $attr = $this->getLabelAttributes($editMode);
        if ($format)
            $label = sprintf($format, $this->ctrl->label);
        else
            $label = $this->ctrl->label;
        $this->outputLabelAsTitle($label, $attr);*/
    }

    public function outputControl()
    {
        $attr = $this->getControlAttributes();
        $value = $this->getValue();
        $jFormsJsVarName = $this->builder->getjFormsJsVarName();

        if ($this->ctrl->hasCheckbox) {
            $chkattr = $attr;
            $chkattr['class'] = str_replace('jforms-ctrl-group', '', $chkattr['class']);
            $chkattr['type'] = 'checkbox';
            $chkattr['id'] = $attr['id'].'_checkbox';
            $chkattr['value'] = $this->ctrl->valueOnCheck;
            $chkattr['onclick'] = $jFormsJsVarName.'.getForm(\''.$this->builder->getName().'\').getControl(\''.$this->ctrl->ref.'\').showActivate()';
            if ($value == $this->ctrl->valueOnCheck) {
                $chkattr['checked'] = 'true';
            }
            $this->displayStartGroup($attr['id'], $this->ctrl->label, $chkattr);
            $this->jsGroupInternal($this->ctrl);
        } else {
            $this->displayStartGroup($attr['id'], $this->ctrl->label);
        }

        foreach ($this->ctrl->getChildControls() as $ctrlref => $c) {
            if ($c->type == 'submit' || $c->type == 'reset' || $c->type == 'hidden') {
                continue;
            }
            if (!$this->builder->getForm()->isActivated($ctrlref)) {
                continue;
            }
            $widget = $this->builder->getWidget($c, $this);
            $this->displayChildControl($widget);
            if ($this->ctrl->hasCheckbox) {
                $this->parentWidget->addJs("gr.addControl(c);\n");
            }
        }
        $this->displayEndGroup();
        if ($this->ctrl->hasCheckbox) {
            $this->parentWidget->addJs("gr.showActivate();})(c);\n");
        }
    }

    protected function displayStartGroup($groupId, $label, $checkBoxAttr = array())
    {
        if (count($checkBoxAttr) == 0) {
            echo '<fieldset id="',$groupId,'" class="jforms-ctrl-group"><legend>',htmlspecialchars($label),"</legend>\n";
        } else {
            echo '<fieldset id="',$groupId,'" class="jforms-ctrl-group"><legend>',
            '<input ';
            $this->_outputAttr($checkBoxAttr);
            echo '> <label for="'.$checkBoxAttr['id'].'">',htmlspecialchars($label),"</label></legend>\n";
        }
        echo '<table class="jforms-table-group" border="0">',"\n";
    }

    /**
     * @param \Jelix\Forms\HtmlWidget\WidgetInterface $widget
     */
    protected function displayChildControl($widget)
    {
        echo '<tr><th scope="row">';
        $widget->outputLabel();
        echo "</th>\n<td>";
        $widget->outputControl();
        $widget->outputHelp();
        echo "</td></tr>\n";
    }

    protected function displayEndGroup()
    {
        echo "</table></fieldset>\n";
    }

    public function outputControlValue()
    {
        $attr = $this->getValueAttributes();

        $showChildControl = (!$this->ctrl->hasCheckbox || ($this->ctrl->hasCheckbox && $this->getValue() == $this->ctrl->valueOnCheck));
        $this->displayStartValueGroup($attr['id'], $this->ctrl->label, $showChildControl);

        if (!$showChildControl) {
            $this->displayEmptyValue();
        } else {
            foreach ($this->ctrl->getChildControls() as $ctrlref => $c) {
                if ($c->type == 'submit' || $c->type == 'reset' || $c->type == 'hidden') {
                    continue;
                }
                if (!$this->builder->getForm()->isActivated($ctrlref)) {
                    continue;
                }
                $widget = $this->builder->getWidget($c, $this);
                $this->displayChildControlValue($widget);
            }
        }
        $this->displayEndValueGroup($showChildControl);
    }

    /**
     * @param $groupId
     * @param $label
     * @param bool $hasChildControlValue true if all values of child controls will be displayed
     */
    protected function displayStartValueGroup($groupId, $label, $hasChildControlValue)
    {
        echo '<fieldset id="',$groupId,'" class="jforms-ctrl-group"><legend>',htmlspecialchars($label),"</legend>\n";
        if ($hasChildControlValue) {
            echo '<table class="jforms-table-group" border="0">',"\n";
        }
    }

    protected function displayEmptyValue()
    {
        parent::outputControlValue();
    }

    /**
     * @param \Jelix\Forms\HtmlWidget\WidgetInterface $widget
     */
    protected function displayChildControlValue($widget)
    {
        echo '<tr><th scope="row">';
        $widget->outputLabel('', false);
        echo "</th>\n<td>";
        $widget->outputControlValue();
        echo "</td></tr>\n";
    }

    /**
     * @param bool $hasChildControlValue true if all values of child controls have been displayed
     */
    protected function displayEndValueGroup($hasChildControlValue)
    {
        if ($hasChildControlValue) {
            echo "</table>\n";
        }
        echo "</fieldset>\n";
    }
}
