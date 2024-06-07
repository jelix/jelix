<?php
/**
 * @package     jelix
 * @subpackage  jtpl_plugin
 *
 * @author      Laurent Jouanneau
 * @copyright   2006-2024 Laurent Jouanneau
 *
 * @see        http://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 *
 * @param mixed $compiler
 * @param mixed $begin
 * @param mixed $param
 */

use Jelix\Forms\Forms;

/**
 * a block to display only data of a form.
 *
 * usage : {formdata $theformobject} here the form content {/formdata}
 *
 * @param jTplCompiler $compiler the template compiler
 * @param bool         $begin    true if it is the begin of block, else false
 * @param array        $param    0=>form object
 * @param array        $param    0=>form object
 *                               2=>name of the builder : default is html
 *                               3=>array of options for the builder
 *
 * @return string the php code corresponding to the begin or end of the block
 *
 * @see Forms
 * @since 1.0.1
 */
function jtpl_block_html_formdata($compiler, $begin, $param = array())
{
    if (!$begin) {
        return '$t->_privateVars[\'__formTplController\']->endForm();
unset($t->_privateVars[\'__formTplController\']);';
    }

    if (count($param) < 1 || count($param) > 3) {
        $compiler->doError2('errors.tplplugin.block.bad.argument.number', 'formdata', '1-3');

        return '';
    }

    if (isset($param[1]) && trim($param[1]) != '""' && trim($param[1]) != "''") {
        $builder = $param[1];
    } else {
        $builder = "'".jApp::config()->tplplugins['defaultJformsBuilder']."'";
    }

    if (isset($param[2])) {
        $options = $param[2];
    } else {
        $options = 'array()';
    }
    return ' $t->_privateVars[\'__formTplController\'] = '.
        'new \\Jelix\\Forms\\HtmlWidget\\TemplateController('.$param[0].','.$builder.','.$options.');';

}
