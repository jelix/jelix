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
 * function plugin :  print the value of a form control. You should use this plugin inside a formcontrols block
 *
 * @param jTpl $tpl template engine
 * @param string $ctrlname  the name of the control to display (required if it is outside a formcontrols)
 * @param string $sep  separator to display values of a multi-value control
 */
function jtpl_function_html_ctrl_value($tpl, $ctrlname='', $sep =', '){

    if( (!isset($tpl->_privateVars['__ctrlref']) || $tpl->_privateVars['__ctrlref'] == '') && $ctrlname =='') {
        return;
    }
    $insideForm = isset($tpl->_privateVars['__formbuilder']);

    if($ctrlname =='') {
        if($tpl->_privateVars['__ctrl']->type == 'submit' && ($tpl->_privateVars['__ctrl']->standalone || $insideForm)){
            return;
        }
        $tpl->_privateVars['__displayed_ctrl'][$ctrlname] = true;
        $ctrl = $tpl->_privateVars['__ctrl'];
        $ctrlname = $tpl->_privateVars['__ctrlref'];
    }else{
        $ctrls = $tpl->_privateVars['__form']->getControls();
        if($ctrls[$ctrlname]->type == 'submit' && ($ctrls[$ctrlname]->standalone || $insideForm)){
            return;
        }
        $ctrl = $ctrls[$ctrlname];
    }

    $value = $tpl->_privateVars['__form']->getData($ctrlname);
    $value = $ctrl->getDisplayValue($value);
    if(is_array($value)){
        $s ='';
        foreach($value as $v){
            $s.=$sep.htmlspecialchars($v);
        }
        echo substr($s, strlen($sep));
    }else
        echo htmlspecialchars($value);
}

?>