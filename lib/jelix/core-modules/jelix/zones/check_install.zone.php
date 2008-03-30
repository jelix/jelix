<?php
/**
* @package    jelix-modules
* @subpackage jelix
* @author     Bastien Jaillot
* @contributor Laurent Jouanneau
* @copyright  2008 Bastien Jaillot
* @licence    http://www.gnu.org/licenses/gpl.html GNU General Public Licence, see LICENCE file
*/

include (JELIX_LIB_CORE_PATH.'jInstallChecker.class.php');

/**
 * an HTML reporter for jInstallChecker
 * @package jelix
 */
class checkZoneInstallReporter implements jIInstallCheckReporter {
    public $trace = '';
    protected $list='';
    function start(){
    }
    function showError($message){
        $this->list .= '<li class="checkerror">'.htmlspecialchars($message).'</li>';
    }
    function showWarning($message){
        $this->list .= '<li class="checkwarning">'.htmlspecialchars($message).'</li>';

    }
    function showOk($message){
        // $this->list .= '<li class="checkok">'.htmlspecialchars($message).'</li>';

    }
    function showNotice($message){
        $this->list .= '<li class="checknotice">'.htmlspecialchars($message).'</li>';

    }
    function end($checker){
        if($this->list !='')
            $this->trace = '<ul class="checkresults">'.$this->list.'</ul>';

        $this->trace .= '<div class="results">';
        if($checker->nbError){
            $this->trace .= ' '.$checker->nbError. $checker->messages->get( ($checker->nbError > 1?'number.errors':'number.error'));
        }
        if($checker->nbWarning){
            $this->trace .= ' '.$checker->nbWarning. $checker->messages->get(($checker->nbWarning > 1?'number.warnings':'number.warning'));
        }
        if($checker->nbNotice){
            $this->trace .= ' '.$checker->nbNotice. $checker->messages->get(($checker->nbNotice > 1?'number.notices':'number.notice'));
        }

        if($checker->nbError){
           $this->trace .= '<p>'.$checker->messages->get(($checker->nbError > 1?'conclusion.errors':'conclusion.error')).'</p>';
        }else  if($checker->nbWarning){
            $this->trace .= '<p>'.$checker->messages->get(($checker->nbWarning > 1?'conclusion.warnings':'conclusion.warning')).'</p>';
        }else  if($checker->nbNotice){
            $this->trace .= '<p>'.$checker->messages->get(($checker->nbNotice > 1?'conclusion.notices':'conclusion.notice')).'</p>';
        }else{
            $this->trace .= '<p>'.$checker->messages->get('conclusion.ok').'</p>';
        }
        $this->trace .= "</div>";
    }
}

/**
 * a zone to display a default start page with results of the installation check
 * @package jelix
 */
class check_installZone extends jZone {

    protected $_tplname='check_install';

    protected function _prepareTpl() {
        $lang = $GLOBALS['gJConfig']->locale;
        if(!$this->getParam('no_lang_check')) {
            $languages = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
            foreach($languages as $bl){
                if(preg_match("/^([a-zA-Z]{2})(?:[-_]([a-zA-Z]{2}))?(;q=[0-9]\\.[0-9])?$/",$bl,$match)){
                    if(isset($match[2]))
                        $lang = strtolower($match[1]).'_'.strtoupper($match[2]);
                    else
                        $lang = strtolower($match[1]).'_'.strtoupper($match[1]);
                    break;
                }
            }
            if($lang!='fr_FR' && $lang != 'en_EN' && $lang != 'en_US')
                $lang = 'en_EN';
            $GLOBALS['gJConfig']->locale = $lang;
        }

        $reporter = new checkZoneInstallReporter();
        $check = new jInstallCheck($reporter, $lang);
        $check->run();
        $this->_tpl->assign('wwwpath', JELIX_APP_WWW_PATH);
        $this->_tpl->assign('configpath', JELIX_APP_CONFIG_PATH);
        $this->_tpl->assign('check',$reporter->trace);
   }
}
