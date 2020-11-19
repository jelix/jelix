<?php
/**
 * @package     jelix
 * @subpackage  responsehtml_plugin
 *
 * @author      Laurent Jouanneau
 * @copyright   2010-2011 Laurent Jouanneau
 *
 * @see        http://jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

/**
 * native plugin for the debugbar, which displays list of errors, warnings...
 *
 * @since 1.3
 */
class errorsDebugbarPlugin implements jIDebugbarPlugin
{
    public function getCss()
    {
        return "
#jxdb-errors li.jxdb-msg-error h5 span {background-image: url('".$this->getErrorIcon()."');}
#jxdb-errors li.jxdb-msg-notice h5 span {background-image: url('data:image/png;base64,".base64_encode(file_get_contents(__DIR__.'/icons/information.png'))."');}
#jxdb-errors li.jxdb-msg-warning h5 span {background-image: url('".$this->getWarningIcon()."'); }
";
    }

    public function getJavascript()
    {
        return file_get_contents(__DIR__.'/errors.debugbar.js');
    }

    public function show($debugbarPlugin)
    {
        $info = new debugbarItemInfo('errors', 'Errors');
        $messages = jLog::getMessages(array('error', 'warning', 'notice', 'deprecated', 'strict'));

        if (!jLog::isPluginActivated('memory', 'error')) {
            array_unshift($messages, new jLogErrorMessage('warning', 0, 'Memory logger is not activated in jLog for errors, You cannot see them', '', 0, array()));
        }
        if (!jLog::isPluginActivated('memory', 'warning')) {
            array_unshift($messages, new jLogErrorMessage('warning', 0, 'Memory logger is not activated in jLog for warnings, You cannot see them', '', 0, array()));
        }
        if (!jLog::isPluginActivated('memory', 'notice')) {
            array_unshift($messages, new jLogErrorMessage('notice', 0, 'Memory logger is not activated in jLog for notices, You cannot see them', '', 0, array()));
        }

        $c = count($messages);
        if ($c == 0) {
            $info->label = 'no error';
            $info->htmlLabel = '<img src="data:image/png;base64,'.base64_encode(file_get_contents(__DIR__.'/icons/accept.png')).'" alt="no errors" title="no errors"/> 0';
        } else {
            $info->popupContent = '<ul id="jxdb-errors" class="jxdb-list">';
            $maxLevel = 0;
            $popupOpened = false;
            $currentCount = array('error' => 0, 'warning' => 0, 'notice' => 0, 'deprecated' => 0, 'strict' => 0);

            $openOnString = jApp::config()->debugbar['errors_openon'];
            $openOn = array();
            if ($openOnString == '*') {
                $popupOpened = true;
            } else {
                $openOn = preg_split('/\\s*,\\s*/', strtoupper($openOnString));
            }

            foreach ($messages as $msg) {
                $cat = $msg->getCategory();
                ++$currentCount[$cat];
                if ($msg instanceof jLogErrorMessage) {
                    if ($cat == 'error') {
                        $maxLevel = 1;
                    }

                    if (!$popupOpened && in_array(strtoupper($cat), $openOn) !== false) {
                        $popupOpened = true;
                    }

                    // careful: if you change the position of the div, update debugbar.js
                    $info->popupContent .= '<li class="jxdb-msg-'.$cat.'">
                    <h5><a href="#" onclick="jxdb.toggleDetails(this);return false;"><span>'.htmlspecialchars($msg->getMessage()).'</span></a></h5>
                    <div><p>Code: '.$msg->getCode().'<br/> File: '.htmlspecialchars($msg->getFile()).' '.htmlspecialchars($msg->getLine()).'</p>';
                    $info->popupContent .= $debugbarPlugin->formatTrace($msg->getTrace());
                    $info->popupContent .= '</div></li>';
                } else {
                    $info->popupContent .= '<li class="jxdb-msg-'.$cat.'">
                    <h5><a href="#" onclick="jxdb.toggleDetails(this);return false;"><span>'.htmlspecialchars($msg->getMessage()).'</span></a></h5>
                    <div><p>Not a real PHP '.$cat.',  logged directly by your code. <br />Details are not available.</p></div></li>';
                }
            }
            if ($maxLevel) {
                $info->htmlLabel = '<img src="'.$this->getErrorIcon().'" alt="Errors" title="'.$c.' errors"/> '.$c;
            } else {
                $info->htmlLabel = '<img src="'.$this->getWarningIcon().'" alt="Warnings" title="There are '.$c.' warnings" /> '.$c;
            }
            $info->popupOpened = $popupOpened;
            $info->popupContent .= '</ul>';

            foreach ($currentCount as $type => $count) {
                if (($c = jLog::getMessagesCount($type)) > $count) {
                    $info->popupContent .= '<p class="jxdb-msg-warning">There are '.$c.' '.$type.' messages. Only first '.$count.' messages are shown.</p>';
                }
            }
        }

        $debugbarPlugin->addInfo($info);
    }

    protected function getErrorIcon()
    {
        return 'data:image/png;base64,'.base64_encode(file_get_contents(__DIR__.'/icons/exclamation.png'));
    }

    protected function getWarningIcon()
    {
        return 'data:image/png;base64,'.base64_encode(file_get_contents(__DIR__.'/icons/error.png'));
    }
}
