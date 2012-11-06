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

class secretconfirm_htmlFormWidget extends jFormsHtmlWidgetBuilder {
    function getJs() {
        $ctrl = $this->ctrl;
        $jFormsJsVarName = $this->builder->getjFormsJsVarName();

        $js  = "c = new ".$jFormsJsVarName."ControlConfirm('".$ctrl->ref."', ".$this->escJsStr($ctrl->label).");\n";
        $js .= $this->commonJs($ctrl);

        return $js;
    }

    function outputControl() {
        $ctrl = $this->ctrl;
        $formName = $this->builder->getName();
        $attr = $this->getControlAttributes();
        $value = $this->builder->getForm()->getData($ctrl->ref);

        if ($ctrl->size != 0)
            $attr['size'] = $ctrl->size;
        $attr['type'] = 'password';
        $attr['value'] = $value;
        echo '<input';
        $this->_outputAttr($attr);
        echo '/>';

    }
}
