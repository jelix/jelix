<?php
/**
* @package      jelix
* @subpackage   jtpl_plugin
* @author       Laurent Jouanneau
* @contributor  Dominique Papin, Julien Issler, Bastien Jaillot
* @copyright    2007-2008 Laurent Jouanneau, 2007 Dominique Papin
* @copyright    2008 Julien Issler, 2008 Bastien Jaillot
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
 *                     6=>name of the builder : default is html
 * @return string the php code corresponding to the begin or end of the block
 */
function jtpl_cfunction_html_formfull($compiler, $params=array())
{
    if (count($params) < 2 || count($params) > 7) {
        $compiler->doError2('errors.tplplugin.cfunction.bad.argument.number','formfull','2-7');
    }

    if(isset($params[6]) && $params[6] != '""'  && $params[6] != "''") 
        $builder = $params[6];
    else 
        $builder = "'html'";

    $compiler->addMetaContent('if(isset('.$params[0].')) { '.$params[0].'->getBuilder('.$builder.')->outputMetaContent($t);}');

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

    $method = isset($params[5])?$params[5]:'\'post\'';

    $content = ' $formfull = '.$params[0].';
    $formfullBuilder = $formfull->getBuilder('.$builder.');
    $formfullBuilder->setAction('.$params[1].','.$params[2].');
    $formfullBuilder->outputHeader(array('.$errdecorator.','.$helpdecorator.','.$method.'));
    $formfullBuilder->outputAllControls();
    $formfullBuilder->outputFooter();';

    return $content;
}

