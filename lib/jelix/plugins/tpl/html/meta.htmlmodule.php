<?php
/**
* @package      jelix
* @subpackage   jtpl_plugin
* @author       Laurent Jouanneau
* @contributor  Yann (description and keywords), Dominique Papin (ie7 support), Mickaël Fradin (style), Loic Mathaud (title), Olivier Demah (auhor,generator), Julien Issler
* @copyright    2005-2012 Laurent Jouanneau, 2007 Dominique Papin, 2008 Mickaël Fradin, 2009 Loic Mathaud, 2010 Olivier Demah
* @copyright    2010 Julien Issler
* @link         http://www.jelix.org
* @licence      GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

/**
 * meta plugin :  allow to add css files and js files stored into modules, in an html response object
 *
 * @see jResponseHtml
 * @param jTpl $tpl template engine
 * @param string $method indicates what you want to specify
 *       (possible values : js, css, jsie, cssie, cssie7, cssltie7, csstheme,
 *       cssthemeie, cssthemeie7, cssthemeltie7)
 * @param string $module  the module where file is stored
 * @param mixed $path the relative path inside the {module}/www/ directory, or inside the {module}/www/themes/{currenttheme}/ directory
 * @params array $params additionnal parameters for the generated tag (a media attribute for stylesheet for example)
 */
function jtpl_meta_html_htmlmodule($tpl, $method, $module, $path, $params=array())
{
    global $gJCoord;

    if($gJCoord->response->getType() != 'html'){
        return;
    }
    if (strpos($method, 'csstheme') === 0) {
        $url = jUrl::get('jelix~www:getfile', array('targetmodule'=>$module, 'file'=>'themes/'.jApp::config()->theme.'/'.$path));
        switch($method){
            case 'csstheme':
                $gJCoord->response->addCSSLink($url,$params);
                break;
            case 'cssthemeie':
                $gJCoord->response->addCSSLink($url,$params,true);
                break;
            case 'cssthemeie7':
            case 'cssthemeie8':
            case 'cssthemeie9':
                $gJCoord->response->addCSSLink($url,$params,'IE '.substr($method,-1,1));
                break;
            case 'cssthemeltie7':
            case 'cssthemeltie8':
            case 'cssthemeltie9':
                $gJCoord->response->addCSSLink($url,$params,'lt IE '.substr($method,-1,1));
                break;
            default:
                trigger_error("Unknown resource type in meta_htmlmodule", E_USER_WARNING);
        }
    }
    else {
        $url = jUrl::get('jelix~www:getfile', array('targetmodule'=>$module, 'file'=>$path));
        switch($method){
            case 'js':
                $gJCoord->response->addJSLink($url,$params);
                break;
            case 'css':
                $gJCoord->response->addCSSLink($url,$params);
                break;
            case 'jsie':
                $gJCoord->response->addJSLink($url,$params,true);
                break;
            case 'cssie':
                $gJCoord->response->addCSSLink($url,$params,true);
                break;
            case 'cssie7':
            case 'cssie8':
            case 'cssie9':
                $gJCoord->response->addCSSLink($url,$params,'IE '.substr($method,-1,1));
                break;
            case 'cssltie7':
            case 'cssltie8':
            case 'cssltie9':
                $gJCoord->response->addCSSLink($url,$params,'lt IE '.substr($method,-1,1));
                break;
            default:
                trigger_error("Unknown resource type in meta_htmlmodule", E_USER_WARNING);
        }
    }
}