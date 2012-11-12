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

class button_htmlFormWidget extends jFormsHtmlWidgetBuilder {
    function outputControl() {
        $attr = $this->getControlAttributes();
        
        unset($attr['readonly']); //readonly is useless on button
        unset($attr['class']); //no class on button

        $attr['value'] = $this->getValue($this->ctrl);
        echo '<button ';
        $this->_outputAttr($attr);
        echo '>',htmlspecialchars($this->ctrl->label),'</button>';
    }
}
