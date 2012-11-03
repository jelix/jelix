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

 /*
c = new jFormsJQControlString('wikicontent', 'a wiki editor');
c.errInvalid='"a wiki editor" field is invalid';
jFormsJQ.tForm.addControl(c);
$("#jforms_testapp_sample_wikicontent").markItUp(markitup_wr3_settings);


<div><label id="jforms_testapp_sample_wikicontent_label" for="jforms_testapp_sample_wikicontent" class="jforms-label">a wiki editor</label>
: <div><div class="markItUp" id="markItUpJforms_testapp_sample_wikicontent"><div class="markItUpContainer"><div class="markItUpHeader">
<ul><li class="markItUpButton markItUpButton1 "><a title="Titre 3 [Ctrl+3]" accesskey="3" href="">Titre 3</a></li><li class="markItUpButton markItUpButton2 "><a title="Titre 4 [Ctrl+4]" accesskey="4" href="">Titre 4</a></li><li class="markItUpButton markItUpButton3 "><a title="Titre 5 [Ctrl+5]" accesskey="5" href="">Titre 5</a></li><li class="markItUpSeparator">---------------</li><li class="markItUpButton markItUpButton4 "><a title="Strong [Ctrl+B]" accesskey="B" href="">Strong</a></li><li class="markItUpButton markItUpButton5 "><a title="Italic [Ctrl+I]" accesskey="I" href="">Italic</a></li><li class="markItUpSeparator">---------------</li><li class="markItUpButton markItUpButton6 "><a title="List" href="">List</a></li><li class="markItUpButton markItUpButton7 "><a title="Numbered List" href="">Numbered List</a></li><li class="markItUpSeparator">---------------</li><li class="markItUpButton markItUpButton8 "><a title="Image [Ctrl+P]" accesskey="P" href="">Image</a></li><li class="markItUpButton markItUpButton9 "><a title="Link [Ctrl+L]" accesskey="L" href="">Link</a></li><li class="markItUpButton markItUpButton10 "><a title="Complete Link" href="">Complete Link</a></li><li class="markItUpSeparator">---------------</li><li class="markItUpButton markItUpButton11 "><a title="Quote" href="">Quote</a></li><li class="markItUpButton markItUpButton12 "><a title="Code" href="">Code</a></li><li class="markItUpButton markItUpButton13 "><a title="Block of source code" href="">Block of source code</a></li><li class="markItUpSeparator">---------------</li><li class="markItUpButton markItUpButton14 "><a title="New line" href="">New line</a></li><li class="markItUpSeparator">---------------</li><li class="markItUpButton markItUpButton15 preview"><a title="Preview" href="">Preview</a></li></ul></div>

<textarea cols="40" rows="5" class="jforms-ctrl-wikieditor markItUpEditor" id="jforms_testapp_sample_wikicontent" name="wikicontent"></textarea>

<div class="markItUpFooter"><div class="markItUpResizeHandle"></div></div></div></div></div>
</div>

<div><label id="jforms_testapp_sample_wikicontent_label" for="jforms_testapp_sample_wikicontent" class="jforms-label">a wiki editor</label>
: <textarea cols="40" rows="5" class="jforms-ctrl-wikieditor" id="jforms_testapp_sample_wikicontent" name="wikicontent"></textarea></div>
 */

class wikieditorFormWidget extends jFormsHtmlWidgetBuilder {
    function outputLabel() {
        $attr = $this->getLabelAttributes();

        echo '<label class="',$attr['class'],'" for="',$this->getId(),'"',$attr['idLabel'],$attr['hint'],'>';
        echo htmlspecialchars($this->ctrl->label), $attr['reqHtml'];
        echo "</label>\n";
    }

    function getJs() {
        $ctrl = $this->ctrl;
        $formName = $this->builder->getName();
        $jFormsJsVarName = $this->builder->getjFormsJsVarName();
        
        $js ="c = new ".$jFormsJsVarName."ControlString('".$ctrl->ref."', ".$this->escJsStr($ctrl->label).");\n";

        $maxl= $ctrl->datatype->getFacet('maxLength');
        if($maxl !== null)
            $js .="c.maxLength = '$maxl';\n";

        $minl= $ctrl->datatype->getFacet('minLength');
        if($minl !== null)
            $js .="c.minLength = '$minl';\n";

        $js .= $this->commonJs($ctrl);

        $engine = jApp::config()->wikieditors[$ctrl->config.'.engine.name'];
        $js .= '$("#'.$formName.'_'.$ctrl->ref.'").markItUp(markitup_'.$engine.'_settings);'."\n";

        return $js;
    }

    function outputControl() {
        $ctrl = $this->ctrl;
        $attr = $this->getControlAttributes();
        $value = $this->builder->getForm()->getData($ctrl->ref);

        if (!isset($attr['rows']))
            $attr['rows'] = $ctrl->rows;
        if (!isset($attr['cols']))
            $attr['cols'] = $ctrl->cols;

        echo '<textarea';
        $this->_outputAttr($attr);
        echo '>',htmlspecialchars($value),'</textarea>';
    }
}
