<?php
/**
* @package    jelix
* @subpackage jtpl_plugin
* @version    $Id$
* @author     Jouanneau Laurent
* @contributor Yann (description and keywords)
* @copyright  2005-2006 Jouanneau laurent
* @link        http://www.jelix.org
* @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

/**
 * meta plugin :  modify an html response object
 *
 * @see jResponseHtml
 * @param jTpl $tpl template engine
 * @param string $method indicates what you want to specify (possible values : js,css,bodyattr)
 * @param mixed $param parameter (a css style sheet for "css" for example)
 */
function jtpl_meta_html_html($tpl, $method, $param)
{
    global $gJCoord,$gJConfig;

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
        case 'jsie':
            $gJCoord->response->addJSLink($param,array(),true);
            break;
        case 'cssie':
            $gJCoord->response->addCSSLink($param,array(),true);
            break;
        case 'csstheme':
            $gJCoord->response->addCSSLink($gJConfig->urlengine['basePath'].'themes/'.$gJConfig->theme.'/'.$param);
            break;
        case 'bodyattr':
            if(is_array($param)){
                foreach($param as $p1=>$p2){
                    if(!is_numeric($p1)) $gJCoord->response->bodyTagAttributes[$p1]=$p2;
                }
            }
            break;
        case 'keywords':
            $gJCoord->response->addMetaKeywords($param);
            break;
        case 'description':
            $gJCoord->response->addMetaDescription($param);
            break;
        case 'others':
            $gJCoord->response->addOthers($param);
            break;
    }
}
?>
