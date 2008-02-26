<?php
/**
* @package
* @subpackage testapp
* @author
* @copyright
* @link
* @licence  http://www.gnu.org/licenses/gpl.html GNU General Public Licence, see LICENCE file
*/

class defaultCtrl extends jControllerCmdLine {
    protected $allowed_options = array(
            'index' => array());

    protected $allowed_parameters = array(
            'index' => array());

    /**
    *
    */
    function index() {
        $rep = $this->getResponse();
        $rep->content = "Hello, it works !";
        return $rep;
    }
}
?>
