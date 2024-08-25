<?php
/**
 * @package      jelix
 * @subpackage   jtpl_plugin
 *
 * @author       Laurent Jouanneau
 *
 * @copyright    2024 Laurent Jouanneau
 *
 * @see          https://www.jelix.org
 * @licence      GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 *
 */

/**
 * function plugin : assign the value of the current control into the given template variable
 *
 * @param jTpl   $tpl      template engine
 * @param string $varname  name of the template variable
 * @param string $ctrlname the name of the control from which we retrieve the value. Empty if current control of a loop.
 *
 * @throws jException
 */
function jtpl_function_html_ctrl_value_assign($tpl, $varname, $ctrlname = '', $insteadOfDisplay = null)
{
    if (!isset($tpl->_privateVars['__formTplController'])) {
        throw new Exception('Cannot display a control outside a form (template '.$tpl->_templateName.")");
    }
    $value = $tpl->_privateVars['__formTplController']->getControlValue($ctrlname, $tpl->_templateName, $insteadOfDisplay);
    $tpl->assign($varname, $value);
}
