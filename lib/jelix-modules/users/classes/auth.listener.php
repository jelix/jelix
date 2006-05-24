<?php
/**
* @package     jelix-modules
* @subpackage  users
* @version     $Id$
* @author      Jouanneau Laurent
* @contributor
* @copyright   2006 Jouanneau laurent
* @link        http://www.jelix.org
* @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
*/

class ListenerAuth extends jEventListener{

   /**
   *
   */
   function onAuthCanLogin ($event) {
        $user = $event->getParam('user');
        $ok = true;
        if(isset($user->actif)){
            $ok = ($user->actif == '1');
        }

        $ok = $ok & ($user->password != '');

        $event->Add(array('canlogin'=>$ok));

   }

   function onFetchXulOverlay($event){
        if($event->getParam('tpl') == 'xulapp~main'){
            $event->Add('users~default_xaovlay');
        }
   }
}
?>