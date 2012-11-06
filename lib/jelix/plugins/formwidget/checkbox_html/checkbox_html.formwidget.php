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

class checkboxFormWidget extends jFormsHtmlWidgetBuilder {
    function outputLabel() {
        $attr = $this->getLabelAttributes();

        echo '<label class="',$attr['class'],'" for="',$this->getId(),'"',$attr['idLabel'],$attr['hint'],'>';
        echo htmlspecialchars($this->ctrl->label), $attr['reqHtml'];
        echo "</label>\n";
    }

    function getJs() {
        $js = "c = new ".$this->builder->getjFormsJsVarName()."ControlBoolean('".$this->ctrl->ref."', ".$this->escJsStr($this->ctrl->label).");\n";
        $js .= $this->commonJs($this->ctrl);

        return $js;
    }

    function outputControl() {
        $ctrl = $this->ctrl;
        $attr = $this->getControlAttributes();
        
        $value = $this->builder->getForm()->getData($ctrl->ref);

        if($ctrl->valueOnCheck == $value){
            $attr['checked'] = "checked";
         }
        $attr['value'] = $ctrl->valueOnCheck;
        $attr['type'] = 'checkbox';
        echo '<input';
        $this->_outputAttr($attr);
        echo '/>';
    }
}
