<?php
/**
* @package     jelix-modules
* @subpackage  jxacl
* @author      Jouanneau Laurent
* @contributor
* @copyright   2006 Jouanneau laurent
* @link        http://www.jelix.org
* @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
*/

class xuladminCtrl extends jController {
    /**
    *
    */
    function index() {
        $daogroup = jDao::get('jelix~jaclgroup');
        $daovaluegroups = jDao::get('jelix~jaclrightvaluesandgroup');

        $rep = $this->getResponse('xulpage');
        $rep->bodyTpl='jxacl~xuladmin';

        $rep->body->assign('groups', $daogroup->findAllPublicGroup());
        $rep->body->assign('valuegroups', $daovaluegroups->findAll());
        return $rep;
    }


    function xaovlay(){
        $rep = $this->getResponse('xuloverlay');
        $rep->bodyTpl = 'jxacl~xaovlay';
        return $rep;
    }

    function jxauthovlay(){
        $rep = $this->getResponse('xuloverlay');
        $rep->bodyTpl = 'jxacl~jxauthovlay';
        $daogroup = jDao::get('jelix~jaclgroup');
        $rep->body->assign('groups', $daogroup->findAllPublicGroup());
        return $rep;
    }

}
?>
