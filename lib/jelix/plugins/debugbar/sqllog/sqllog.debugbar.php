<?php
/**
 * @package     jelix
 * @subpackage  debugbar_plugin
 *
 * @author      Laurent Jouanneau
 * @copyright   2011 Laurent Jouanneau
 *
 * @see        http://jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

/**
 * plugin to show all sql queries into the debug bar.
 */
class sqllogDebugbarPlugin implements jIDebugbarPlugin
{
    /**
     * @return string CSS styles
     */
    public function getCss()
    {
        return '';
    }

    /**
     * @return string Javascript code lines
     */
    public function getJavascript()
    {
        return '';
    }

    /**
     * it should adds content or set some properties on the debugbar
     * to displays some contents.
     *
     * @param debugbarHTMLResponsePlugin $debugbar the debugbar
     */
    public function show($debugbar)
    {
        $info = new debugbarItemInfo('sqllog', 'SQL queries');
        $messages = jLog::getMessages('sql');
        $info->htmlLabel = '<img src="data:image/png;base64,'.base64_encode(file_get_contents(__DIR__.'/../../htmlresponse/debugbar/icons/database.png')).'" alt="SQL queries" title="SQL queries"/> ';

        if (!jLog::isPluginActivated('memory', 'sql')) {
            $info->htmlLabel .= '?';
            $info->label .= 'memory logger is not active';
        } else {
            $realCount = jLog::getMessagesCount('sql');
            $currentCount = count($messages);
            $info->htmlLabel .= $realCount;
            if ($realCount) {
                if ($realCount > $currentCount) {
                    $info->popupContent = '<p class="jxdb-msg-warning">Too many queries ('.$realCount.'). Only first '.$currentCount.' queries are shown.</p>';
                }
                $sqlDetailsContent = '<ul id="jxdb-sqllog" class="jxdb-list">';
                $totalTime = 0;
                foreach ($messages as $msg) {
                    if (get_class($msg) != 'jSQLLogMessage') {
                        continue;
                    }
                    $dao = $msg->getDao();
                    if ($dao) {
                        $m = 'DAO '.$dao;
                    } else {
                        $m = substr($msg->getMessage(), 0, 50).' [...]';
                    }

                    $msgTime = $msg->getTime();
                    $totalTime += $msgTime;
                    $sqlDetailsContent .= '<li>
                    <h5><a href="#" onclick="jxdb.toggleDetails(this);return false;"><span>'.htmlspecialchars($m).'</span></a></h5>
                    <div>
                    <p>Time: '.$msgTime.'s</p>';
                    $sqlDetailsContent .= '<pre style="white-space:pre-wrap">'.htmlspecialchars($msg->getMessage()).'</pre>';
                    if ($msg->getMessage() != $msg->originalQuery) {
                        $sqlDetailsContent .= '<p>Original query: </p><pre style="white-space:pre-wrap">'.htmlspecialchars($msg->originalQuery).'</pre>';
                    }
                    $sqlDetailsContent .= $debugbar->formatTrace($msg->getTrace());
                    $sqlDetailsContent .= '</div></li>';
                }
                $sqlDetailsContent .= '</ul>';

                $info->popupContent .= '<div>Total SQL time&nbsp;: '.$totalTime.'s</div>';
                $info->popupContent .= $sqlDetailsContent;
            }
        }

        $debugbar->addInfo($info);
    }
}
