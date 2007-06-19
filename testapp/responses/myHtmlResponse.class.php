<?php

/**
* @package     jelix
* @subpackage  testapp
* @author      Jouanneau Laurent
* @contributor
* @copyright   2005-2007 Jouanneau laurent
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

require_once (JELIX_LIB_RESPONSE_PATH.'jResponseHtml.class.php');

class myHtmlResponse extends jResponseHtml {


   public $bodyTpl = 'testapp~main';

   // modifications communes aux actions utilisant cette reponses
   protected function _commonProcess(){
       $this->title .= ($this->title !=''?' - ':'').' Test App';

       $this->body->assignZone('menu','testapp~sommaire');
       $this->body->assignIfNone('MAIN','<p>No content</p>');
       $this->body->assignIfNone('page_title','Test App');
       $this->addCSSLink($GLOBALS['gJConfig']->urlengine['basePath'].'design/screen.css');
   }
}
?>