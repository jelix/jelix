<?php
/**
* @package    jelix
* @subpackage jtpl_plugin
* @author     Laurent Jouanneau
* @copyright  2007 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

/**
 * Display all datas of a form without the use of other plugins.
 *
 * @param jTpl $tpl template engine
 * @param jFormsBase $form  the form to display
 */
function jtpl_function_html_formdatasfull($tpl, $form)
{

    //$formBuilder = $form->getBuilder('html', $action, $params);

    echo '<table class="jforms-table" border="0">';


    foreach( $form->getControls() as $ctrlref=>$ctrl){
        if($ctrl->type == 'submit') continue;

        echo '<tr><th scope="row">';
        echo htmlspecialchars($ctrl->label);
        echo '</th><td>';
        $value = $ctrl->getDisplayValue($form->getData($ctrlref));
        if(is_array($value)){
            $s ='';
            foreach($value as $v){
                $s.=','.htmlspecialchars($v);
            }
            echo substr($s, 1);
        }else
            echo htmlspecialchars($value);

        echo '</td></tr>';

    }

    echo '</table>';
}

?>