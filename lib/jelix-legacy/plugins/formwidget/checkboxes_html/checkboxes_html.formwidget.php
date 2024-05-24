<?php
/**
 * @package     jelix
 * @subpackage  forms_widget_plugin
 *
 * @author      Claudio Bernardes
 * @contributor Laurent Jouanneau, Julien Issler, Dominique Papin
 *
 * @copyright   2012 Claudio Bernardes
 * @copyright   2006-2012 Laurent Jouanneau, 2008-2011 Julien Issler, 2008 Dominique Papin
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
class checkboxes_htmlFormWidget extends \Jelix\Forms\HtmlWidget\WidgetBase
{
    protected function outputJs($refName)
    {
        $jFormsJsVarName = $this->builder->getjFormsJsVarName();

        $this->parentWidget->addJs('c = new '.$jFormsJsVarName."ControlString('".$refName."', ".$this->escJsStr($this->ctrl->label).");\n");
        $this->commonJs();
    }

    public function outputControl()
    {
        $attr = $this->getControlAttributes();
        $value = $this->getValue();

        $attr['name'] = $this->ctrl->ref.'[]';
        unset($attr['title']);
        if (is_array($value) && count($value) == 1) {
            $value = $value[0];
        }

        if (is_array($value)) {
            $value = array_map(function ($v) { return (string) $v; }, $value);
        } else {
            $value = (string) $value;
        }
        $this->showRadioCheck($attr, $value, '');
        $this->outputJs($this->ctrl->ref.'[]');
    }

    /**
     * @param $attr
     * @param $value
     * @param string $span is deprecated
     */
    protected function showRadioCheck(&$attr, &$value, $span)
    {
        $id = $this->builder->getName().'_'.$this->ctrl->ref.'_';
        $i = 0;
        $data = $this->ctrl->datasource->getData($this->builder->getForm());
        if ( ($this->ctrl->datasource instanceof \Jelix\Forms\Datasource\DatasourceInterface
            || $this->ctrl->datasource instanceof \jIFormsDatasource2)
            && $this->ctrl->datasource->hasGroupedData()
        ) {
            if (isset($data[''])) {
                $this->echoCheckboxes($span, $id, $data[''], $attr, $value, $i);
            }
            foreach ($data as $group => $values) {
                if ($group === '') {
                    continue;
                }
                $this->displayStartRadioCheckboxesGroup($group);
                $this->echoCheckboxes($span, $id, $values, $attr, $value, $i);
                $this->displayEndRadioCheckboxesGroup();
            }
            echo "\n";
        } else {
            $this->echoCheckboxes($span, $id, $data, $attr, $value, $i);
            echo "\n";
        }
    }

    protected function echoCheckboxes($span, $id, &$values, &$attr, &$value, &$i)
    {
        foreach ($values as $v => $label) {
            $attr['id'] = $id.$i;
            $attr['value'] = $v;
            $checked = (is_array($value) && in_array((string) $v, $value, true)) || ($value === (string) $v);
            $this->displayRadioCheckbox($attr, $label, $checked);
            ++$i;
        }
    }

    protected function displayStartRadioCheckboxesGroup($groupName)
    {
        echo '<fieldset><legend>'.htmlspecialchars($groupName).'</legend>'."\n";
    }

    protected function displayRadioCheckbox($attr, $label, $checked)
    {
        echo '<span class="jforms-chkbox jforms-ctl-'.$this->ctrl->ref.'"><input type="checkbox"';
        $this->_outputAttr($attr);
        if ($checked) {
            echo ' checked';
        }
        echo '/>','<label for="',$attr['id'],'">',htmlspecialchars($label),"</label></span> <br/>\n";
    }

    protected function displayEndRadioCheckboxesGroup()
    {
        echo "</fieldset>\n";
    }
}
