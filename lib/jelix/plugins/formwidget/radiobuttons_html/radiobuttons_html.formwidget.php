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
require_once __DIR__.'/../checkboxes_html/checkboxes_html.formwidget.php';

/**
 * HTML form builder.
 *
 * @package     jelix
 * @subpackage  forms_widget_plugin
 *
 * @see http://developer.jelix.org/wiki/rfc/jforms-controls-plugins
 */
class radiobuttons_htmlFormWidget extends checkboxes_htmlFormWidget
{
    public function outputControl()
    {
        $attr = $this->getControlAttributes();
        $value = $this->getValue();

        $attr['name'] = $this->ctrl->ref;
        unset($attr['title']);
        if (is_array($value)) {
            if (isset($value[0])) {
                $value = $value[0];
            } else {
                $value = '';
            }
        }
        $value = (string) $value;
        $this->showRadioCheck($attr, $value, '');
        $this->outputJs($this->ctrl->ref);
    }

    protected function displayRadioCheckbox($attr, $label, $checked)
    {
        echo '<span class="jforms-radio jforms-ctl-'.$this->ctrl->ref.'"><input type="radio"';
        $this->_outputAttr($attr);
        if ($checked) {
            echo ' checked';
        }
        echo '/>','<label for="',$attr['id'],'">',htmlspecialchars($label),"</label></span> <br/>\n";
    }
}
