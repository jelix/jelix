<?php
/**
* @package     testapp
* @subpackage  test app module
* @version     $Id$
* @author      Jouanneau Laurent
* @contributor
* @copyright   2005-2006 Jouanneau laurent
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

class ZoneTest extends jZone {
   protected $_tplname='testzone';


    protected function _prepareTpl(){

        $dao = jDAO::get('config');

        $this->_tpl->assign('config',$dao->findAll());
        $this->_tpl->assign('oneconf',$dao->get('foo'));
        $this->_tpl->assign('nombre',$dao->countAll());
        $this->_tpl->assign('nombrevalue',$dao->getCountValue());

    }

}

?>