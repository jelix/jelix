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
 * It accept also some parameters
 * 1) an optional jFormsBase object if the {formcontrols} is outside a {form} block
 * 2) an optional array of control names : only these controls will be displayed
 *
 * @param jTplCompiler $compiler the template compiler
 * @param boolean $begin true if it is the begin of block, else false
 * @param array $param empty array
 *                     or 0=>jFormsBase object 
 *                     or 0=>jFormsBase object, 1=>array of control names
 *                     or 0=>array of control names
 * @return string the php code corresponding to the begin or end of the block
 * @see jForms
 */
function jtpl_block_html_formcontrols($compiler, $begin, $param=array())
{

    if(!$begin){
        return '}} $t->_privateVars[\'__ctrlref\']=\'\';'; // if, foreach
    }

    if(count($param) > 2){
        $compiler->doError2('errors.tplplugin.block.bad.argument.number','formcontrols',2);
        return '';
    }
    if(count($param)){
        if(count($param) == 1){
            $content = 'if(is_array('.$param[0].')){
                $ctrls_to_display = '.$param[0].';
            }
            else {
                $t->_privateVars[\'__form\'] = '.$param[0].';
                $ctrls_to_display=null;
            }';
        }
        else{
            $content = ' $t->_privateVars[\'__form\'] = '.$param[0].";\n";
            $content .= ' $ctrls_to_display = '.$param[1].'; ';
        }
    }else{
        $content = '$ctrls_to_display=null;';
    }

    $content .= '
if (!isset($t->_privateVars[\'__displayed_ctrl\'])) {
    $t->_privateVars[\'__displayed_ctrl\'] = array();
}
$t->_privateVars[\'__ctrlref\']=\'\';
foreach($t->_privateVars[\'__form\']->getControls() as $ctrlref=>$ctrl){ 
    if($ctrl->type == \'submit\' && isset($t->_privateVars[\'__formbuilder\'])) continue;
    if(!isset($t->_privateVars[\'__displayed_ctrl\'][$ctrlref]) && ( $ctrls_to_display===null || in_array($ctrlref, $ctrls_to_display))){
        $t->_privateVars[\'__ctrlref\'] = $ctrlref;
        $t->_privateVars[\'__ctrl\'] = $ctrl;
';
    return $content;
}

?>
