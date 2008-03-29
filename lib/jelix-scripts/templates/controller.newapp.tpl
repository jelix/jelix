<?php
/**
* @package
* @subpackage %%module%%
* @author
* @copyright
* @link
* @licence  http://www.gnu.org/licenses/gpl.html GNU General Public Licence, see LICENCE file
*/

class %%name%%Ctrl extends jController {
    /**
    *
    */
    function %%method%%() {
        $rep = $this->getResponse('html');

        // this is a call for the 'welcome' zone after creating a new application
        // remove this line !
        $rep->body->assignZone('MAIN', 'jelix~check_install');

        return $rep;
    }
}
?>
