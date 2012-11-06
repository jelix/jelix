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

class input_htmlFormWidget extends jFormsHtmlWidgetBuilder {
    function getHeader() { }

    function outputLabel() {
        $attr = $this->getLabelAttributes();

        echo '<label class="',$attr['class'],'" for="',$this->getId(),'"',$attr['idLabel'],$attr['hint'],'>';
        echo htmlspecialchars($this->ctrl->label), $attr['reqHtml'];
        echo "</label>\n";
    }

    function getJs() {
        $js = '';
        $ctrl = $this->ctrl;
        $jFormsJsVarName = $this->builder->getjFormsJsVarName();

        $datatype = array('jDatatypeBoolean'=>'Boolean','jDatatypeDecimal'=>'Decimal','jDatatypeInteger'=>'Integer','jDatatypeHexadecimal'=>'Hexadecimal',
                        'jDatatypeDateTime'=>'Datetime','jDatatypeDate'=>'Date','jDatatypeTime'=>'Time',
                        'jDatatypeUrl'=>'Url','jDatatypeEmail'=>'Email','jDatatypeIPv4'=>'Ipv4','jDatatypeIPv6'=>'Ipv6');
        $isLocale = false;
        $data_type_class = get_class($ctrl->datatype);
        if(isset($datatype[$data_type_class]))
            $dt = $datatype[$data_type_class];
        else if ($ctrl->datatype instanceof jDatatypeLocaleTime)
            { $dt = 'Time'; $isLocale = true; }
        else if ($ctrl->datatype instanceof jDatatypeLocaleDate)
            { $dt = 'LocaleDate'; $isLocale = true; }
        else if ($ctrl->datatype instanceof jDatatypeLocaleDateTime)
            { $dt = 'LocaleDatetime'; $isLocale = true; }
        else
            $dt = 'String';

        $js .="c = new ".$jFormsJsVarName."Control".$dt."('".$ctrl->ref."', ".$this->escJsStr($ctrl->label).");\n";
        if ($isLocale)
            $js .="c.lang='".jApp::config()->locale."';\n";

        $maxl= $ctrl->datatype->getFacet('maxLength');
        if($maxl !== null)
            $js .="c.maxLength = '$maxl';\n";

        $minl= $ctrl->datatype->getFacet('minLength');
        if($minl !== null)
            $js .="c.minLength = '$minl';\n";
        $re = $ctrl->datatype->getFacet('pattern');
        if($re !== null)
            $js .="c.regexp = ".$re.";\n";

        $js .= $this->commonJs($ctrl);
        return $js;
    }

    function outputControl() {
        $ctrl = $this->ctrl;
        $attr = $this->getControlAttributes();
        $value = $this->builder->getForm()->getData($ctrl->ref);

        if ($ctrl->size != 0)
            $attr['size'] = $ctrl->size;
        $maxl= $ctrl->datatype->getFacet('maxLength');
        if($maxl !== null)
            $attr['maxlength']=$maxl;
        $attr['value'] = $value;
        $attr['type'] = 'text';

        echo '<input';
        $this->_outputAttr($attr);
        echo '/>';
    }
}
