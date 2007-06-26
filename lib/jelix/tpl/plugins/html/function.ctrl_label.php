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
 */
function jtpl_function_ctrl_label($tpl)
{
    if(isset($tpl->_privateVars['__builder']))
        $tpl->_privateVars['__builder']->outputControlLabel($tpl->_privateVars['__ctrl']);
    else
        echo htmlspecialchars($tpl->_privateVars['__ctrl']->label);
}

?>