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
* fichier orginellement issue du framework Copix 2.3dev. http://www.copix.org (CopixErrorHandler.lib.php)
* Une partie du code est sous Copyright 2001-2005 CopixTeam (licence LGPL)
* Auteur initial :Laurent Jouanneau
* Adaptée et améliorée pour Jelix par Laurent Jouanneau
*/

error_reporting (E_ALL);

define ('ERR_MSG_NOTHING'   ,0);
define ('ERR_MSG_ECHO'      ,1);
define ('ERR_MSG_LOG_FILE'  ,2);
define ('ERR_MSG_LOG_MAIL'  ,4);
define ('ERR_MSG_LOG_SYSLOG',8);

//define ('ERR_ACT_REDIRECT',  128);
define ('ERR_ACT_EXIT',      256);
define ('ERR_ACT_NOTHING',   0);
define ('ERR_MSG_ECHO_EXIT', ERR_MSG_ECHO | ERR_ACT_EXIT);

/**
* Gestionnaire d'erreur du framework
* Remplace le gestionnaire par defaut du moteur PHP
* @param   integer     $errno      code erreur
* @param   string      $errmsg     message d'erreur
* @param   string      $filename   nom du fichier où s'est produit l'erreur
* @param   integer     $linenum    numero de ligne
* @param   array       $vars       variables de contexte
*/
function jErrorHandler($errno, $errmsg, $filename, $linenum, $errcontext){
    global $gJConfig, $gJCoord;

    $codeString = array(
        E_ERROR         => 'error',
        E_WARNING       => 'warning',
        E_NOTICE        => 'notice',
        E_USER_ERROR    => 'jlx_error',
        E_USER_WARNING  => 'jlx_warning',
        E_USER_NOTICE   => 'jlx_notice',
        E_STRICT        => 'strict'
        );

    if (error_reporting() == 0)
        return;

    $conf = $gJConfig->errorHandlerActions;

    if (isset ($codeString[$errno])){
        $action = $conf[$codeString[$errno]];
    }else{
        $action = $gJConfig->errorhandler['defaultAction'];
    }

    // formatage du message
    $messageLog = strtr($gJConfig->errorhandler['messageFormat'], array(
        '%date%' => date("Y-m-d H:i:s"),
        '%code%' => $codeString[$errno],
        '%msg%'  => $errmsg,
        '%file%' => $filename,
        '%line%' => $linenum
    ));

    // traitement du message
    if($action & ERR_MSG_ECHO){
        /*if($action & ERR_ACT_EXIT)
            header("HTTP/1.1 500 Internal copix error");*/

        if($gJCoord->response == null){
            $gJCoord->initDefaultResponseOfRequest();
        }
        if($gJCoord->response->addErrorMsg($codeString[$errno], 0, $errmsg, $filename, $linenum) || $action & ERR_ACT_EXIT){
            $gJCoord->response->outputErrors();
        }
    }
    if($action & ERR_MSG_LOG_FILE){
        error_log($messageLog,3, JELIX_APP_LOG_PATH.$gJConfig->errorhandler['logFile']);
    }
    if($action & ERR_MSG_LOG_MAIL){
        error_log($messageLog,1, $gJConfig->errorhandler['email'], $gJConfig->errorhandler['emailHeaders']);
    }
    if($action & ERR_MSG_LOG_SYSLOG){
        error_log($messageLog,0);
    }

    // action
    /*if($action & ERR_ACT_REDIRECT){
        header('location: '.$gJConfig->errhandler_redirect);
        exit;
    }*/

    if($action & ERR_ACT_EXIT){
        exit;
    }
}
?>