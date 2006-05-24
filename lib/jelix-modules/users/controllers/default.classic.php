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

class CTdefault extends jController {
    /**
    *
    */
    function index() {
        $rep = $this->getResponse('xulpage');
        $rep->bodyTpl='users~adminusers';
        return $rep;
    }


    function xaovlay(){
        $rep = $this->getResponse('xuloverlay');
        $rep->bodyTpl = 'users~xaovlay';
        return $rep;

    }

}
?>
