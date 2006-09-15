<?php
/**
* @package     jelix-modules
* @subpackage  jxauth
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
        $rep = $this->getResponse('xulpage');
        $rep->bodyTpl='jxauth~xuladmin';
        return $rep;
    }


    function xaovlay(){
        $rep = $this->getResponse('xuloverlay');
        $rep->bodyTpl = 'jxauth~xaovlay';
        return $rep;

    }

}
?>
