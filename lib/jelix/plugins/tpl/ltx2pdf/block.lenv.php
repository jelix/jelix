<?php
/**
* @package    jelix
* @subpackage jtpl_plugin
* @author     Aubanel MONNIER
* @copyright  2007 Aubanel MONNIER
* @link       http://www.jelix.org
* @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

/**
 * a special to insert latex content
 *
 * usage : {lenv 'latex_block_name'} ..here latex content {/lenv}
 * @param jTplCompiler $compiler the template compiler
 * @param boolean true if it is the begin of block, else false
 * @param $param array  1=>latex command
 * @return string the php code corresponding to the begin or end of the block
 */
function jtpl_block_ltx2pdf_lenv($compiler, $begin, $param=array())
{
    static $stack=array();
    if ($begin){
        array_push($stack,$param[0]);
        return 'echo \'\\begin{'.$param[0].'}\';';
    }else 
        return 'echo \'\\end{'.array_pop($stack).'}\';';
}


?>
