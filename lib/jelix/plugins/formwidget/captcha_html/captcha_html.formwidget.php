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

class captcha_htmlFormWidget extends jFormsHtmlWidgetBuilder {
    function getHeader() { }

    function outputLabel() {
        $attr = $this->getLabelAttributes();

        echo '<label class="',$attr['class'],'" for="',$this->getId(),'"',$attr['idLabel'],$attr['hint'],'>';
        echo htmlspecialchars($this->ctrl->label), $attr['reqHtml'];
        echo "</label>\n";
    }

    function getJs() {
        $ctrl = $this->ctrl;
        $jFormsJsVarName = $this->builder->getjFormsJsVarName();
        
        $js ="c = new ".$jFormsJsVarName."ControlString('".$ctrl->ref."', ".$this->escJsStr($ctrl->label).");\n";

        $maxl= $ctrl->datatype->getFacet('maxLength');
        if($maxl !== null)
            $js .="c.maxLength = '$maxl';\n";

        $minl= $ctrl->datatype->getFacet('minLength');
        if($minl !== null)
            $js .="c.minLength = '$minl';\n";

        $js .= $this->commonJs($ctrl);
        
        return $js;
    }

    function outputControl() {
        $ctrl = $this->ctrl;
        $attr = $this->getControlAttributes();
        $value = $this->builder->getForm()->getData($ctrl->ref);

        $ctrl->initExpectedValue();
        echo '<span class="jforms-captcha-question">',htmlspecialchars($ctrl->question),'</span> ';

        unset($attr['readonly']);
        $attr['type'] = 'text';
        $attr['value'] = '';
        echo '<input';
        $this->_outputAttr($attr);
        echo '/>';
    }
}
