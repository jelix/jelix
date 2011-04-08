<?php
/**
* @package     jelix
* @subpackage  responsehtml_plugin
* @author      Laurent Jouanneau
* @copyright   2010-2011 Laurent Jouanneau
* @link        http://jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

#includerawinto LOGOERROR icons/exclamation.png | base64
#includerawinto LOGOWARNING icons/error.png | base64
#includerawinto LOGONOERROR icons/accept.png | base64
#includerawinto LOGONOTICE icons/information.png | base64

/**
 * native plugin for the debugbar, which displays list of errors, warnings...
 * @since 1.3
 */
class errorsDebugbarPlugin implements jIDebugbarPlugin {

    function getCss() { return "
##jxdb-errors li.jxdb-msg-error h5 span {background-image: url('".$this->getErrorIcon()."');}
#expand #jxdb-errors li.jxdb-msg-notice h5 span {background-image: url('data:image/png;base64,__LOGONOTICE__');}
##jxdb-errors li.jxdb-msg-warning h5 span {background-image: url('".$this->getWarningIcon()."'); }
";}

    function getJavascript() {return <<<EOS
#includeraw errors.debugbar.js
EOS
;
    }

    function show($debugbarPlugin) {
        $info = new debugbarItemInfo('errors', 'Errors');
        $messages = jLog::getMessages(array('error','warning','notice','deprecated','strict'));

        if (!jLog::isPluginActivated('memory', 'error')) {
            array_unshift($messages, new jLogErrorMessage('warning',0,"Memory logger is not activated in jLog for errors, You cannot see them",'',0,array()));
        }
        if (!jLog::isPluginActivated('memory', 'warning')) {
            array_unshift($messages, new jLogErrorMessage('warning',0,"Memory logger is not activated in jLog for warnings, You cannot see them",'',0,array()));
        }
        if (!jLog::isPluginActivated('memory', 'notice')) {
            array_unshift($messages, new jLogErrorMessage('notice',0,"Memory logger is not activated in jLog for notices, You cannot see them",'',0,array()));
        }

        $c = count($messages);
        if ($c == 0) {
            $info->label = 'no error';
#expand             $info->htmlLabel = '<img src="data:image/png;base64,__LOGONOERROR__" alt="no errors" title="no errors"/> 0';
        }
        else {
            $info->popupContent = '<ul id="jxdb-errors" class="jxdb-list">';
            $maxLevel = 0;
            foreach($messages as $msg) {
                if ($msg instanceOf jLogErrorMessage) {
                    $cat = $msg->getCategory();

                    if ($cat == 'error')
                        $maxLevel = 1;

                    // careful: if you change the position of the div, update debugbar.js
                    $info->popupContent .= '<li class="jxdb-msg-'.$cat.'">
                    <h5><a href="#" onclick="jxdb.toggleDetails(this);return false;"><span>'.htmlspecialchars($msg->getMessage()).'</span></a></h5>
                    <div>
                    <p>Code: '.$msg->getCode().'<br/> File: '.htmlspecialchars($msg->getFile()).' '.htmlspecialchars($msg->getLine()).'</p>';
                    $info->popupContent .= $debugbarPlugin->formatTrace($msg->getTrace());
                    $info->popupContent .='</div></li>';
                }
            }
            if ($maxLevel) {
                $info->htmlLabel = '<img src="'.$this->getErrorIcon().'" alt="Errors" title="'.$c.' errors"/> '.$c;
                $info->popupOpened = true;
            }
            else {
                $info->htmlLabel = '<img src="'.$this->getWarningIcon().'" alt="Warnings" title="There are '.$c.' warnings" /> '.$c;
            }
            $info->popupContent .= '</ul>';
        }

        $debugbarPlugin->addInfo($info);
    }

    protected function getErrorIcon() {
#expand    return 'data:image/png;base64,__LOGOERROR__';
    }

    protected function getWarningIcon() {
#expand    return 'data:image/png;base64,__LOGOWARNING__';
    }

}