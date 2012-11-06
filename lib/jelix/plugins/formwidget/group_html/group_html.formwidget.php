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

class group_htmlFormWidget extends jFormsHtmlWidgetBuilder {
    function getJs() { /* no js */ }
    
    function outputControl() {
        $ctrl = $this->ctrl;
        $formName = $this->builder->getName();
        $attr = $this->getControlAttributes();
        $value = $this->builder->getForm()->getData($ctrl->ref);

        echo '<fieldset id="',$attr['id'],'"><legend>',htmlspecialchars($ctrl->label),"</legend>\n";
        echo '<table class="jforms-table-group" border="0">',"\n";
        foreach( $ctrl->getChildControls() as $ctrlref=>$c){
            if($c->type == 'submit' || $c->type == 'reset' || $c->type == 'hidden') continue;
            if(!$this->builder->getForm()->isActivated($ctrlref)) continue;
            echo '<tr><th scope="row">';
            $this->builder->outputControlLabel($c);
            echo "</th>\n<td>";
            $this->builder->outputControl($c);
            echo "</td></tr>\n";
        }
        echo "</table></fieldset>";
    }
}
