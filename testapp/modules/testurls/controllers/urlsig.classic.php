<?php
/**
* @package     testapp
* @subpackage  testurls module
* @author      Jouanneau Laurent
* @contributor
* @copyright   2008 Jouanneau laurent
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

