<?php
/**
* @package    jelix
* @subpackage template plugins
* @version    $Id$
* @author     Jouanneau Laurent
* @contributor
* @copyright   2006 Jouanneau laurent
* @link        http://www.jelix.org
* @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/


/**
 * @param $params array  1:sujet 2:valeur du droit 3: ressource éventuelles
 */
function jtpl_block_ifnotacl($compiler, $begin, $params=array())
{
    if($begin){
        if(count($param) == 2){
            $content = ' if(!jAcl::check('.$param[1].','.$param[2].')){';
        }elseif(count($param) == 3){
            $content = ' if(!jAcl::check('.$param[1].','.$param[2].','.$param[3].')){';
        }else{
            $content='';
            $compiler->doError2('errors.tplplugin.block.bad.argument.number','ifnotacl',2);
        }
    }else{
        $content = ' } ';
    }
    return $content;
}

?>