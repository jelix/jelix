<?php
/**
 * @author      Laurent Jouanneau
 * @copyright   2007-2014 Laurent Jouanneau
 *
 * @see        http://jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

namespace Jelix\Installer\Reporter;

/**
 * an HTML reporter storing generated content into a string.
 */
class HtmlBuffer implements ReporterInterface
{
    use ReporterTrait;

    public $messageProvider;

    protected $html = '';

    public function __construct(\Jelix\SimpleLocalization\Container $messageProvider)
    {
        $this->messageProvider = $messageProvider;
    }

    public function start()
    {
    }

    public function message($message, $type = '')
    {
        $this->addMessageType($type);
        if ($type == 'error' || $type == 'warning' || $type == 'notice') {
            $this->html .= '<li class="'.$type.'">'.htmlspecialchars($message).'</li>';
        }
    }

    public function end()
    {
        if ($this->html != '') {
            $this->html = '<ul class="checkresults">'.$this->html.'</ul>';
        }

        $nbError = $this->getMessageCounter('error');
        $nbWarning = $this->getMessageCounter('warning');
        $nbNotice = $this->getMessageCounter('notice');

        $this->html .= '<div class="results">';
        if ($nbError) {
            $this->html .= ' '.$nbError.$this->messageProvider->get(($nbError > 1 ? 'number.errors' : 'number.error'));
        }
        if ($nbWarning) {
            $this->html .= ' '.$nbWarning.$this->messageProvider->get(($nbWarning > 1 ? 'number.warnings' : 'number.warning'));
        }
        if ($nbNotice) {
            $this->html .= ' '.$nbNotice.$this->messageProvider->get(($nbNotice > 1 ? 'number.notices' : 'number.notice'));
        }

        if ($nbError) {
            $this->html .= '<p>'.$this->messageProvider->get(($nbError > 1 ? 'conclusion.errors' : 'conclusion.error')).'</p>';
        } elseif ($nbWarning) {
            $this->html .= '<p>'.$this->messageProvider->get(($nbWarning > 1 ? 'conclusion.warnings' : 'conclusion.warning')).'</p>';
        } elseif ($nbNotice) {
            $this->html .= '<p>'.$this->messageProvider->get(($nbNotice > 1 ? 'conclusion.notices' : 'conclusion.notice')).'</p>';
        } else {
            $this->html .= '<p>'.$this->messageProvider->get('conclusion.ok').'</p>';
        }
        $this->html .= '</div>';
    }

    public function getHtml()
    {
        return $this->html;
    }
}
