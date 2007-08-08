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
 * function plugin :  print the html content of a form control. You should use this plugin inside a formcontrols block
 *
 * @param jTpl $tpl template engine
 * @param string $ctrlname  the name of the control to display (required if it is outside a formcontrols)
 */
function jtpl_function_html_ctrl_control($tpl, $ctrlname='')
{
    if($tpl->_privateVars['__ctrlref'] == '' && $ctrlname =='') {
        return;
    }
    if($ctrlname =='') {
        $tpl->_privateVars['__displayed_ctrl'][$tpl->_privateVars['__ctrlref']] = true;
        $tpl->_privateVars['__formbuilder']->outputControl($tpl->_privateVars['__ctrl']);
    }else{
        $tpl->_privateVars['__displayed_ctrl'][$ctrlname] = true;
        $ctrls = $tpl->_privateVars['__form']->getControls();
        $tpl->_privateVars['__formbuilder']->outputControl($ctrls[$ctrlname]);
    }
}

?>