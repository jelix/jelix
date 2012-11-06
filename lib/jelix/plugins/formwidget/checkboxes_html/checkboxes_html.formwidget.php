<?php
/**
* @package     jelix
* @subpackage  forms
* @author      Claudio Bernardes
* @copyright   2012 Claudio Bernardes
* @link        http://www.jelix.org
* @licence     http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*/

/**
 * HTML form builder
 * @package     jelix
 * @subpackage  jelix-plugins
 * @link http://developer.jelix.org/wiki/rfc/jforms-controls-plugins
 */

class checkboxes_htmlFormWidget extends jFormsHtmlWidgetBuilder {
    function getJs() {
        $ctrl = $this->ctrl;
        $jFormsJsVarName = $this->builder->getjFormsJsVarName();

        $js = "c = new ".$jFormsJsVarName."ControlString('".$ctrl->ref."[]', ".$this->escJsStr($ctrl->label).");\n";
        $js .= $this->commonJs($ctrl);

        return $js;
    }
    
    function outputControl() {
        $ctrl = $this->ctrl;
        $formName = $this->builder->getName();
        $attr = $this->getControlAttributes();
        $value = $this->builder->getForm()->getData($ctrl->ref);

        $attr['name'] = $ctrl->ref.'[]';
        unset($attr['title']);
        if(is_array($value) && count($value) == 1)
            $value = $value[0];
        $span ='<span class="jforms-chkbox jforms-ctl-'.$ctrl->ref.'"><input type="checkbox"';

        if(is_array($value)){
            $value = array_map(function($v){ return (string) $v;},$value);
        }
        else {
            $value = (string) $value;
        }
        $this->showRadioCheck($ctrl, $attr, $value, $span);
    }
}
