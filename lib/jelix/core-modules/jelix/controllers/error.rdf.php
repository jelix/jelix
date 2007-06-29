<?php
/**
* @package jelix-modules
* @subpackage jelix
* @author Laurent Jouanneau
* @copyright 2006 Laurent Jouanneau
* @link
* @licence  http://www.gnu.org/licenses/gpl.html GNU General Public Licence, see LICENCE file
*/

class errorCtrl extends jController {

    /**
    * 404 error page
    */
    public function notfound() {
        $rep = $this->getResponse('rdf', true);
        $rep->datas = array(  array('name'=>'error', 'error'=>'404 not found (wrong action)'));
        $rep->setHttpStatus('404', 'Not Found');

        return $rep;
    }
}
?>
