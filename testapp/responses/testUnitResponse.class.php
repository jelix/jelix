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

class testUnitResponse extends jResponseHtml {


    public $bodyTpl = 'unittest~accueil';

    protected function _commonProcess(){
       $this->title .= ($this->title !=''?' - ':'').' Test unitaires';
       $this->addCSSLink('/unittest.css');
    }


}
?>