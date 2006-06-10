<?php
/**
* @package     jelix-modules
* @subpackage  acl
* @version     $Id$
* @author      Jouanneau Laurent
* @contributor
* @copyright   2006 Jouanneau laurent
* @link        http://www.jelix.org
* @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
*/

class CTxuladmin extends jController {
    /**
    *
    */
    function index() {
        $daogroup = jDao::get('jaclgroup');

        $rep = $this->getResponse('xulpage');
        $rep->bodyTpl='acl~xuladmin';


        $rep->body->assign('groups', $daogroup->findAllPublicGroup());
        return $rep;
    }


    function xaovlay(){
        $rep = $this->getResponse('xuloverlay');
        $rep->bodyTpl = 'acl~xaovlay';
        return $rep;

    }

}
?>
