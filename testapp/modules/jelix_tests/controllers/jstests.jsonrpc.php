<?php
/**
* @package     testapp
* @subpackage  testapp module
* @author      laurent Jouanneau
* @contributor
* @copyright   2009 laurent Jouanneau
* @link        http://jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

class jstestsCtrl extends jController {

 
  function first() {
      $rep = $this->getResponse();
      $rep->response = array ("coucou");
      return $rep;
  }

  function second() {
      $rep = $this->getResponse();
      $rep->response = 1564;
      return $rep;
  }
  
}

