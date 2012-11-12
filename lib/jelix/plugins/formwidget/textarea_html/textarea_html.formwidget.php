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

class textarea_htmlFormWidget extends jFormsHtmlWidgetBuilder {
    function outputJs() {
        $ctrl = $this->ctrl;
        $jFormsJsVarName = $this->builder->getjFormsJsVarName();
        
        $js ="c = new ".$jFormsJsVarName."ControlString('".$ctrl->ref."', ".$this->escJsStr($ctrl->label).");\n";

        $maxl= $ctrl->datatype->getFacet('maxLength');
        if($maxl !== null)
            $js .="c.maxLength = '$maxl';\n";

        $minl= $ctrl->datatype->getFacet('minLength');
        if($minl !== null)
            $js .="c.minLength = '$minl';\n";

        $this->builder->jsContent .= $js;
        $this->commonJs($ctrl);
    }

    function outputControl() {
        $attr = $this->getControlAttributes();
        $value = $this->getValue($this->ctrl);

        if (!isset($attr['rows']))
            $attr['rows'] = $this->ctrl->rows;
        if (!isset($attr['cols']))
            $attr['cols'] = $this->ctrl->cols;

        echo '<textarea';
        $this->_outputAttr($attr);
        echo '>',htmlspecialchars($value),'</textarea>';
    }
}
