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
 * plugin to show general message logs.
 */
class defaultlogDebugbarPlugin implements jIDebugbarPlugin
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
        $info = new debugbarItemInfo('defaultlog', 'General logs');
        $info->htmlLabel = '<img src="data:image/png;base64,'.base64_encode(file_get_contents(__DIR__.'/../../htmlresponse/debugbar/icons/book_open.png')).'" alt="General logs" title="General logs"/> ';

        $messages = jLog::getMessages(array('default', 'debug'));

        $c = count($messages);
        $info->htmlLabel .= $c;
        if ($c == 0) {
            $info->label = 'no message';
        } else {
            $info->popupContent = '<ul id="jxdb-defaultlog" class="jxdb-list">';
            $currentCount = array('default' => 0, 'debug' => 0);
            foreach ($messages as $msg) {
                $cat = $msg->getCategory();
                ++$currentCount[$cat];
                $title = $msg->getFormatedMessage();
                $truncated = false;
                if (strlen($title) > 60) {
                    $truncated = true;
                    $title = substr($title, 0, 60).'...';
                }
                $info->popupContent .= '<li>
                <h5><a href="#" onclick="jxdb.toggleDetails(this);return false;"><span>'.htmlspecialchars($title).'</span></a></h5>
                <div>';
                if ($truncated) {
                    if ($msg instanceof jLogDumpMessage) {
                        $info->popupContent .= '<pre>'.htmlspecialchars($msg->getMessage()).'</pre>';
                    } else {
                        $info->popupContent .= htmlspecialchars($msg->getMessage());
                    }
                }
                $info->popupContent .= '</div></li>';
            }
            $info->popupContent .= '</ul>';

            foreach ($currentCount as $type => $count) {
                if (($c = jLog::getMessagesCount($type)) > $count) {
                    $info->popupContent .= '<p class="jxdb-msg-warning">There are '.$c.' '.($type == 'default' ? '' : $type).' messages. Only first '.$count.' messages are shown.</p>';
                }
            }
        }
        $debugbar->addInfo($info);
    }
}
