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

class installappWizPage extends installWizardPage {
    
    /**
     * action to display the page
     * @param \Jelix\Castor\Castor $tpl the template container
     */
    function show (\Jelix\Castor\Castor $tpl) {
        if (isset($this->config['level'])) {
            $level = $this->config['level'];
            if (!in_array($level, array('error', 'notice', 'warning')))
                $level = 'warning';
        }
        else
            $level = 'warning';
        
        
        $reporter = new wizInstallReporter($level, $this);
        $installer = new \Jelix\Installer\Installer($reporter);
        $ok = $installer->installApplication();
        
        $tpl->assign('messages', $reporter->messages);
        $tpl->assign('installok', $ok);
        
        return $ok;
    }
    
    /**
     * action to process the page after the submit
     */
    function process() {
        return 0;
    }
}

 /**
 * 
 */
class wizInstallReporter implements \Jelix\Installer\Reporter\ReporterInterface {
    use \Jelix\Installer\Reporter\ReporterTrait;
    /**
     * @var string error, notice or warning
     */
    protected $level;

    public $messages = array();
    
    function __construct($level= 'notice', $page) {
       $this->level = $level;
       $this->page = $page;
    }

    function start() {
        if ($this->level == 'notice')
            $this->messages[] = array('notice', $this->page->getLocale('install.start'));
    }

    /**
     * displays a message
     * @param string $message the message to display
     * @param string $type the type of the message : 'error', 'notice', 'warning', ''
     */
    function message($message, $type='') {
        if (($type == 'error' && $this->level != '')
            || ($type == 'warning' && $this->level != 'notice' && $this->level != '')
            || (($type == 'notice' || $type =='') && $this->level == 'notice'))
        $this->messages[] = array( $type, $message);
    }

    /**
     * called when the installation is finished
     * @param array $results an array which contains, for each type of message,
     * the number of messages
     */
    function end() {
        $this->messages[] = array('',  $this->page->getLocale('install.end'));
    }
}