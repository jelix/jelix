<?php
/**
* @package     jelix
* @subpackage  formwidgets
* @author      Laurent Jouanneau
* @copyright   2014 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*/

/**
 * display a textarea form control as an input html
 */
class textarea_as_input_htmlFormWidget extends \Jelix\Forms\HtmlWidget\WidgetBase {
    protected function outputJs() {
        $ctrl = $this->ctrl;
        $jFormsJsVarName = $this->builder->getjFormsJsVarName();
        
        $js ="c = new ".$jFormsJsVarName."ControlString('".$ctrl->ref."', ".$this->escJsStr($ctrl->label).");\n";

        $maxl= $ctrl->datatype->getFacet('maxLength');
        if($maxl !== null)
            $js .="c.maxLength = '$maxl';\n";

        $minl= $ctrl->datatype->getFacet('minLength');
        if($minl !== null)
            $js .="c.minLength = '$minl';\n";

        $this->parentWidget->addJs($js);
        $this->commonJs();
    }

    function outputControl() {
        $attr = $this->getControlAttributes();
        $maxl= $this->ctrl->datatype->getFacet('maxLength');
        if($maxl !== null)
            $attr['maxlength']=$maxl;
        $attr['value'] = htmlspecialchars($this->getValue($this->ctrl));
        $attr['type'] = 'text';

        echo '<input';
        $this->_outputAttr($attr);
        echo "/>\n";
        $this->outputJs();
    }

    public function outputControlValue(){
        $attr = $this->getValueAttributes();
        echo '<div ';
        $this->_outputAttr($attr);
        echo '>';
        $value = $this->getValue();
        $value = $this->ctrl->getDisplayValue($value);
        if ($this->ctrl->isHtmlContent())
            echo $value;
        else
            echo nl2br(htmlspecialchars($value));

        echo '</div>';
    }
}
