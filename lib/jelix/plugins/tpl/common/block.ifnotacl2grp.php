<?php
/**
* @package     jelix
* @subpackage  jtpl_plugin
* @author      Laurent Jouanneau
* @contributor Dominique Papin
* @contributor Philippe Villiers
* @copyright   2006-2008 Laurent Jouanneau
* @copyright   2007 Dominique Papin
* @copyright   2012 Philippe Villiers
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

/**
 * a special if block to test easily a group value
 *
 * <pre>{ifnotacl2grp 'group_id'} ..here generated content if the user is NOT in the given group  {/ifnotacl2grp}</pre>
 * @param jTplCompiler $compiler the template compiler
 * @param boolean $begin true if it is the begin of block, else false
 * @param array $params 0=>group_id
 * @return string the php code corresponding to the begin or end of the block
 */
function jtpl_block_common_ifnotacl2grp($compiler, $begin, $params=array())
{
    if($begin){
        if(count($param) == 1){
            $content = ' if(!jAcl2DbUserGroup::isMemberOfGroup('.$param[0].')):';
        }else{
            $content='';
            $compiler->doError2('errors.tplplugin.block.bad.argument.number','ifnotacl2grp',1);
        }
    }else{
        $content = ' endif; ';
    }
    return $content;
}

