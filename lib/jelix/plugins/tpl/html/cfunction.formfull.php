<?php
/**
* @package      jelix
* @subpackage   jtpl_plugin
* @author       Laurent Jouanneau
* @contributor  Dominique Papin
* @copyright    2007 Laurent Jouanneau, 2007 Dominique Papin
* @link         http://www.jelix.org
* @licence      GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

/**
 * Display a full form without the use of other plugins.
 * usage : {formfull $theformobject,'submit_action', $submit_action_params}
 * You can add this others parameters :
 *   string $errDecorator name of your javascript object for error listener<br/>
 *   string $helpDecorator name of your javascript object for help listener<br/>
 *   string $method : the method of submit : 'post' or 'get'
 *
 * @param jTplCompiler $compiler the template compiler
 * @param array $params 0=>form object
 *                     1=>selector of submit action
 *                     2=>array of parameters for submit action
 *                     3=>name of your javascript object for error listener
 *                     4=>name of your javascript object for help listener
 *                     5=>name of the method : POST or GET
 * @return string the php code corresponding to the begin or end of the block
 */
function jtpl_cfunction_html_formfull($compiler, $params=array())
{
    if (count($params) < 2 || count($params) > 6) {
        $compiler->doError2('errors.tplplugin.cfunction.bad.argument.number','formfull','2-6');
    }

    $compiler->addMetaContent('if($GLOBALS[\'gJCoord\']->response!= null){
        $GLOBALS[\'gJCoord\']->response->addJSLink($GLOBALS[\'gJConfig\']->urlengine[\'jelixWWWPath\'].\'js/jforms.js\');
        $GLOBALS[\'gJCoord\']->response->addCSSLink($GLOBALS[\'gJConfig\']->urlengine[\'jelixWWWPath\'].\'design/jform.css\');
    }
    ');

    if(count($params) == 2){
        $params[2] = 'array()';
    }
    if(isset($params[3]) && $params[3] != '""'  && $params[3] != "''")
        $errdecorator = $params[3];
    else
        $errdecorator = "'jFormsErrorDecoratorAlert'";

    if(isset($params[4]) && $params[4] != '""'  && $params[4] != "''")
        $helpdecorator = $params[4];
    else
        $helpdecorator = "'jFormsHelpDecoratorAlert'";

    $method = strtolower(isset($params[5])?$params[5]:'post');
    if($method!='get' && $method!='post')
        $method='post';

    $content = ' $formfull = '.$params[0].';
    $formfullBuilder = $formfull->getBuilder(\'html\', '.$params[1].','.$params[2].');
    $formfullBuilder->outputHeader(array('.$errdecorator.','.$helpdecorator.',\''.$method.'\'));
    echo \'<table class="jforms-table" border="0">\';

    foreach( $formfull->getControls() as $ctrlref=>$ctrl){
        if($ctrl->type == \'submit\' || $ctrl->type == \'reset\') continue;
        echo \'<tr><th scope="row">\';
        $formfullBuilder->outputControlLabel($ctrl);
        echo \'</th><td>\';
        $formfullBuilder->outputControl($ctrl);
        echo \'</td></tr>\';
    }
    echo \'</table> <div class="jforms-submit-buttons">\';
    if ( $ctrl = $formfull->getReset() )
        $formfullBuilder->outputControl($ctrl);
    foreach( $formfull->getSubmits() as $ctrlref=>$ctrl){
        $formfullBuilder->outputControl($ctrl);
        echo \' \';
    }
    echo \'</div>\';
    $formfullBuilder->outputFooter();';

    return $content;
}

?>