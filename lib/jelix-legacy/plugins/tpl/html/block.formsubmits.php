<?php
/**
 * @package     jelix
 * @subpackage  jtpl_plugin
 *
 * @author      Laurent Jouanneau
 * @contributor Mickaël Fradin
 *
 * @copyright   2007-2024 Laurent Jouanneau, 2007 Mickaël Fradin
 *
 * @see        http://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

use Jelix\Forms\Forms;

/**
 * a block to loop over submit button list of a form and to display them.
 *
 * usage : {formsubmits} here content to display one submit {/formsubmits}
 * It accept also some parameters
 * 1) an optional FormInstance object if the {formsubmits} is outside a {form} block
 * 2) an optional array of submit control names : only these controls will be displayed
 *
 * @param jTplCompiler $compiler the template compiler
 * @param bool         $begin    true if it is the begin of block, else false
 * @param array        $param    empty array
 *                               or 0=>FormInstance object
 *                               or 0=>FormInstance object, 1=>array of submit names
 *                               or 0=>array of submit names
 *
 * @return string the php code corresponding to the begin or end of the block
 *
 * @see Forms
 */
function jtpl_block_html_formsubmits($compiler, $begin, $param = array())
{
    if (!$begin) {
        return '}'."\n".' $t->_privateVars[\'__submitref\']=\'\';'; // foreach
    }

    if (count($param) > 2) {
        $compiler->doError2('errors.tplplugin.block.bad.argument.number', 'formsubmits', 2);

        return '';
    }
    if (count($param)) {
        if (count($param) == 1) {
            $content = 'if(is_array('.$param[0].')){
                $submits_to_display = '.$param[0].';
            }
            else {
                $form = '.$param[0].';
                $submits_to_display=null;
            }';
        } else {
            $content = ' $form = '.$param[0].";\n";
            $content .= ' $submits_to_display = '.$param[1].'; ';
        }
    } else {
        $content = '$form =null; $submits_to_display=null;';
    }

    $content .= '
if (!isset($t->_privateVars[\'__formTplController\'])) {
    if ($form === null) { throw new \Exception("Error: form is missing to process formsubmits"); }
    $t->_privateVars[\'__formTplController\'] = new \\Jelix\\Forms\\HtmlWidget\\TemplateController($form,"html");
}
';
    $content .= '
foreach($t->_privateVars[\'__formTplController\']->submitsLoop($submits_to_display) as $ctrl) { 
';

    return $content;
}
