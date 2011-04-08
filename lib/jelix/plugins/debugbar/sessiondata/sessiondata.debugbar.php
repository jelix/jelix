<?php
/**
* @package     jelix
* @subpackage  debugbar_plugin
* @author      Laurent Jouanneau
* @copyright   2011 Laurent Jouanneau
* @link        http://jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

#includerawinto LOGOSESSIONDATA ../../htmlresponse/debugbar/icons/drive_user.png | base64

/**
 * plugin to show content of a session
 */
class sessiondataDebugbarPlugin implements jIDebugbarPlugin {

    /**
     * @return string CSS styles
     */
    function getCss() {
        return '';
    }

    /**
     * @return string Javascript code lines
     */
    function getJavascript() {
        return '';
    }

    /**
     * it should adds content or set some properties on the debugbar
     * to displays some contents.
     * @param debugbarHTMLResponsePlugin $debugbar the debugbar
     */
    function show($debugbar) {
        $info = new debugbarItemInfo('sessiondata', 'Session');
#expand             $info->htmlLabel = '<img src="data:image/png;base64,__LOGOSESSIONDATA__" alt="Session data" title="Session data"/> ';

        if (!isset($_SESSION) || count($_SESSION) == 0) {
            $info->htmlLabel .= '0';
        }
        else {
            $info->htmlLabel .= count($_SESSION);
            $info->popupContent = '<ul id="jxdb-sessiondata" class="jxdb-list">';
            foreach($_SESSION as $key=>$value) {
                $info->popupContent .= '<li> ';
                $pre = '';
                $title = $value;
                if (is_scalar($value)) {
                    if (is_string($value)) {
                        if( strlen($value) > 40) {
                            $title = '"'.substr($value,0,40).'..."';
                            $pre = $value;
                        }
                        else $title = '"'.$value.'"';
                    }
                    else if (is_bool($value)) {
                        $title = ($value?'true':'false');
                    }
                }
                else if(is_null($value)) {
                    $title = 'null';
                }
                else {
                    $pre = var_export($value, true);
                }

                if ($pre) {
                    $info->popupContent .= '<h5><a href="#" onclick="jxdb.toggleDetails(this);return false;"><span>'.$key.'</span></a></h5>
                    <div><pre>';
                    $info->popupContent .= var_export($value, true);
                    $info->popupContent .='</pre></div></li>';
                }
                else {
                    $info->popupContent .= '<h5><a href="#" onclick="jxdb.toggleDetails(this);return false;"><span>'.$key.' = '.htmlspecialchars($title).'</span></a></h5><div></div>';
                    $info->popupContent .='</li>';
                }
            }
            $info->popupContent .= '</ul>';
        }

        $debugbar->addInfo($info);
    }

}