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
 * Display a full form without the use of other plugins.
 *
 * @param jTpl $tpl template engine
 * @param jFormsBase $form  the form to display
 * @param string $action selector of submit action
 * @param array $params parameters for submit action
 * @param string $errDecorator name of your javascript object for error listener
 * @param string $helpDecorator name of your javascript object for help listener
 */
function jtpl_function_html_formfull($tpl, $form, $action, $params=array(), $errDecorator='jFormsErrorDecoratorAlert', $helpDecorator='jFormsHelpDecoratorAlert')
{

    $formBuilder = $form->getBuilder('html', $action, $params);
    $formBuilder->outputHeader(array($errDecorator, $helpDecorator));

    if($GLOBALS['gJCoord']->response!= null){
        $GLOBALS['gJCoord']->response->addJSLink($GLOBALS['gJConfig']->urlengine['jelixWWWPath'].'js/jforms.js');
        $GLOBALS['gJCoord']->response->addCSSLink($GLOBALS['gJConfig']->urlengine['jelixWWWPath'].'design/jform.css');
    }

    echo '<table class="jforms-table" border="0">';


    foreach( $form->getControls() as $ctrlref=>$ctrl){
        if($ctrl->type == 'submit') continue;

        echo '<tr><th scope="row">';
        $formBuilder->outputControlLabel($ctrl);
        echo '</th><td>';
        $formBuilder->outputControl($ctrl);
        echo '</td></tr>';

    }

    echo '</table> <div class="jforms-submit-buttons">';
    foreach( $form->getSubmits() as $ctrlref=>$ctrl){
        $formBuilder->outputControl($ctrl);
        echo ' ';
    }
    echo '</div>';
    $formBuilder->outputFooter();
}

?>