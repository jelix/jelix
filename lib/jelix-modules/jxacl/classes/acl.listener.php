<?php
/**
* @package     jelix-modules
* @subpackage  jxacl
* @version     $Id$
* @author      Jouanneau Laurent
* @contributor neolao
* @copyright   2006 Jouanneau laurent, neolao
* @link        http://www.jelix.org
* @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
*/

class ListenerAcl extends jEventListener{

   function onFetchXulOverlay($event){
        $tpl = $event->getParam('tpl');
        if($tpl == 'jxxulapp~main'){
            $event->Add('jxacl~xuladmin_xaovlay');
        }elseif($tpl == 'jxauth~xuladmin'){
            $event->Add('jxacl~xuladmin_jxauthovlay');
        }
   }

   /**
    * Création d'un nouvel utilisateur
    *
    * @var jEvent $event L'événement
    */
   function onAuthNewUser($event){
        $user = $event->getParam('user');
        jAclUserGroup::createUser($user->login);
   }

   /**
    * Suppression d'un utilisateur
    *
    * @var jEvent $event L'événement
    */
   function onAuthRemoveUser($event){
        $login = $event->getParam('login');
        jAclUserGroup::removeUser($login);
   }
}
?>