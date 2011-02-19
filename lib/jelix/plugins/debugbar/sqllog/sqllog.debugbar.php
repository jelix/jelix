<?php
/**
* @package     jelix
* @subpackage  debugbar_plugin
* @author      Laurent Jouanneau
* @copyright   2011 Laurent Jouanneau
* @link        http://jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

#includerawinto LOGOSQLLOG ../../htmlresponse/debugbar/icons/database.png | base64

/**
 * plugin to show all sql queries into the debug bar
 */
class sqllogDebugbarPlugin implements jIDebugbarPlugin {

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
        $info = new debugbarItemInfo('sqllog', 'SQL queries');
        $messages = jLog::getMessages('sql');
#expand             $info->htmlLabel = '<img src="data:image/png;base64,__LOGOSQLLOG__" alt="SQL queries" title="SQL queries"/> ';

        if (!jLog::isPluginActivated('memory', 'sql')) {
            $info->htmlLabel .= '?';
            $info->label .= 'memory logger is not active';
        }
        else {
            $info->htmlLabel .= count($messages);
            if (count($messages)) {
                $info->popupContent = '<ul id="jxdb-sqllog" class="jxdb-list">';
                foreach($messages as $msg) {
                    $info->popupContent .= '<li>
                    <h5><a href="#" onclick="jxdb.toggleDetails(this);return false;"><span>'.htmlspecialchars($msg->getMessage()).'</span></a></h5>
                    <div>
                    <p>Time: '.$msg->getTime().'s<br/>';
                    $dao = $msg->getDao();
                    if ($dao)
                        $info->popupContent.='Dao: '.$dao;
                    $info->popupContent.='</p>'.$debugbar->formatTrace($msg->getTrace());
                    $info->popupContent .='</div>';
                }
                $info->popupContent .= '</ul>';
            }
        }

        $debugbar->addInfo($info);
    }

}