<?php
/**
* @package    jelix
* @subpackage core
* @version    $Id:$
* @author     Laurent Jouanneau
* @contributor
* @copyright  2001-2005 CopixTeam, 2005-2006 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence    http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*
* fichier orginellement issue du framework Copix 2.3dev20050901. http://www.copix.org (CopixErrorHandler.lib.php)
* Une partie du code est sous Copyright 2001-2005 CopixTeam (licence LGPL)
* Auteur initial :Laurent Jouanneau
* Adaptée et améliorée pour Jelix par Laurent Jouanneau
*/

/**
* Error handler for the framework.
* Replace the default PHP error handler
* @param   integer     $errno      error code
* @param   string      $errmsg     error message
* @param   string      $filename   filename where the error appears
* @param   integer     $linenum    line number where the error appears
* @param   array       $errcontext
*/
function jErrorHandler($errno, $errmsg, $filename, $linenum, $errcontext){
    global $gJConfig, $gJCoord;

    if (error_reporting() == 0)
        return;

    $codeString = array(
        E_ERROR         => 'error',
        E_WARNING       => 'warning',
        E_NOTICE        => 'notice',
        E_USER_ERROR    => 'error',
        E_USER_WARNING  => 'warning',
        E_USER_NOTICE   => 'notice',
        E_STRICT        => 'strict'
    );

    if(preg_match('/^\s*\((\d+)\)(.+)$/',$errmsg,$m)){
        $code = $m[1];
        $errmsg = $m[2];
    }else{
        $code=1;
    }

    $conf = $gJConfig->error_handling;

    if (isset ($codeString[$errno])){
        $action = $conf[$codeString[$errno]];
    }else{
        $action = $conf['default'];
    }

    $doecho=true;
    if($gJCoord->request == null){
        $errmsg = 'JELIX PANIC ! Error during initialization !! '.$errmsg;
        $doecho = false;
        $action.= ' EXIT';
    }elseif($gJCoord->response == null){
        $ret = $gJCoord->initDefaultResponseOfRequest();
        if(is_string($ret)){
            $errmsg = 'Double error ! 1)'. $ret.'; 2)'.$errmsg;
        }
    }

    // formatage du message
    $messageLog = strtr($conf['messageLogFormat'], array(
        '%date%' => date("Y-m-d H:i:s"),
        '%typeerror%'=>$codeString[$errno],
        '%code%' => $code,
        '%msg%'  => $errmsg,
        '%file%' => $filename,
        '%line%' => $linenum,
        '\t' =>"\t",
        '\n' => "\n"
    ));

    if(strpos($action , 'TRACE') !== false){
        $arr = debug_backtrace();
        $messageLog.="\ttrace:";
        array_shift($arr);
        foreach($arr as $k=>$t){
            $messageLog.="\n\t$k\t".(isset($t['class'])?$t['class'].$t['type']:'').$t['function']."()\t";
            $messageLog.=(isset($t['file'])?$t['file']:'[php]').' : '.(isset($t['line'])?$t['line']:'');
        }
        $messageLog.="\n";
    }
    // traitement du message
    if(strpos($action , 'ECHOQUIET') !== false){
        if(!$doecho){
            header('Content-type: text/plain');
            echo 'JELIX PANIC ! Error during initialization !! ';
        }elseif($gJCoord->addErrorMsg($codeString[$errno], $code, $conf['quietMessage'], '', ''))
            $action.=' EXIT';
    }elseif(strpos($action , 'ECHO') !== false){
        if(!$doecho){
            header('Content-type: text/plain');
            echo $messageLog;
        }elseif($gJCoord->addErrorMsg($codeString[$errno], $code, $errmsg, $filename, $linenum)){
            $action.=' EXIT';
        }
    }
    if(strpos($action , 'LOGFILE') !== false){
        @error_log($messageLog,3, JELIX_APP_LOG_PATH.$conf['logFile']);
    }
    if(strpos($action , 'MAIL') !== false){
        error_log($messageLog,1, $conf['email'], $conf['emailHeaders']);
    }
    if(strpos($action , 'SYSLOG') !== false){
        error_log($messageLog,0);
    }

    if(strpos($action , 'EXIT') !== false){
        if($doecho && $gJCoord->response)
            $gJCoord->response->outputErrors();
        exit;
    }
}
?>