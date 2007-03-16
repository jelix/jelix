<?php
/**
* @package
* @subpackage {$module}
* @author
* @copyright
* @link
* @licence  http://www.gnu.org/licenses/gpl.html GNU General Public Licence, see LICENCE file
*/

class {$name}Ctrl extends jControllerCmdLine {
    protected $allowed_options = array(
            '{$method}' => array());
    
    protected $allowed_parameters = array(
            '{$method}' => array());
    /**
    *
    */
    function {$method}() {
        $rep = $this->getResponse('text');
        
        return $rep;
    }
}
?>
