<?php
/**
* @package      jelix
* @subpackage   jtpl_plugin
* @author       Laurent Jouanneau
* @contributor  Dominique Papin
* @copyright    2007-2008 Laurent Jouanneau, 2007 Dominique Papin
* @link         http://www.jelix.org
* @licence      GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
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
        $ctrl = $tpl->_privateVars['__ctrl'];
        $tpl->_privateVars['__displayed_ctrl'][$ctrlname] = true;
        $ctrlname = $tpl->_privateVars['__ctrlref'];
    }else{
        $ctrls = $tpl->_privateVars['__form']->getControls();
        $ctrl = $ctrls[$ctrlname];
    }

    if($ctrl->type == 'hidden' || $ctrl->type == 'captcha')
        return;

    if($ctrl->type == 'submit'  && ($ctrl->standalone || $insideForm))
        return;

    if($ctrl->type == 'reset' && $insideForm){
        return;
    }

    if(!$tpl->_privateVars['__form']->isActivated($ctrlname))
        return;

    $value = $tpl->_privateVars['__form']->getData($ctrlname);
    $value = $ctrl->getDisplayValue($value);
    if(is_array($value)){
        $s ='';
        foreach($value as $v){
            $s.=$sep.htmlspecialchars($v);
        }
        echo substr($s, strlen($sep));
    }elseif($ctrl->datatype instanceof jDatatypeHtml) {
        echo $value;
    }else
        echo htmlspecialchars($value);
}
