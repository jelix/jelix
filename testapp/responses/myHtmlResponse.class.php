<?php

/**
* @package     jelix
* @subpackage  testapp
* @version     $Id$
* @author      Jouanneau Laurent
* @contributor
* @copyright   2005-2006 Jouanneau laurent
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

require_once (JELIX_LIB_RESPONSE_PATH.'jResponseHtml.class.php');

class myHtmlResponse extends jResponseHtml {


   public $bodyTpl = 'testapp~main';
   public $bodyErrorTpl = 'testapp~error';

   // modifications communes aux actions utilisant cette reponses
   protected function _commonProcess(){
       $this->title .= ($this->title !=''?' - ':'').' My Test App !';

       $this->body->assignIfNone('person','you');
       $this->body->assignIfNone('MAIN','<p>No content</p>');
   }
}
?>