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

class listbox_htmlFormWidget extends jFormsHtmlWidgetBuilder {
    function getJs() {
        $js = '';
        $ctrl = $this->ctrl;
        $jFormsJsVarName = $this->builder->getjFormsJsVarName();
        
        if($ctrl->multiple){
            $js .= "c = new ".$jFormsJsVarName."ControlString('".$ctrl->ref."[]', ".$this->escJsStr($ctrl->label).");\n";
            $js .= "c.multiple = true;\n";
        } else {
            $js .= "c = new ".$jFormsJsVarName."ControlString('".$ctrl->ref."', ".$this->escJsStr($ctrl->label).");\n";
        }
        $js .= $this->commonJs($ctrl);

        return $js;
    }
    
    function outputControl() {
        $ctrl = $this->ctrl;
        $formName = $this->builder->getName();
        $attr = $this->getControlAttributes();
        $value = $this->builder->getForm()->getData($ctrl->ref);

        unset($attr['readonly']);
        $attr['size'] = $ctrl->size;

        if($ctrl->multiple){
            $attr['name'] = $ctrl->ref.'[]';
            $attr['id'] = $this->_name.'_'.$ctrl->ref;
            $attr['multiple'] = 'multiple';
            echo '<select';
            $this->_outputAttr($attr);
            echo ">\n";
            if($ctrl->emptyItemLabel !== null)
                echo '<option value=""',(in_array('',$value,true)?' selected="selected"':''),'>',htmlspecialchars($ctrl->emptyItemLabel),"</option>\n";
            if(is_array($value) && count($value) == 1)
                $value = $value[0];

            if(is_array($value)){
                $value = array_map(function($v){ return (string) $v;},$value);
                $this->fillSelect($ctrl, $value);
            }else{
                $this->fillSelect($ctrl, (string)$value);
            }
            echo '</select>';
        }else{
            if(is_array($value)){
                if(count($value) >= 1)
                    $value = $value[0];
                else
                    $value ='';
            }

            $value = (string) $value;
            echo '<select';
            $this->_outputAttr($attr);
            echo ">\n";
            if($ctrl->emptyItemLabel !== null)
                echo '<option value=""',($value===''?' selected="selected"':''),'>',htmlspecialchars($ctrl->emptyItemLabel),"</option>\n";
            $this->fillSelect($ctrl, $value);
            echo '</select>';
        }

    }
}
