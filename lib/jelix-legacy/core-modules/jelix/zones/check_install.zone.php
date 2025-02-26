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
 * @copyright  2012-2024 Laurent Jouanneau
 * @licence    http://www.gnu.org/licenses/gpl.html GNU General Public Licence, see LICENCE file
 */

/**
 * a zone to display a default start page with results of the installation check.
 *
 * @package jelix
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
        $reporter = new \Jelix\Installer\Reporter\HtmlBuffer($messages);
        $check = new \Jelix\Installer\Checker\Checker($reporter, $messages);
        $check->run();


        $this->_tpl->assign('phpExtensions', get_loaded_extensions());
        $this->_tpl->assign('version', jFramework::version());
        $this->_tpl->assign('wwwpath', jApp::wwwPath());
        $this->_tpl->assign('configpath', jApp::varConfigPath());
        $this->_tpl->assign('check', $reporter->trace);
    }
}
