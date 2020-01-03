<?php
/**
 * @package    jelix
 * @subpackage jtpl_plugin
 *
 * @author     Laurent Jouanneau
 * @contributor Loic Mathaud
 *
 * @copyright  2007-2020 Laurent Jouanneau, 2009 Loic Mathaud
 *
 * @see        http://www.jelix.org
 * @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

/**
 * function plugin :  print the html content of a form submit button. You can use this plugin inside a formsubmits block.
 *
 * @param jTpl   $tpl        template engine
 * @param string $ctrlname   the name of the submit to display (required if it is outside a formsubmits)
 * @param array  $attributes attributes for the generated html element
 *
 * @throws jException
 */
function jtpl_function_html_formsubmit($tpl, $ctrlname = '', $attributes = array())
{
    if (!isset($tpl->_privateVars['__formTplController'])) {
        throw new Exception('Cannot display a reset control outside a form (template '.$tpl->_templateName.")");
    }
    $tpl->_privateVars['__formTplController']->outputSubmit($ctrlname, $attributes, $tpl->_templateName);
}
