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

 class output_htmlFormWidget extends jFormsHtmlWidgetBuilder {
    function getHeader() { }

    function outputLabel() {
        $attr = $this->getLabelAttributes();

        echo '<span class="',$attr['class'],'"',$attr['idLabel'],$attr['hint'],'>';
        echo htmlspecialchars($this->ctrl->label), $attr['reqHtml'];
        echo "</span>\n";
    }

    function getJs() { /* no JS */ }

    function outputControl() {
        $ctrl = $this->ctrl;
        $formName = $this->builder->getName();
        $attr = $this->getControlAttributes();
        $value = $this->builder->getForm()->getData($ctrl->ref);
        
        unset($attr['readonly']);
        unset($attr['class']);
        if (isset($attr['title'])){
            $hint = ' title="'.htmlspecialchars($attr['title']).'"';
            unset($attr['title']);
        }
        else $hint = '';
        
        $attr['type'] = 'hidden';
        $attr['value'] = $value;
        echo '<input';
        $this->_outputAttr($attr);
        echo '/>';
        echo '<span class="jforms-value"',$hint,'>',htmlspecialchars($attr['value']),'</span>';
    }
}
