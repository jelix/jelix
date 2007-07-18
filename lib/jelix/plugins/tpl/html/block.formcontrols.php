<?php
/**
* @package     jelix
* @subpackage  jtpl_plugin
* @author      Jouanneau Laurent
* @contributor Mickaël Fradin
* @copyright   2007 Jouanneau laurent, 2007 Mickaël Fradin
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
 * @param array $param array empty or a jFormsBase object as first item or/and an array of controls to display as first/second item (depending if jFormsBase object is given or not)
 * @return string the php code corresponding to the begin or end of the block
 * @see jForms
 */
function jtpl_block_formcontrols($compiler, $begin, $param=array())
{

    if(!$begin){
        return '}} unset($t->_privateVars[\'__to_display_ctrl\']);';
    }

    if(count($param) > 2){
        $compiler->doError2('errors.tplplugin.block.bad.argument.number','formcontrols',2);
        return '';
    }
    if(count($param)){
        if(count($param) == 1){
            $content = 'if(is_array('.$param[0].')){
                $t->_privateVars[\'__to_display_ctrl\'] = '.$param[0].';
            }
            else {
                $t->_privateVars[\'__form\'] = '.$param[0].'; 
            }';
        }
        else{
            $content = ' $t->_privateVars[\'__form\'] = '.$param[0].'; ';
            $content .= ' $t->_privateVars[\'__to_display_ctrl\'] = '.$param[1].'; ';
        }
    }else{
        $content = '';
    }

    $content .= ' if (!isset($t->_privateVars[\'__displayed_ctrl\'])) {$t->_privateVars[\'__displayed_ctrl\'] = array();} ';
    $content .= ' foreach($t->_privateVars[\'__form\']->getControls() as $ctrlref=>$ctrl){ 
if(!isset($t->_privateVars[\'__displayed_ctrl\'][$ctrlref]) 
    && (!isset($t->_privateVars[\'__to_display_ctrl\']) 
        || in_array($ctrlref, $t->_privateVars[\'__to_display_ctrl\']))){
    $t->_privateVars[\'__ctrlref\'] = $ctrlref;
    $t->_privateVars[\'__ctrl\'] = $ctrl;
    $t->_privateVars[\'__displayed_ctrl\'][$ctrlref] = true;
';
    return $content;
}

?>
