<?php
/**
* @package     jelix
* @subpackage  jtpl_plugin
* @author      Jouanneau Laurent
* @copyright   2007 Jouanneau laurent
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

/**
 * a block to loop over controls list of a form and to display them
 *
 * usage : {formcontrols} here content to display one control {/formcontrols}
 * 
 * @param jTplCompiler $compiler the template compiler
 * @param boolean $begin true if it is the begin of block, else false
 * @param array $param array  empty or a jFormBase object as first item
 * @return string the php code corresponding to the begin or end of the block
 * @see jForms
 */
function jtpl_block_formcontrols($compiler, $begin, $param=array())
{

    if(!$begin){
        return '}';
    }

    if(count($param) > 1){
        $compiler->doError2('errors.tplplugin.block.bad.argument.number','formcontrols',1);
        return '';
    }
    if(count($param)){
        $content = ' $t->_privateVars[\'__form\'] = '.$param[0].'; ';
    }else{
        $content = '';
    }

    $content .= ' foreach($t->_privateVars[\'__form\']->getControls() as $ctrlref=>$ctrl){ 
$t->_privateVars[\'__ctrlref\'] = $ctrlref;
$t->_privateVars[\'__ctrl\'] = $ctrl;
';
    return $content;
}

?>