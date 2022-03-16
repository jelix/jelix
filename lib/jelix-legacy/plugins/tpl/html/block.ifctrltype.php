<?php
/**
 * @package     jelix
 * @subpackage  jtpl_plugin
 *
 * @author      Dominique Papin
 * @author      Laurent Jouanneau
 * @copyright   2008 Dominique Papin
 * @copyright   2020 Laurent Jouanneau
 *
 * @see        https://jelix.org
 * @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

/**
 * a special if block to test easily the current control type
 * TO BE USED inside a {formcontrols} block.
 *
 * {ifctrltype 'type1','type2',...} some tpl {else} some other tpl {/ifctrltype}
 *
 * @param jTplCompiler $compiler the template compiler
 * @param bool         $begin    true if it is the begin of block, else false
 * @param array        $params   0=>'type',etc. to match against current control type
 *
 * @return string the php code corresponding to the begin or end of the block
 */
function jtpl_block_html_ifctrltype($compiler, $begin, $params = array())
{
    if ($begin) {
        if (count($params) == 0) {
            $content = '';
            $compiler->doError2('errors.tplplugin.block.bad.argument.number', 'ifctrltype', '1+');
        } else {
            $content = ' if(isset($t->_privateVars[\'__ctrl\'])&&(';
            foreach ($params as $ctrltype) {
                $content .= '$t->_privateVars[\'__ctrl\']->type=='.$ctrltype.' || ';
            }
            $content = substr($content, 0, -4); // -4 == size of the last ' || '
            $content .= ')):';
        }
    } else {
        $content = ' endif; ';
    }

    return $content;
}
