<?php
/**
* @package     myapp
* @subpackage  myappmodule
* @version     $Id$
* @author      Jouanneau Laurent
* @contributor
* @copyright   2005-2006 Jouanneau laurent
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

class AGMain extends jActionGroup {

  function getDefault(){
      $rep = $this->_getResponse('hello');
      $rep->bodyTpl = 'myapp~hello';
      return $rep;
   }

}

?>