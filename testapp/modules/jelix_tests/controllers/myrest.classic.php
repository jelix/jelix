<?php
/**
* @package     testapp
* @subpackage  testapp module
* @author      Laurent Jouanneau
* @copyright   2010 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

class myrestCtrl extends jController implements  jIRestController {
    public function get() {
      $rep = $this->getResponse('text');
      $rep->content= 'this is a GET response. resturl='.jUrl::get('jelix_tests~myrest:@classic');
      return $rep;
    }
    public function post() {
      $received = '';
      foreach($this->request->params as $n=>$v) {
        $received .= " $n=$v";
      }
      
      $rep = $this->getResponse('text');
      $rep->content= 'this is a POST response.'.$received;
      return $rep;
    }
    public function put() {
      $received = '';
      foreach($this->request->params as $n=>$v) {
        $received .= " $n=$v";
      }
      $rep = $this->getResponse('text');
      $rep->content= 'this is a PUT response.'.$received;
      return $rep;
    }
    public function delete() {
      $rep = $this->getResponse('text');
      $rep->content= 'this is a DELETE response';
      return $rep;
    }
}
