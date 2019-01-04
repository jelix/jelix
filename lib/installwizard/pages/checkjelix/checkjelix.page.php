<?php

/**
* page for Installation wizard
*
* @package     InstallWizard
* @subpackage  pages
* @author      Laurent Jouanneau
* @copyright   2010-2018 Laurent Jouanneau
* @link        http://jelix.org
* @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
*/

/**
 * page for a wizard, to check a jelix installation
 */
class checkjelixWizPage extends installWizardPage  implements \Jelix\Installer\Reporter\ReporterInterface {
    use \Jelix\Installer\Reporter\ReporterTrait;
    protected $tpl;
    protected $messages;

    /**
     * action to display the page
     * @param \Jelix\Castor\Castor $tpl the template container
     */
    function show (\Jelix\Castor\Castor $tpl) {
        $this->tpl = $tpl;
        $messages = new \Jelix\Installer\Checker\Messages();
        $check = new \Jelix\Installer\Checker\Checker($this, $messages);
        if (isset($this->config['verbose'])) {
            $check->verbose = (!!$this->config['verbose']);
        }

        if (isset($this->config['databases'])) {
            $db = explode(',', trim($this->config['databases']));
            $check->addDatabaseCheck($db, true);
        }
        if (isset($this->config['pathcheck'])) {
            if(is_string($this->config['pathcheck']))
                $files = explode(',', trim($this->config['pathcheck']));
            else
                $files = $this->config['pathcheck'];
            $check->addWritablePathCheck($files);
        }

        $check->checkForInstallation = true;
        $check->run();

        return ($check->nbError == 0);
    }

    //----- \Jelix\Installer\Reporter\ReporterInterface implementation

    function start() {}

    function message($message, $type=''){
        $this->addMessageType($type);
        $this->messages[] = array($type, $message);
    }
    
    function end(){
        $nbError = $this->getMessageCounter('error');
        $nbWarning = $this->getMessageCounter('warning');
        $nbNotice = $this->getMessageCounter('notice');
        $this->tpl->assign('messages', $this->messages);
        $this->tpl->assign('nbError', $nbError);
        $this->tpl->assign('nbWarning', $nbWarning);
        $this->tpl->assign('nbNotice', $nbNotice);
    }
}
