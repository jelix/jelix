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

class choice_htmlFormWidget extends jFormsHtmlWidgetBuilder {
    protected $_js;
    
    function getJs() {
        $ctrl = $this->ctrl;
        $jFormsJsVarName = $this->builder->getjFormsJsVarName();
        $value = $this->builder->getForm()->getData($ctrl->ref);

        if(is_array($value)){
            if(isset($value[0]))
                $value = $value[0];
            else
                $value='';
        }
        
        $this->_js .= "c2.activate('".$value."');\n";
        return $this->_js;
    }

    protected function jsChoiceInternal($ctrl) {
        $ctrl = $this->ctrl;
        $jFormsJsVarName = $this->builder->getjFormsJsVarName();

        $this->_js = "c = new ".$jFormsJsVarName."ControlChoice('".$ctrl->ref."', ".$this->escJsStr($ctrl->label).");\n";
        $this->_js .= $this->commonJs($ctrl);
    }

    function outputControl() {
        $ctrl = $this->ctrl;
        $formName = $this->builder->getName();
        $attr = $this->getControlAttributes();
        $value = $this->builder->getForm()->getData($ctrl->ref);
        $jFormsJsVarName = $this->builder->getjFormsJsVarName();

        echo '<ul class="jforms-choice jforms-ctl-'.$ctrl->ref.'" >',"\n";
        if(is_array($value)){
            if(isset($value[0]))
                $value = $value[0];
            else
                $value='';
        }

        $i=0;
        $attr['name'] = $ctrl->ref;
        $id = $this->builder->getName().'_'.$ctrl->ref.'_';
        $attr['type']='radio';
        unset($attr['class']);
        $readonly = (isset($attr['readonly']) && $attr['readonly']!='');

        $this->jsChoiceInternal($ctrl);
        $this->_js .="c2 = c;\n";

        $this->isRootControl = false;
        foreach( $ctrl->items as $itemName=>$listctrl){
            if (!$ctrl->isItemActivated($itemName))
                continue;
            echo '<li><label><input';
            $attr['id'] = $id.$i;
            $attr['value'] = $itemName;
            if ($itemName==$value)
                $attr['checked'] = 'checked';
            else
                unset($attr['checked']);
            $this->_outputAttr($attr);
            echo ' onclick="'.$jFormsJsVarName.'.getForm(\'',$this->builder->getName(),'\').getControl(\'',$ctrl->ref,'\').activate(\'',$itemName,'\')"', '/>';
            echo htmlspecialchars($ctrl->itemsNames[$itemName]),"</label>\n";

            $displayedControls = false;
            foreach($listctrl as $ref=>$c) {
                if(!$this->builder->getForm()->isActivated($ref) || $c->type == 'hidden') continue;
                $displayedControls = true;
                echo ' <span class="jforms-item-controls">';
                $this->builder->outputControlLabel($c);
                echo ' ';
                $this->builder->outputControl($c);
                echo "</span>\n";
                $this->_js .="c2.addControl(c, ".$this->escJsStr($itemName).");\n";
            }
            if(!$displayedControls) {
                $this->_js .="c2.items[".$this->escJsStr($itemName)."]=[];\n";
            }

            echo "</li>\n";
            $i++;
        }
        echo "</ul>\n";
        $this->isRootControl = true;
    }

}
