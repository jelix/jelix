<?php
/**
 * @package     jelix
 * @subpackage  jtpl_plugin
 *
 * @author      Laurent Jouanneau
 * @copyright   2024 Laurent Jouanneau
 *
 * @see          https://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

/**
 * a special if block to check if a ctrl is activated in the form
 * TO BE USED inside a `{form}` or `{formadata}` block.
 *
 * {ifctrlactivated 'name1'} some tpl {else} some other tpl {/ifctrlactivated}
 *
 * @param jTplCompiler $compiler the template compiler
 * @param bool         $begin    true if it is the begin of block, else false
 * @param array        $params   0=>'name', to match against current control name
 *
 * @return string the php code corresponding to the begin or end of the block
 */
function jtpl_block_html_ifctrlactivated($compiler, $begin, $params = array())
{
    if ($begin) {
        if (count($params) > 1) {
            $content = '';
            $compiler->doError1('errors.tplplugin.block.bad.argument.number', 'ifctrlactivated', '1');
        } else if (count($params)) {
            $content = ' if ($t->_privateVars[\'__formTplController\']->isControlActivated('.$params[0].')):';
        } else {
            $content = ' if ($t->_privateVars[\'__formTplController\']->isControlActivated()):';
        }
    } else {
        $content = ' endif; ';
    }

    return $content;
}
