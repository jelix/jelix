<?php

include (JELIX_LIB_CORE_PATH.'jInstallChecker.class.php');

/**
 * an HTML reporter for jInstallChecker
 * @package jelix
 */
class checkZoneInstallReporter implements jIInstallCheckReporter {
    public $trace = '';
    
    function start(){
        $this->trace .= '<ul class="checkresults">';
    }
    function showError($message){
        $this->trace .= '<li class="checkerror">'.htmlspecialchars($message).'</li>';
    }
    function showWarning($message){
        $this->trace .= '<li class="checkwarning">'.htmlspecialchars($message).'</li>';

    }
    function showOk($message){
        // $this->trace .= '<li class="checkok">'.htmlspecialchars($message).'</li>';

    }
    function showNotice($message){
        $this->trace .= '<li class="checknotice">'.htmlspecialchars($message).'</li>';

    }
    function end($checker){
        $this->trace .= '</ul>';
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


class check_installZone extends jZone {
 
	protected $_tplname='check_install';
 
	protected function _prepareTpl() {
	    $reporter = new checkZoneInstallReporter();
        $check = new jInstallCheck($reporter);
        $check->run();
        $this->_tpl->assign('check',$reporter->trace);
   }
}