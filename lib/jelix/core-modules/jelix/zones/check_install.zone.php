<?php
/**
 * @package    jelix-modules
 * @subpackage jelix-module
 *
 * @author     Bastien Jaillot
 * @contributor Laurent Jouanneau, Julien Issler
 *
 * @copyright  2008 Bastien Jaillot
 * @copyright  2009 Julien Issler
 * @copyright 2012-2018 Laurent Jouanneau
 * @licence    http://www.gnu.org/licenses/gpl.html GNU General Public Licence, see LICENCE file
 */

/**
 * an HTML reporter for jInstallChecker.
 *
 * @package    jelix-modules
 * @subpackage jelix-module
 */
class checkZoneInstallReporter implements \Jelix\Installer\Reporter\ReporterInterface
{
    use \Jelix\Installer\Reporter\ReporterTrait;

    public $trace = '';
    protected $messageProvider;
    protected $list = '';

    public function __construct($messageProvider)
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
            $this->list .= '<li class="'.$type.'">'.htmlspecialchars($message).'</li>';
        }
    }

    public function end()
    {
        if ($this->list != '') {
            $this->trace = '<ul class="checkresults">'.$this->list.'</ul>';
        }

        $nbError = $this->getMessageCounter('error');
        $nbWarning = $this->getMessageCounter('warning');
        $nbNotice = $this->getMessageCounter('notice');

        $this->trace .= '<div class="results">';
        if ($nbError) {
            $this->trace .= ' '.$nbError.$this->messageProvider->get(($nbError > 1 ? 'number.errors' : 'number.error'));
        }
        if ($nbWarning) {
            $this->trace .= ' '.$nbWarning.$this->messageProvider->get(($nbWarning > 1 ? 'number.warnings' : 'number.warning'));
        }
        if ($nbNotice) {
            $this->trace .= ' '.$nbNotice.$this->messageProvider->get(($nbNotice > 1 ? 'number.notices' : 'number.notice'));
        }

        if ($nbError) {
            $this->trace .= '<p>'.$this->messageProvider->get(($nbError > 1 ? 'conclusion.errors' : 'conclusion.error')).'</p>';
        } elseif ($nbWarning) {
            $this->trace .= '<p>'.$this->messageProvider->get(($nbWarning > 1 ? 'conclusion.warnings' : 'conclusion.warning')).'</p>';
        } elseif ($nbNotice) {
            $this->trace .= '<p>'.$this->messageProvider->get(($nbNotice > 1 ? 'conclusion.notices' : 'conclusion.notice')).'</p>';
        } else {
            $this->trace .= '<p>'.$this->messageProvider->get('conclusion.ok').'</p>';
        }
        $this->trace .= '</div>';
    }
}

/**
 * a zone to display a default start page with results of the installation check.
 *
 * @package    jelix-modules
 * @subpackage jelix-module
 */
class check_installZone extends jZone
{
    protected $_tplname = 'check_install';

    protected function _prepareTpl()
    {
        $lang = jApp::config()->locale;
        if (!$this->param('no_lang_check')) {
            $locale = jLocale::getPreferedLocaleFromRequest();
            if (!$locale) {
                $locale = 'en_US';
            }
            jApp::config()->locale = $locale;
        }

        $messages = new \Jelix\Installer\Checker\Messages($lang);
        $reporter = new checkZoneInstallReporter($messages);
        $check = new \Jelix\Installer\Checker\Checker($reporter, $messages);
        $check->run();

        $this->_tpl->assign('phpExtensions', get_loaded_extensions());
        $this->_tpl->assign('version', jFramework::version());
        $this->_tpl->assign('wwwpath', jApp::wwwPath());
        $this->_tpl->assign('configpath', jApp::varConfigPath());
        $this->_tpl->assign('check', $reporter->trace);
    }
}
