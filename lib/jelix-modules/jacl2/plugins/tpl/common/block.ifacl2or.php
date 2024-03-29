<?php
/**
 * @package     jelix
 * @subpackage  jacl2_plugin
 *
 * @author      Laurent Jouanneau
 *
 * @copyright   2022 Laurent Jouanneau
 *
 * @see         https://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

/**
 * a special if block to test easily if the user has a right among a list of rights.
 *
 * <pre>{ifacl2or 'right1', 'right2', 'right3'} ..here generated content if the user has one of given rights  {/ifacl2or}</pre>
 * <pre>{ifacl2or array('right1', 'right2', 'right3')} ..here generated content if the user has one of given rights  {/ifacl2or}</pre>
 *
 * @param jTplCompiler $compiler the template compiler
 * @param bool         $begin    true if it is the begin of block, else false
 * @param array        $param    0=>subject 1=>optional resource
 *
 * @return string the php code corresponding to the begin or end of the block
 */
function jtpl_block_common_ifacl2or($compiler, $begin, $param = array())
{
    if ($begin) {
        if (count($param) < 1) {
            $content = '';
            $compiler->doError2('errors.tplplugin.block.bad.argument.number', 'ifacl2or', 1);
        }
        else {
            if (is_array($param[0])) {
                $param = $param[0];
            }
            $test = array_map(function($right) {
                return 'jAcl2::check('.$right.')';
            }, $param);
            $content = ' if('.implode ('||', $test).'):';
        }
    } else {
        $content = ' endif; ';
    }

    return $content;
}
