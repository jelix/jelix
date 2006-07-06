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

function jtpl_block_ifusernotconnected($compiler, $begin, $params=array())
{
    if($begin){
        if(count($param)){
            $content='';
            $compiler->doError1('errors.tplplugin.block.too.many.arguments','ifuserconnected');
        }else{
            $content = ' if(!jAuth::isConnected()){';
        }
    }else{
        $content = ' } ';
    }
    return $content;
}

?>