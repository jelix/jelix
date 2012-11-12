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

class upload_htmlFormWidget extends jFormsHtmlWidgetBuilder {
    function outputJs() {
        $ctrl = $this->ctrl;
        $jFormsJsVarName = $this->builder->getjFormsJsVarName();
        
        $this->builder->jsContent .="c = new ".$jFormsJsVarName."ControlString('".$ctrl->ref."', ".$this->escJsStr($ctrl->label).");\n";
        $this->commonJs($ctrl);
    }

    function outputControl() {
        $attr = $this->getControlAttributes();

        /*if($this->ctrl->maxsize){
            echo '<input type="hidden" name="MAX_FILE_SIZE" value="',$this->ctrl->maxsize,'"','/>';
        }*/
        $attr['type'] = 'file';
        $attr['value'] = '';
        echo '<input';
        $this->_outputAttr($attr);
        echo '/>';
    }
}
