<?php
/**
 * @package     jelix
 * @subpackage  forms_widget_plugin
 *
 * @author      Claudio Bernardes
 * @contributor Laurent Jouanneau, Julien Issler, Dominique Papin
 *
 * @copyright   2012 Claudio Bernardes
 * @copyright   2006-2012 Laurent Jouanneau, 2008-2011 Julien Issler, 2008 Dominique Papin
 *
 * @see        http://www.jelix.org
 * @licence     http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */

/**
 * HTML form builder.
 *
 * @package     jelix
 * @subpackage  forms_widget_plugin
 *
 * @see http://developer.jelix.org/wiki/rfc/jforms-controls-plugins
 */
class wikieditor_htmlFormWidget extends \Jelix\Forms\HtmlWidget\WidgetBase
{
    public function outputMetaContent($resp)
    {
        $resp->addAssets('jforms_wikieditor_'.$this->ctrl->config);
    }

    protected function outputJs()
    {
        $ctrl = $this->ctrl;
        $formName = $this->builder->getName();
        $jFormsJsVarName = $this->builder->getjFormsJsVarName();

        $js = 'c = new '.$jFormsJsVarName."ControlString('".$ctrl->ref."', ".$this->escJsStr($ctrl->label).");\n";

        $maxl = $ctrl->datatype->getFacet('maxLength');
        if ($maxl !== null) {
            $js .= "c.maxLength = '{$maxl}';\n";
        }

        $minl = $ctrl->datatype->getFacet('minLength');
        if ($minl !== null) {
            $js .= "c.minLength = '{$minl}';\n";
        }
        $this->parentWidget->addJs($js);

        $this->commonJs();

        $engine = jApp::config()->wikieditors[$ctrl->config.'.engine.name'];
        $this->parentWidget->addJs('$("#'.$formName.'_'.$ctrl->ref.'").markItUp(markitup_'.$engine.'_settings);'."\n");
    }

    public function outputControl()
    {
        $attr = $this->getControlAttributes();
        $value = $this->getValue();

        if (!isset($attr['rows'])) {
            $attr['rows'] = $this->ctrl->rows;
        }
        if (!isset($attr['cols'])) {
            $attr['cols'] = $this->ctrl->cols;
        }
        if ($this->ctrl->placeholder != '') {
            $attr['placeholder'] = $this->ctrl->placeholder;
        }

        echo '<textarea';
        $this->_outputAttr($attr);
        echo '>',htmlspecialchars($value),"</textarea>\n";
        $this->outputJs();
    }

    public function outputControlValue()
    {
        $attr = $this->getValueAttributes();
        echo '<div ';
        $this->_outputAttr($attr);
        echo '>';
        $value = $this->getValue();
        $value = $this->ctrl->getDisplayValue($value);
        echo $value,'</div>';
    }
}
