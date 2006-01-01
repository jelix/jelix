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
        $msg = $exception->getMessage();
    }else{
        $msg = $exception->getLocaleMessage();
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

        if($gJCoord->response->addErrorMsg(E_USER_ERROR, $exception->getCode(), $msg, $exception->getFile(), $exception->getLine()) || strpos($action , 'EXIT') !== false){
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

   public function __construct($localekey, $localeParams=array(), $code = 0) {
       parent::__construct($localekey, $code);
       $this->localeParams=$localeParams;

   }

   public function __toString() {
      try{
         return jLocale::get($this->message, $this->localeParams);
      }catch(Exception $e){
         return $this->message;
      }
   }

   public function getLocaleMessage(){
      return jLocale::get($this->message, $this->localeParams);
   }

}



?>