<?php
/**
* @package     jelix
* @subpackage  formwidgets
* @author      Claudio Bernardes
* @contributor Laurent Jouanneau, Julien Issler, Dominique Papin
* @copyright   2012 Claudio Bernardes
* @copyright   2006-2012 Laurent Jouanneau, 2008-2011 Julien Issler, 2008 Dominique Papin
* @link        http://www.jelix.org
* @licence     http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*/

/**
 * HTML form builder
 * @package     jelix
 * @subpackage  jelix-plugins
 * @link http://developer.jelix.org/wiki/rfc/jforms-controls-plugins
 */

class checkboxes_htmlFormWidget extends \jelix\forms\HtmlWidget\WidgetBase {
    protected function outputJs() {
        $ctrl = $this->ctrl;
        $jFormsJsVarName = $this->builder->getjFormsJsVarName();

        $this->parentWidget->addJs("c = new ".$jFormsJsVarName."ControlString('".$ctrl->ref."[]', ".$this->escJsStr($ctrl->label).");\n");
        $this->commonJs($ctrl);
    }
    
    function outputControl() {
        $attr = $this->getControlAttributes();
        $value = $this->getValue($this->ctrl);

        $attr['name'] = $this->ctrl->ref.'[]';
        unset($attr['title']);
        if(is_array($value) && count($value) == 1)
            $value = $value[0];
        $span ='<span class="jforms-chkbox jforms-ctl-'.$this->ctrl->ref.'"><input type="checkbox"';

        if(is_array($value)){
            $value = array_map(function($v){ return (string) $v;},$value);
        }
        else {
            $value = (string) $value;
        }
        $this->showRadioCheck($this->ctrl, $attr, $value, $span);
        $this->outputJs();
    }
}
