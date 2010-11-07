<?php
/**
* @package     testapp
* @subpackage  testurls module
* @author      Laurent Jouanneau
* @contributor
* @copyright   2008 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/


class urlsigCtrl extends jController {

   function urla() {
      $rep = $this->getResponse('html',true);
      $content='<h1>test urlA</h1>';
      $rep->body->assign('MAIN',$content);
      return $rep;
   }
   function urlb() {
      $rep = $this->getResponse('html',true);
      $content='<h1>test urlB</h1>';
      $rep->body->assign('MAIN',$content);
      return $rep;
   }
}

