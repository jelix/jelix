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

error_reporting (E_ALL);

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

    // formatage du message
    $messageLog = strtr($conf['messageLogFormat'], array(
        '%date%' => date("Y-m-d H:i:s"),
        '%typeerror%'=>$codeString[$errno],
        '%code%' => $code,
        '%msg%'  => $errmsg,
        '%file%' => $filename,
        '%line%' => $linenum
    ));

    // traitement du message
    if(strpos($action , 'ECHO') !== false){

        if($gJCoord->response == null){
            $gJCoord->initDefaultResponseOfRequest();
        }
        if($gJCoord->response->addErrorMsg($codeString[$errno], $code, $errmsg, $filename, $linenum) || strpos($action , 'EXIT') !== false){
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
?>