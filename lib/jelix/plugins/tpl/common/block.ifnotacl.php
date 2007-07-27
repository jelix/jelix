<?php
/**
* @package    jelix
* @subpackage jtpl_plugin
* @author     Jouanneau Laurent
* @copyright  2006-2007 Jouanneau laurent
* @link       http://www.jelix.org
* @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

/**
 * a special if block to test easily a right value
 *
 * <pre>{ifnotacl 'subject',54} ..here generated content if the user has NOT the right  {/ifnotacl}</pre>
 * @param jTplCompiler $compiler the template compiler
 * @param boolean true if it is the begin of block, else false
 * @param $params array  0=>subject 1=>right value 2=>optional resource
 * @return string the php code corresponding to the begin or end of the block
 */
function jtpl_block_common_ifnotacl($compiler, $begin, $params=array())
{
    if($begin){
        if(count($params) == 2){
            $content = ' if(!jAcl::check('.$params[0].','.$params[1].')){';
        }elseif(count($params) == 3){
            $content = ' if(!jAcl::check('.$params[0].','.$params[1].','.$params[2].')){';
        }else{
            $content='';
            $compiler->doError2('errors.tplplugin.block.bad.argument.number','ifnotacl',2);
        }
    }else{
        $content = ' } ';
    }
    return $content;
}

?>