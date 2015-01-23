<?php

/**
* @package     jelix
* @subpackage  testapp
* @author      Laurent Jouanneau
* @contributor
* @copyright   2005-2007 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

require_once (JELIX_LIB_CORE_PATH.'response/jResponseHtml.class.php');

class myHtmlResponse extends jResponseHtml {


   public $bodyTpl = 'testapp~main';

   // modifications communes aux actions utilisant cette reponses
   protected function doAfterActions(){
       $this->title .= ($this->title !=''?' - ':'').' Test App';

       $this->body->assignZone('menu','testapp~sommaire');
       $this->body->assignIfNone('MAIN','<p>No content</p>');
       $this->body->assignIfNone('page_title','Test App');
       $this->addCSSLink(jApp::urlBasePath().'design/screen.css');
   }
}
?>