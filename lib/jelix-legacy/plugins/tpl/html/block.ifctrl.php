<?php
/**
 * @package     jelix
 * @subpackage  jtpl_plugin
 *
 * @author      Dominique Papin
 * @contributor Laurent Jouanneau
 * @copyright   2008 Dominique Papin, 2008-2020 Laurent Jouanneau
 *
 * @see        http://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

/**
 * a special if block to test easily the current control name
 * TO BE USED inside a {formcontrols} block.
 *
 * {ifctrl 'name1','name2',...} some tpl {else} some other tpl {/ifctrl}
 *
 * @param jTplCompiler $compiler the template compiler
 * @param bool         $begin    true if it is the begin of block, else false
 * @param array        $params   0=>'name',etc. to match against current control name
 *
 * @return string the php code corresponding to the begin or end of the block
 */
function jtpl_block_html_ifctrl($compiler, $begin, $params = array())
{
    if ($begin) {
        if (count($params) == 0) {
            $content = '';
            $compiler->doError1('errors.tplplugin.block.bad.argument.number', 'ifctrl', '1+');
        } else {
            $content = ' if($t->_privateVars[\'__formTplController\']->isCurrentControl(';
            $content .= implode(',', $params);
            $content .= ')):';
        }
    } else {
        $content = ' endif; ';
    }

    return $content;
}
