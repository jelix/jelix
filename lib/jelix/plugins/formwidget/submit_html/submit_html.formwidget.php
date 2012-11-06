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

class submit_htmlFormWidget extends jFormsHtmlWidgetBuilder {
    function getHeader() { }

    function outputLabel() { /* no label */ }

    function getJs() { /* no javascript */ }

    function outputHelp() { /* no help */ }

    function outputControl() {
        $attr = $this->getControlAttributes();
        
        unset($attr['readonly']);
        $attr['class'] = 'jforms-submit';
        $attr['type'] = 'submit';

        if($this->ctrl->standalone){
            $attr['value'] = $this->ctrl->label;
            echo '<input';
            $this->_outputAttr($attr);
            echo '/>';
        }else{
            $id = $this->builder->getName().'_'.$this->ctrl->ref.'_';
            $attr['name'] = $this->ctrl->ref;
            foreach($this->ctrl->datasource->getData($this->builder->getForm()) as $v=>$label){
                // because IE6 sucks with <button type=submit> (see ticket #431), we must use input :-(
                $attr['value'] = $label;
                $attr['id'] = $id.$v;
                echo ' <input';
                $this->_outputAttr($attr);
                echo '/>';
            }
        }
    }
}
