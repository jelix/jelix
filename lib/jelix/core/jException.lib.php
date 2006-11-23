<?php
/**
* @package    jelix
* @subpackage core
* @version    $Id$
* @author     Jouanneau Laurent
* @contributor
* @copyright  2005-2006 Jouanneau laurent
* @link        http://www.jelix.org
* @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

/**
* Exception handler for the framework.
* Replace the default PHP Exception handler
* @param   Exception   $exception  the exception object
*/
function jExceptionHandler($exception){
    global $gJConfig, $gJCoord;

    if($exception instanceof jException){
        $msg = $exception->getLocaleMessage();
    }else{
        $msg = $exception->getMessage();
    }

    $conf = $gJConfig->error_handling;
    $action = $conf['exception'];

    $doecho=true;
    if($gJCoord->request == null){
        $msg = 'JELIX PANIC ! Error during initialization !! '.$msg;
        $doecho = false;
    }elseif($gJCoord->response == null){
        $ret = $gJCoord->initDefaultResponseOfRequest();
        if(is_string($ret)){
            $errmsg = 'Double error ! 1)'. $ret.'; 2)'.$errmsg;
        }

    }

    // formatage du message de log
    $messageLog = strtr($conf['messageLogFormat'], array(
        '%date%' => date("Y-m-d H:i:s"),
        '%code%' => $exception->getCode(),
        '%msg%'  => $msg,
        '%file%' => $exception->getFile(),
        '%line%' => $exception->getLine(),
        '%typeerror%'=>'exception',
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
        }else
            $gJCoord->addErrorMsg('error', $exception->getCode(), $conf['quietMessage'], '', '');
    }elseif(strpos($action , 'ECHO') !== false){
        if($doecho)
            $gJCoord->addErrorMsg('error', $exception->getCode(), $msg, $exception->getFile(), $exception->getLine());
        else{
            header('Content-type: text/plain');
            echo $messageLog;
        }
    }
    if(strpos($action , 'LOGFILE') !== false){
        error_log($messageLog,3, JELIX_APP_LOG_PATH.$conf['logFile']);
    }
    if(strpos($action , 'MAIL') !== false){
        error_log($messageLog,1, $conf['email'], $conf['emailHeaders']);
    }
    if(strpos($action , 'SYSLOG') !== false){
        error_log($messageLog,0);
    }

    if($doecho)
        $gJCoord->response->outputErrors();
    exit;
}


/**
 * Jelix Exception
 * It handles locale messages.
 * message property contains the locale key, and a new property
 * contains the localized message
 * @package  jelix
 * @subpackage core
 */
class jException extends Exception {

    /**
     * parameters for the locale key
     */
    public $localeParams = array();

    /**
     * the localized message
     * @var string
     */
    public $localizedMessage = '';

    /**
     * @param string $localekey a locale key
     * @param array $localeParams parameters for the message (for sprintf)
     * @param integer $code error code (can be provided by the localized message)
     */
    public function __construct($localekey, $localeParams=array(), $code = 1) {
        try{
            $this->localizedMessage = jLocale::get($localekey, $localeParams);
        }catch(Exception $e){
            $this->localizedMessage = $e->getMessage();
        }
        if(preg_match('/^\s*\((\d+)\)(.+)$/',$this->localizedMessage,$m)){
            $code = $m[1];
            $this->localizedMessage = $m[2];
        }
        parent::__construct($localekey, $code);
        $this->localeParams=$localeParams;
    }

    /**
     * magic function for echo
     * @return string localized message
     */
    public function __toString() {
        return $this->localizedMessage;
    }

    /**
     * getter for the localized message
     * @return string
     */
    public function getLocaleMessage(){
        return $this->localizedMessage;
    }

}

?>