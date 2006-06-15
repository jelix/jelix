<?php
/**
* @package    jelix
* @subpackage template plugins
* @version    $Id$
* @author     Jouanneau Laurent
* @contributor
* @copyright  2005-2006 Jouanneau laurent
* @link        http://www.jelix.org
* @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

function jtpl_meta_html($tpl, $method, $param)
{
    global $gJCoord;

    if($gJCoord->response->getFormatType() != 'html'){
        return;
    }
    switch($method){
        case 'js':
            $gJCoord->response->addJSLink($param);
            break;
        case 'css':
            $gJCoord->response->addCSSLink($param);
            break;
        case 'bodyattr':
            if(is_array($param)){
                foreach($param as $p1=>$p2){
                    if(!is_numeric($p1)) $gJCoord->response->bodyTagAttributes[$p1]=$p2;
                }
            }
            break;
    }
}
?>
