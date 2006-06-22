<?php
/**
* @package     jelix-modules
* @subpackage  users
* @version     $Id$
* @author      Jouanneau Laurent
* @contributor
* @copyright   2006 Jouanneau laurent
* @link        http://www.jelix.org
* @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
*/

class CTadmin extends jController {
    /**
    *
    */
    function rightslist() {
        $rep = $this->getResponse('rdf');
        $grpid = $this->param('grpid');

        $srv= jClasses::getService('aclservice');

        $rep->datas = $srv->getGroupRights($grpid);
        $rep->asAttribute = array('id_aclsbj','id_aclvalgrp','value','id_aclres');
        $rep->asElement = array('label','value_label');

        $rep->resNs="http://jelix.org/ns/rights#";
        $rep->resNsPrefix='r';
        $rep->resUriPrefix = "urn:data:row:";
        $rep->resUriRoot = "urn:data:row";

        return $rep;
    }

}
?>
