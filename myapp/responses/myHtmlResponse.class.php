<?php

/**
* @package     jelix
* @subpackage  myapp
* @version     $Id$
* @author      Jouanneau Laurent
* @contributor
* @copyright   2005-2006 Jouanneau laurent
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

require_once (JELIX_LIB_RESPONSE_PATH.'jResponseHtml.class.php');

class myHtmlResponse extends jResponseHtml {


	public $bodyTpl = 'myapp~main';
    public $bodyErrorTpl = 'myapp~error';



    // à surcharger dans les classes héritières
    protected function _commonProcess(){

       $this->title .= ($this->title !=''?' - ':'').' My Sample App !';
    }


}
?>