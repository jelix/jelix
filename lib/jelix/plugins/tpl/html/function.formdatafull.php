<?php
/**
* @package      jelix
* @subpackage   jtpl_plugin
* @author       Laurent Jouanneau
* @contributor  Dominique Papin
* @copyright    2007-2008 Laurent Jouanneau, 2007 Dominique Papin
* @link         http://www.jelix.org
* @licence      GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

/**
 * Display all data of a form without the use of other plugins.
 *
 * @param jTpl $tpl template engine
 * @param jFormsBase $form  the form to display
 */
function jtpl_function_html_formdatafull($tpl, $form)
{
    echo '<table class="jforms-table" border="0">';

    foreach( $form->getControls() as $ctrlref=>$ctrl){
        if($ctrl->type == 'submit' || $ctrl->type == 'reset' || $ctrl->type == 'hidden' || $ctrl->type == 'captcha') continue;
        if(!$form->isActivated($ctrlref)) continue;

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
        }elseif($ctrl->datatype instanceof jDatatypeHtml) {
            echo $value;
        }else
            echo htmlspecialchars($value);

        echo '</td></tr>';
    }
    echo '</table>';
}
