<?php
/**
 * @package     jelix
 * @subpackage  forms_widget_plugin
 *
 * @author       Laurent Jouanneau <laurent@jelix.org>
 * @copyright    2023 Laurent Jouanneau
 *
 * @link         https://jelix.org
 * @licence      http://www.gnu.org/licenses/gpl.html GNU General Public Licence, see LICENCE file
 */

/**
 */
class password_htmlFormWidget extends \Jelix\Forms\HtmlWidget\WidgetBase
{
    public function outputMetaContent($resp)
    {
        $JelixWWWPath = jApp::urlJelixWWWPath();
        $resp->addJSLink($JelixWWWPath.'js/jforms/password-editor.js');
    }

    protected function outputJs($id)
    {
        $ctrl = $this->ctrl;
        $jFormsJsVarName = $this->builder->getjFormsJsVarName();

        $js = 'c = new '.$jFormsJsVarName."ControlSecret('".$ctrl->ref."', ".$this->escJsStr($ctrl->label).");\n";

        $maxl = $ctrl->datatype->getFacet('maxLength');
        if ($maxl !== null) {
            $js .= "c.maxLength = '{$maxl}';\n";
        }

        $minl = $ctrl->datatype->getFacet('minLength');
        if ($minl !== null) {
            $js .= "c.minLength = '{$minl}';\n";
        }
        $re = $ctrl->datatype->getFacet('pattern');
        if ($re !== null) {
            $js .= 'c.regexp = '.$re.";\n";
        }

        $js .= $this->customWidgetJs();

        $js .= "JelixPasswordEditor.initSimple(document.getElementById('".$id."').parentNode, c);\n";
        $this->parentWidget->addJs($js);
        $this->commonJs();
    }

    protected function customWidgetJs()
    {
        $js = "c.toggleEyeDesign = function(btnEye, showStatus) {
        let img = btnEye.querySelector('img');
        if (showStatus == 'show') {
            img.setAttribute('src', img.dataset.srcHide);
        }
        else {
            img.setAttribute('src', img.dataset.srcShow);
        }
        };\n";
        return $js;
    }

    public function outputControl()
    {
        $attr = $this->getControlAttributes();

        if ($this->ctrl->size != 0) {
            $attr['size'] = $this->ctrl->size;
        }
        $maxl = $this->ctrl->datatype->getFacet('maxLength');
        if ($maxl !== null) {
            $attr['maxlength'] = $maxl;
        }
        $minl = $this->ctrl->datatype->getFacet('minLength');
        if ($minl !== null) {
            $attr['minlength'] = $minl;
        }
        $attr['type'] = 'password';
        $attr['value'] = $this->getValue();

        $btnLabels = array(
            'showLabel' =>   jLocale::get('jelix~jforms.password.editor.button.show'),
        );

        $this->displayInput($attr, $btnLabels);
        $this->outputJs($attr['id']);
    }

    protected function displayInput(&$attr, $btnLabels)
    {
        echo '<div class="jforms-password-editor"><input';
        $this->_outputAttr($attr);
        echo "/>\n";

        $designPath = jApp::urlJelixWWWPath().'design/icons8/';
        $urlshow = $designPath.'icons8-eye-24.png';
        $urlhide = $designPath.'icons8-invisible-24.png';
        extract($btnLabels);

        echo <<<EOHTML
            <div class="jforms-password-buttons">
                <button type="button" class="btn btn-outline-secondary jforms-password-toggle-visibility" title="$showLabel">
                    <img src="$urlshow"  data-src-show="$urlshow"  data-src-hide="$urlhide" alt="$showLabel" width="15" class="jforms-password-visibility"/>
                </button>
            </div>
        </div>

EOHTML;
    }
}
