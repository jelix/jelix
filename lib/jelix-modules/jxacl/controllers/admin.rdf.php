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

class adminCtrl extends jController {
    /**
    *
    */
    function rightslist() {
        $rep = $this->getResponse('rdf');
        $grpid = $this->intParam('grpid');

        $srv= jClasses::getService('aclservice');

        $rep->datas = $srv->getGroupRights($grpid);
        /*$rep->asAttribute = array('id_aclsbj','id_aclvalgrp','value','id_aclres');
        $rep->asElement = array('label','value_label');

        $rep->resNs="http://jelix.org/ns/rights#";
        $rep->resNsPrefix='r';
        $rep->resUriPrefix = "urn:data:row:";
        $rep->resUriRoot = "urn:data:row";*/
        $rep->template='rights.rdf';

        return $rep;
    }

    /**
    *
    */
    function userslist() {
        $rep = $this->getResponse('rdf');
        $grpid = $this->intParam('grpid');
        $offset = $this->intParam('offset');
        $count= $this->intParam('count');

        $dao = jDao::create('jaclusergroup');
        $rep->datas = $dao->getUsersGroupLimit($grpid, $offset, $count);

        $rep->asAttribute = array('login');

        $rep->resNs="http://jelix.org/ns/usersgroup#";
        $rep->resNsPrefix='ug';
        $rep->resUriPrefix = "urn:data:row:";
        $rep->resUriRoot = "urn:data:row";

        return $rep;
    }


}
?>
