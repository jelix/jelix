<?php
/**
 * @package      jelix
 * @subpackage   jtpl_plugin
 *
 * @author       Laurent Jouanneau
 * @contributor  Dominique Papin
 *
 * @copyright    2007-2020 Laurent Jouanneau, 2007 Dominique Papin
 *
 * @see         http://www.jelix.org
 * @licence      GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

/**
 * function plugin :  print the html content of a form control. You should use this plugin inside a formcontrols block.
 *
 * @param jTpl   $tpl        template engine
 * @param string $ctrlname   the name of the control to display (required if it is outside a formcontrols)
 * @param array  $attributes attribute to add on the generated code (html attributes for example)
 *
 * @throws jException
 */
function jtpl_function_html_ctrl_control($tpl, $ctrlname = '', $attributes = array())
{
    if (!isset($tpl->_privateVars['__formTplController'])) {
        throw new Exception('Cannot display a control outside a form (template '.$tpl->_templateName.")");
    }

    $tpl->_privateVars['__formTplController']->outputControl($ctrlname, $attributes, $tpl->_templateName);
}
