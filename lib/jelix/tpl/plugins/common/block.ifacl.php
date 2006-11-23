<?php
/**
* @package    jelix
* @subpackage jtpl_plugin
* @version    $Id$
* @author     Jouanneau Laurent
* @copyright   2006 Jouanneau laurent
* @link        http://www.jelix.org
* @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

/**
 * a special if block to test easily a right value
 *
 * usage : {ifacl 'subject',54} ..here generated content if the user has the right  {/ifacl}
 * @param jTplCompiler $compiler the template compiler
 * @param boolean true if it is the begin of block, else false
 * @param $param array  1=>subject 2=>right value 3=>optional resource
 * @return string the php code corresponding to the begin or end of the block
 */
function jtpl_block_ifacl($compiler, $begin, $param=array())
{
    if($begin){
        if(count($param) == 2){
            $content = ' if(jAcl::check('.$param[0].','.$param[1].')){';
        }elseif(count($param) == 3){
            $content = ' if(jAcl::check('.$param[0].','.$param[1].','.$param[2].')){';
        }else{
            $content='';
            $compiler->doError2('errors.tplplugin.block.bad.argument.number','ifacl',2);
        }
    }else{
        $content = ' } ';
    }
    return $content;
}

?>