<?php
/**
* @package     jelix
* @subpackage  formwidgets
* @author      Claudio Bernardes
* @contributor Laurent Jouanneau, Julien Issler, Dominique Papin
* @copyright   2012 Claudio Bernardes
* @copyright   2006-2012 Laurent Jouanneau, 2008-2011 Julien Issler, 2008 Dominique Papin
* @link        http://www.jelix.org
* @licence     http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*/

/**
 * HTML form builder
 * @package     jelix
 * @subpackage  jelix-plugins
 * @link http://developer.jelix.org/wiki/rfc/jforms-controls-plugins
 */

class htmleditor_htmlFormWidget extends \jelix\forms\HtmlWidget\WidgetBase {
    function outputJs() {
        $ctrl = $this->ctrl;
        $formName = $this->builder->getName();
        $jFormsJsVarName = $this->builder->getjFormsJsVarName();
        
        $js ="c = new ".$jFormsJsVarName."ControlHtml('".$ctrl->ref."', ".$this->escJsStr($ctrl->label).");\n";

        $maxl= $ctrl->datatype->getFacet('maxLength');
        if($maxl !== null)
            $js .="c.maxLength = '$maxl';\n";

        $minl= $ctrl->datatype->getFacet('minLength');
        if($minl !== null)
            $js .="c.minLength = '$minl';\n";

        $this->builder->jsContent .= $js;
        $this->commonJs($ctrl);

        $engine = jApp::config()->htmleditors[$ctrl->config.'.engine.name'];
        $this->builder->jsContent .= 'jelix_'.$engine.'_'.$ctrl->config.'("'.$formName.'_'.$ctrl->ref.'","'.$formName.'","'.$ctrl->skin."\",".$jFormsJsVarName.".config);\n";
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
