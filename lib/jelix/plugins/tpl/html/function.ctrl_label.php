<?php
/**
* @package    jelix
* @subpackage jtpl_plugin
* @author     Laurent Jouanneau
* @copyright  2007 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

/**
 * function plugin :  print the label of a form control. You should use this plugin inside a formcontrols block
 *
 * @param jTpl $tpl template engine
 * @param string $ctrlname  the name of the control to display (required if it is outside a formcontrols)
 */
function jtpl_function_html_ctrl_label($tpl, $ctrlname='')
{
    if( (!isset($tpl->_privateVars['__ctrlref']) || $tpl->_privateVars['__ctrlref'] == '') && $ctrlname =='') {
        return;
    }
    if($ctrlname =='') {
        $ctrl=$tpl->_privateVars['__ctrl'];
    }else{
        $ctrls = $tpl->_privateVars['__form']->getControls();
        $ctrl=$ctrls[$ctrlname];
    }

    if(isset($tpl->_privateVars['__formbuilder'])){
        if($ctrl->type == 'submit') return;
        $tpl->_privateVars['__formbuilder']->outputControlLabel($ctrl);
    }else
        echo htmlspecialchars($ctrl->label);
}

?>