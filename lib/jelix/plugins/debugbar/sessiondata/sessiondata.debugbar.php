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
 * plugin to show content of a session.
 */
class sessiondataDebugbarPlugin implements jIDebugbarPlugin
{
    /**
     * @return string CSS styles
     */
    public function getCss()
    {
        return '
.jxdb-jform-dump dt { padding-top:6px; color:blue;font-size:11pt}
.jxdb-jform-dump dd { font-size:10pt;}
.jxdb-jform-dump table {border:1px solid black; border-collapse: collapse;}
.jxdb-jform-dump table th, .jxdb-jform-dump table td {border:1px solid black;}';
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
        $info = new debugbarItemInfo('sessiondata', 'Session');
        $info->htmlLabel = '<img src="data:image/png;base64,'.base64_encode(file_get_contents(__DIR__.'/../../htmlresponse/debugbar/icons/drive_user.png')).'" alt="Session data" title="Session data"/> ';

        if (!isset($_SESSION) || count($_SESSION) == 0) {
            $info->htmlLabel .= '0';
        } else {
            $info->htmlLabel .= count($_SESSION);
            $info->popupContent = '<ul id="jxdb-sessiondata" class="jxdb-list">';
            foreach ($_SESSION as $key => $value) {
                $info->popupContent .= '<li> ';
                $pre = false;
                $title = $value;
                if (is_scalar($value)) {
                    if (is_string($value)) {
                        if (strlen($value) > 40) {
                            $title = '"'.substr($value, 0, 40).'..."';
                            $pre = true;
                        } else {
                            $title = '"'.$value.'"';
                        }
                    } elseif (is_bool($value)) {
                        $title = ($value ? 'true' : 'false');
                    }
                } elseif (is_null($value)) {
                    $title = 'null';
                } else {
                    $pre = true;
                }

                if ($pre) {
                    $info->popupContent .= '<h5><a href="#" onclick="jxdb.toggleDetails(this);return false;"><span>'.$key.'</span></a></h5>
                   <div>';
                    if ($key == 'JFORMS') {
                        $info->popupContent .= '<dl class="jxdb-jform-dump">';
                        foreach ($value as $selector => $formlist) {
                            foreach ($formlist as $formid => $form) {
                                $info->popupContent .= '<dt>'.$selector.' ('.$formid.')</dt>';
                                $info->popupContent .= "<dd>Data:<table style=''><tr><th>name</th><th>value</th><th>original value</th><th>RO</th><th>Deact.</th></tr>";
                                foreach ($form->data as $dn => $dv) {
                                    if (is_array($dv)) {
                                        $info->popupContent .= "<tr><td>{$dn}</td><td>".var_export($dv, true).'</td>';
                                        $info->popupContent .= '<td>'.(isset($form->originalData[$dn]) ? var_export($form->originalData[$dn], true) : '').'</td>';
                                    } else {
                                        $info->popupContent .= "<tr><td>{$dn}</td><td>".htmlspecialchars($dv).'</td>';
                                        $info->popupContent .= '<td>'.(isset($form->originalData[$dn]) ? htmlspecialchars($form->originalData[$dn]) : '').'</td>';
                                    }
                                    $info->popupContent .= '<td>'.($form->isReadOnly($dn) ? 'Y' : '').'</td>';
                                    $info->popupContent .= '<td>'.($form->isActivated($dn) ? '' : 'Y').'</td></tr>';
                                }
                                $info->popupContent .= '</table>';

                                $info->popupContent .= '<br/>Update Time: '.($form->updatetime ? date('Y-m-d H:i:s', $form->updatetime) : '');
                                $info->popupContent .= '<br/>Token: '.$form->token;
                                $info->popupContent .= '<br/>Ref count: '.$form->refcount;
                                $info->popupContent .= '</dd>';
                            }
                        }
                        $info->popupContent .= '</dl>';
                    } else {
                        $info->popupContent .= '<pre>';
                        $info->popupContent .= var_export($value, true);
                        $info->popupContent .= '</pre>';
                    }
                    $info->popupContent .= '</div></li>';
                } else {
                    $info->popupContent .= '<h5><a href="#" onclick="jxdb.toggleDetails(this);return false;"><span>'.$key.' = '.htmlspecialchars($title).'</span></a></h5><div></div>';
                    $info->popupContent .= '</li>';
                }
            }
            $info->popupContent .= '</ul>';
        }

        $debugbar->addInfo($info);
    }
}
