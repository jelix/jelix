<?php
/**
* @package     jelix-modules
* @subpackage  jxacl
* @version     $Id$
* @author      Jouanneau Laurent
* @contributor
* @copyright   2006 Jouanneau laurent
* @link        http://www.jelix.org
* @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
*/

class adminCtrl extends jController {

    /**
    *
    */
    function usersgcount() {
        $grpid = $this->intParam('grpid');

        $dao = jDao::create('jaclusergroup');

        $rep = $this->getResponse('text');
        $rep->content= $dao->getUsersGroupCount($grpid);

        return $rep;
    }


}
?>
