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

class checkbox_htmlFormWidget extends jFormsHtmlWidgetBuilder {
    function outputJs() {
        $js = "c = new ".$this->builder->getjFormsJsVarName()."ControlBoolean('".$this->ctrl->ref."', ".$this->escJsStr($this->ctrl->label).");\n";
        $this->builder->jsContent .= $js;
        $this->commonJs($this->ctrl);
    }

    function outputControl() {
        $attr = $this->getControlAttributes();

        if($this->ctrl->valueOnCheck == $this->getValue($this->ctrl)){
            $attr['checked'] = "checked";
        }
        $attr['value'] = $this->ctrl->valueOnCheck;
        $attr['type'] = 'checkbox';
        echo '<input';
        $this->_outputAttr($attr);
        echo '/>';
    }
}
