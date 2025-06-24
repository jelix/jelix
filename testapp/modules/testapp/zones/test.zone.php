<?php

use Jelix\Dao\DaoConditions;

/**
* @package     testapp
* @subpackage  test app module
* @version     $Id$
* @author      Laurent Jouanneau
* @contributor
* @copyright   2005-2006 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

class testZone extends jZone {
   protected $_tplname='testzone';


    protected function _prepareTpl(){

        $dao = jDao::get('config');

        $this->_tpl->assign('config',$dao->findAll());
        $this->_tpl->assign('oneconf',$dao->get('foo'));
        $this->_tpl->assign('nombre',$dao->countAll());
        $this->_tpl->assign('nombrevalue',$dao->getCountValue());

        $cond = new DaoConditions('or');
        $cond->addCondition('ckey','=','foo');
        $cond->addCondition('ckey','=','bar');

        $this->_tpl->assign('petitconfig',$dao->findBy($cond));


    }

}

?>