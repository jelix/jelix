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
 */
function jtpl_function_html_ctrl_value($tpl, $sep =', ')
{
    $value = $tpl->_privateVars['__form']->getData($tpl->_privateVars['__ctrl']->ref);
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