<?php
/**
* @package     jelix-modules
* @subpackage  jxxulapp
* @version     $Id$
* @author      Jouanneau Laurent
* @contributor
* @copyright   2006 Jouanneau laurent
* @link        http://www.jelix.org
* @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
*/

class defaultCtrl extends jController {
    /**
    *
    */
    function index() {
        $rep = $this->getResponse('xul');
        $rep->bodyTpl='jxxulapp~main';
        $rep->fetchOverlays = true;
        return $rep;
    }

    function login() {
        $rep = $this->getResponse('xul');
        $rep->bodyTpl='jxxulapp~login';
        return $rep;
    }

}
?>
