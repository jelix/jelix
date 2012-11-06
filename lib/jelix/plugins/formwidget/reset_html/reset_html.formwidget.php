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

class reset_htmlFormWidget extends jFormsHtmlWidgetBuilder {
    function getHeader() { }

    function outputLabel() { /* no label */ }

    function getJs() { /* no javascript */ }

    function outputControl() {
        $attr = $this->getControlAttributes();
        
        unset($attr['readonly']);
        $attr['class'] = 'jforms-reset';
        $attr['type'] = 'reset';
        echo '<button';
        $this->_outputAttr($attr);
        echo '>',htmlspecialchars($this->ctrl->label),'</button>';
    }

    function outputHelp() {}

}
