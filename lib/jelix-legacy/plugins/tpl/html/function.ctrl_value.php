<?php
/**
 * @package      jelix
 * @subpackage   jtpl_plugin
 *
 * @author       Laurent Jouanneau
 * @contributor  Dominique Papin, Julien Issler
 *
 * @copyright    2007-2020 Laurent Jouanneau, 2007 Dominique Papin
 * @copyright    2008 Julien Issler
 *
 * @see         http://www.jelix.org
 * @licence      GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 *
 */

/**
 * function plugin :  print the value of a form control. You should use this plugin inside a formcontrols block.
 *
 * @param jTpl   $tpl      template engine
 * @param string $ctrlname the name of the control to display (required if it is outside a formcontrols)
 * @param string $sep      separator to display values of a multi-value control
 *
 * @throws jException
 */
function jtpl_function_html_ctrl_value($tpl, $ctrlname = '', $sep = ', ')
{
    if (!isset($tpl->_privateVars['__formTplController'])) {
        throw new Exception('Cannot display a control outside a form (template '.$tpl->_templateName.")");
    }

    $tpl->_privateVars['__formTplController']->outputControlValue($ctrlname, (is_array($sep) ? $sep : array('separator' => $sep)), $tpl->_templateName);
}
