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


function jExceptionHandler($exception){
    global $gJConfig, $gJCoord;

    if($exception instanceof jException){
        $msg = $exception->getLocaleMessage();
    }else{
        $msg = $exception->getMessage();
    }

    $conf = $gJConfig->errorHandling;
    $action = $conf['exception'];

    // formatage du message de log
    $messageLog = strtr($conf['messageLogFormat'], array(
        '%date%' => date("Y-m-d H:i:s"),
        '%code%' => $exception->getCode(),
        '%msg%'  => $msg,
        '%file%' => $exception->getFile(),
        '%line%' => $exception->getLine()
    ));

    // traitement du message
    if(strpos($action , 'ECHO') !== false){

        /*if($action & ERR_ACT_EXIT)
            header("HTTP/1.1 500 Internal jelix error");*/

        if($gJCoord->response == null){
            $gJCoord->initDefaultResponseOfRequest();
        }

        if($gJCoord->response->addErrorMsg('error', $exception->getCode(), $msg, $exception->getFile(), $exception->getLine()) || strpos($action , 'EXIT') !== false){
            $gJCoord->response->outputErrors();
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

    if(strpos($action , 'EXIT') !== false){
        exit;
    }
}



class jException extends Exception {
   public $localeParams = array();
   public $localizedMessage = '';

   public function __construct($localekey, $localeParams=array(), $code = 1) {
      try{
         $this->localizedMessage = jLocale::get($localekey, $localeParams);
      }catch(Exception $e){
         $this->localizedMessage = $localekey;
      }
      if(preg_match('/^\s*\((\d+)\)(.+)$/',$this->localizedMessage,$m)){
          $code = $m[1];
          $this->localizedMessage = $m[2];
      }
      parent::__construct($localekey, $code);
      $this->localeParams=$localeParams;

   }

   public function __toString() {
      return $this->localizedMessage;
   }

   public function getLocaleMessage(){
      return $this->localizedMessage;
   }

}



?>