<?php
/**
* @package     testapp
* @subpackage  unittest module
* @version     $Id$
* @author      Jouanneau Laurent
* @contributor
* @copyright   2006 Jouanneau laurent
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/


class AGUrlsig extends jActionGroup {

   function url1() {
      return $this->getResponse('testunit');
   }
   function url2() {
      return $this->getResponse('testunit');
   }
   function url3() {
      return $this->getResponse('testunit');
   }
   function url4() {
      return $this->getResponse('testunit');
   }
   function url5() {
      return $this->getResponse('testunit');
   }
}

?>
