<?php
/**
 * @package    jelix
 * @subpackage jtpl_plugin
 *
 * @author     Dominique Papin
 * @contributor Laurent Jouanneau
 * @copyright  2007 Dominique Papin, 2008-2020 Laurent Jouanneau
 *
 * @see        http://www.jelix.org
 * @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

/**
 * function plugin :  print the html content of a form reset button.
 *
 * @param jTpl $tpl template engine
 */
function jtpl_function_html_formreset($tpl)
{
    if (!isset($tpl->_privateVars['__formTplController'])) {
        throw new Exception('Cannot display a reset control outside a form (template '.$tpl->_templateName.")");
    }
    $tpl->_privateVars['__formTplController']->outputReset();
}
