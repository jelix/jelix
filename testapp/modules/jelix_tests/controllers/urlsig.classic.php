<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Laurent Jouanneau
* @contributor
* @copyright   2006-2007 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/


class urlsigCtrl extends jController {

   function url1($type='url1') {
      $rep = $this->getResponse('html',true);
      $content='<h1>test url : news '.$type.'</h1>';
      $content.='<ul><li>annee='.$this->param('annee').'</li>';
      $content.='<li>mois='.$this->param('mois').'</li>';
      $content.='<li>id='.$this->param('id').'</li></ul>';
      $rep->body->assign('MAIN',$content);
      return $rep;
   }
   function url2() {
      $rep = $this->getResponse('html',true);
      $content='<h1>test url : testnews (url2)</h1>';
      $content.='<ul><li>annee='.$this->param('annee').'</li>';
      $content.='<li>mois='.$this->param('mois').'</li>';
      $content.='<li>mystatic='.$this->param('mystatic').'</li></ul>';
      $rep->body->assign('MAIN',$content);
      return $rep;
   }
   function url3() {
      $rep = $this->getResponse('html',true);
      $content='<h1>test url : cms (url3)</h1>';
      $content.='<ul><li>rubrique='.$this->param('rubrique').'</li>';
      $content.='<li>id_art='.$this->param('id_art').'</li>';
      $content.='<li>article='.$this->param('article').'</li></ul>';
      $rep->body->assign('MAIN',$content);
      return $rep;
   }
   function url4() {
       $rep = $this->getResponse('html',true);
      $content='<h1>test url handler (url4)</h1>';
      $content.='<ul><li>first='.$this->param('first').'</li>';
      $content.='<li>second='.$this->param('second').'</li></ul>';
      $rep->body->assign('MAIN',$content);
      return $rep;
   }
   function url5() {
      return $this->getResponse('html',true);
   }
   function url6() {
      $rep = $this->getResponse('html',true);
      $content='<h1>test url : cms2 (url6)</h1>';
      $content.='<ul><li>rubrique='.$this->param('rubrique').'</li>';
      $content.='<li>id_art='.$this->param('id_art').'</li></ul>';
      $rep->body->assign('MAIN',$content);
      return $rep;
   }
   function url7() {
      return $this->getResponse('html',true);
   }
   function url8() {
      return $this->url1('url8');
   }
   function url9() {
      return $this->url1('url9');
   }
   function url10() {
      return $this->url1('url10');
   }

   function url30() {
      $rep = $this->getResponse('html',true);
      $content='<h1>test url 30</h1>';
      $rep->body->assign('MAIN',$content);
      return $rep;
   }
}

?>
