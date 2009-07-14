<?php
/**
* @package     jelix
* @subpackage  installer
* @author      Laurent Jouanneau
* @contributor 
* @copyright   2007-2009 Laurent Jouanneau
* @link        http://jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

require(JELIX_LIB_PATH.'installer/jInstallerMessageProvider.class.php');


/**
* EXPERIMENTAL
* base class for installers
* @package     jelix
* @subpackage  installer
* @experimental
* @since 1.2
*/
abstract class jInstallerBase {

    /**
     * the object responsible of the results output
     * @var jIInstallReporter
     */
    public $reporter;

    /**
     * @var JInstallerMessageProvider
     */
    public $messages;

    public $nbError = 0;
    public $nbOk = 0;
    public $nbWarning = 0;
    public $nbNotice = 0;

    function __construct ($reporter, $lang=''){
        $this->reporter = $reporter;
        $this->messages = new jInstallerMessageProvider($lang);
    }

    protected function startMessage () {
        $this->nbError = 0;
        $this->nbOk = 0;
        $this->nbWarning = 0;
        $this->nbNotice = 0;
        $this->reporter->start();
    }
    
    protected function endMessage() {
        $this->reporter->end($this);
    }

    protected function error($msg, $params=null, $fullString=false){
        if($this->reporter) {
            if (!$fullString)
                $msg = $this->messages->get($msg,$params);
            $this->reporter->showMessage ( $msg, 'error');
        }
        $this->nbError ++;
    }

    protected function ok($msg, $params=null, $fullString=false){
        if($this->reporter) {
            if (!$fullString)
                $msg = $this->messages->get($msg,$params);
            $this->reporter->showMessage ( $msg, '');
        }
        $this->nbOk ++;
    }
    /**
     * generate a warning
     * @param string $msg  the key of the message to display
     */
    protected function warning($msg, $params=null, $fullString=false){
        if($this->reporter) {
            if (!$fullString)
                $msg = $this->messages->get($msg,$params);
            $this->reporter->showMessage ( $msg, 'warning');
        }
        $this->nbWarning ++;
    }

    protected function notice($msg, $params=null, $fullString=false){
        if($this->reporter) {
            if (!$fullString)
                $msg = $this->messages->get($msg,$params);
            $this->reporter->showMessage ( $msg, 'notice');
        }
        $this->nbNotice ++;
    }
}
