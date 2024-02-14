<?php
/**
 * @package     jelix
 * @subpackage  forms_widget_plugin
 *
 * @author      Claudio Bernardes
 * @contributor Laurent Jouanneau, Julien Issler, Dominique Papin
 *
 * @copyright   2012 Claudio Bernardes
 * @copyright   2006-2018 Laurent Jouanneau, 2008-2011 Julien Issler, 2008 Dominique Papin
 *
 * @see        http://www.jelix.org
 * @licence     http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */

use Jelix\Forms\HtmlWidget\WidgetInterface;

/**
 * HTML form builder.
 *
 * @package     jelix
 * @subpackage  forms_widget_plugin
 *
 * @see http://developer.jelix.org/wiki/rfc/jforms-controls-plugins
 *
 * @example generated JS code:
 * c = new jFormsJQControlChoice('choice2', 'Another choice');
 * c.errInvalid='"Another choice" field is invalid';
 * jFormsJQ.tForm.addControl(c);
 * (function(ch){
 * ch.items['choice1']=[];
 * ch.addControl(c, 'choice2');
 * ch.addControl(c, 'choice2');
 * ch.addControl(c, 'choice2');
 * ch.addControl(c, 'choice3');
 * ch.addControl(c, 'choice4');
 * ch.addControl(c, 'choice4');
 * ch.activate('');
 * })(c);
 */
class choice_htmlFormWidget extends \jelix\forms\HtmlWidget\WidgetBase implements \jelix\forms\HtmlWidget\ParentWidgetInterface
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
        return true;
    }

    // -------- WidgetInterface

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

    protected function jsChoiceInternal($ctrl)
    {
        $jFormsJsVarName = $this->builder->getjFormsJsVarName();

        $this->parentWidget->addJs('c = new '.$jFormsJsVarName."ControlChoice('".$this->ctrl->ref."', ".$this->escJsStr($this->ctrl->label).");\n");
        $this->commonJs();
        $this->parentWidget->addJs("(function(ch){let c;\n");
    }

    public function outputControl()
    {
        $ctrl = $this->ctrl;
        $attr = $this->getControlAttributes();
        $value = $this->getValue();
        $jFormsJsVarName = $this->builder->getjFormsJsVarName();

        $class = 'jforms-choice jforms-ctl-'.$ctrl->ref.' '.$this->ctrl->getAttribute('class');
        $this->displayStartChoice($attr['id'].'_choice_list', $class, '');

        if (is_array($value)) {
            if (isset($value[0])) {
                $value = $value[0];
            } else {
                $value = '';
            }
        }
        $value = (string) $value;

        $attrLabel = array();
        if (isset($attr['itemLabelClass'])) {
            $attrLabel['class'] = $attr['itemLabelClass'];
            unset($attr['itemLabelClass']);
        }

        $i = 0;
        $attr['name'] = $ctrl->ref;
        $id = $this->builder->getName().'_'.$ctrl->ref.'_';
        $attr['type'] = 'radio';
        unset($attr['class']);
        //$readonly = (isset($attr['readonly']) && $attr['readonly'] != ''); // FIXME: should be used?

        $this->jsChoiceInternal($ctrl);

        foreach ($ctrl->items as $itemName => $listctrl) {
            if (!$ctrl->isItemActivated($itemName)) {
                continue;
            }

            $attr['id'] = $id.$i;
            $attr['value'] = $itemName;
            $checked = false;
            if ((string) $itemName === $value) {
                $checked = true;
            }
            $attr['onclick'] = $jFormsJsVarName.'.getForm(\''.$this->builder->getName().'\').getControl(\''.$ctrl->ref.'\').activate(\''.$itemName.'\')';
            $this->displayStartChoiceItem(
                $id.$itemName.'_item',
                $attr,
                $checked,
                $ctrl->itemsNames[$itemName],
                $attrLabel
            );

            $displayedControls = false;
            foreach ($listctrl as $ref => $c) {
                if (!$this->builder->getForm()->isActivated($ref) || $c->type == 'hidden') {
                    continue;
                }
                $widget = $this->builder->getWidget($c, $this);
                $displayedControls = true;
                $this->displayControl($widget);
                $this->parentWidget->addJs('ch.addControl(c, '.$this->escJsStr($itemName).");\n");
            }
            if (!$displayedControls) {
                $this->parentWidget->addJs('ch.items['.$this->escJsStr($itemName)."]=[];\n");
            }

            $this->displayEndChoiceItem();
            ++$i;
        }
        $this->displayEndChoice();
        $this->parentWidget->addJs("ch.activate('".$value."');})(c);\n");
    }

    public function outputControlValue()
    {
        $ctrl = $this->ctrl;
        $attr = $this->getValueAttributes();
        $value = $this->getValue();

        if (is_array($value)) {
            if (isset($value[0])) {
                $value = $value[0];
            } else {
                $value = '';
            }
        }

        $attr['name'] = $ctrl->ref;

        if (!isset($ctrl->items[$value])) {
            if (!$ctrl->isItemActivated($value) || $ctrl->emptyValueLabel === null) {
                return;
            }
            $this->displayEmptyValue($attr, $ctrl->emptyValueLabel);

            return;
        }

        $listctrl = $ctrl->items[$value];
        $hasChildValues = count($listctrl) > 0;
        $this->displayValueLabel($ctrl->itemsNames[$value], $value, $hasChildValues);
        if ($hasChildValues) {
            $this->displayValueStartBlock();
            foreach ($listctrl as $ref => $c) {
                if (!$this->builder->getForm()->isActivated($ref) || $c->type == 'hidden') {
                    continue;
                }
                $widget = $this->builder->getWidget($c, $this);
                $this->displayControlValue($widget);
            }

            $this->displayValueEndBlock();
        }
    }

    protected function displayStartChoice($blockId, $class, $attrs)
    {
        echo '<ul class="'.$class.'" id="'.$blockId.'" '.$attrs.'>', "\n";
    }

    protected function displayStartChoiceItem($idItem, $attrRadio, $checked, $label, $attrLabel)
    {
        echo '<li id="'.$idItem.'"><input ';
        $this->_outputAttr($attrRadio);
        echo ' '.($checked ? 'checked' : '').'/><label for="'.$attrRadio['id'].'"';
        $this->_outputAttr($attrLabel);
        echo '>'.htmlspecialchars($label)."</label>\n";
    }

    /**
     * @param WidgetInterface $widget
     */
    protected function displayControl($widget)
    {
        echo ' <span class="jforms-item-controls">';
        $widget->outputLabel();
        echo ' ';
        $widget->outputControl();
        $widget->outputHelp();
        echo "</span>\n";
    }

    protected function displayEndChoiceItem()
    {
        echo "</li>\n";
    }

    protected function displayEndChoice()
    {
        echo "</ul>\n\n";
    }

    protected function displayEmptyValue($attr, $emptyLabel)
    {
        echo '<span ';
        $this->_outputAttr($attr);
        echo '>', htmlspecialchars($emptyLabel), '</span>';
    }

    protected function displayValueLabel($label, $value, $hasChildValues)
    {
        echo '<label>',htmlspecialchars($label),"</label>\n";
    }

    protected function displayValueStartBlock()
    {
        echo "<ul>\n";
    }

    /**
     * @param WidgetInterface $widget
     */
    protected function displayControlValue($widget)
    {
        echo '<li class="jforms-item-controls">';
        $widget->outputLabel('', false);
        echo ': ';
        $widget->outputControlValue();
        echo "</li>\n";
    }

    protected function displayValueEndBlock()
    {
        echo "</ul>\n";
    }
}
