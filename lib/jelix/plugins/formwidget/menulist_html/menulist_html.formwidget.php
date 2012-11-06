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

class menulist_htmlFormWidget extends jFormsHtmlWidgetBuilder {
    function getJs() {
        $ctrl = $this->ctrl;
        $jFormsJsVarName = $this->builder->getjFormsJsVarName();

        $js = "c = new ".$jFormsJsVarName."ControlString('".$ctrl->ref."', ".$this->escJsStr($ctrl->label).");\n";
        if ($ctrl instanceof jFormsControlDatasource
            && $ctrl->datasource instanceof jFormsDaoDatasource) {
            $dependentControls = $ctrl->datasource->getDependentControls();
            if ($dependentControls) {
                $js .="c.dependencies = ['".implode("','",$dependentControls)."'];\n";
            }
        }
        $js .= $this->commonJs($ctrl);

        return $js;
    }

    function getLastJs() {
        $ctrl = $this->ctrl;

        if ($ctrl instanceof jFormsControlDatasource
            && $ctrl->datasource instanceof jFormsDaoDatasource) {
            $dependentControls = $ctrl->datasource->getDependentControls();
            if ($dependentControls) {
                return "jFormsJQ.tForm.declareDynamicFill('".$ctrl->ref."');\n";
            }
        }
    }
    
    function outputControl() {
        $ctrl = $this->ctrl;
        $formName = $this->builder->getName();
        $attr = $this->getControlAttributes();
        $value = $this->builder->getForm()->getData($ctrl->ref);

        unset($attr['readonly']);
        $attr['size'] = '1';
        echo '<select';
        $this->_outputAttr($attr);
        echo ">\n";

        if(is_array($value)){
            if(isset($value[0]))
                $value = $value[0];
            else
                $value='';
        }
        $value = (string) $value;
        echo '<option value=""',($value===''?' selected="selected"':''),'>',htmlspecialchars($ctrl->emptyItemLabel),"</option>\n";
        $this->fillSelect($ctrl, $value);
        echo '</select>';
    }
}
