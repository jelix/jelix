<?php
/**
* @package    jelix-modules
* @subpackage jelix
* @author     Loic Mathaud
* @copyright  2006 Loic Mathaud
* @licence    http://www.gnu.org/licenses/gpl.html GNU General Public Licence, see LICENCE file
*/

/**
 * @package    jelix-modules
 * @subpackage jelix
 */
class helpCtrl extends jControllerCmdLine {
    protected $allowed_options = array(
            'index' => array());

    protected $allowed_parameters = array(
            'index' => array('cmd_name' => false));

    /**
    *
    */
    public function index() {
        global $gJConfig;

        $rep = $this->getResponse('text');

        $cmd_name = $this->param('cmd_name');

        if (empty($cmd_name)) {
            $rep->content = "
General purpose:
    php cmdline.php help [COMMAND]

    COMMAND : name of the command to launch
               'module~controller:action' or more simply
               'controller:action' or 'action', depending of the app configuration
";
        } else {
            if (!preg_match('/(?:([\w\.]+)~)/', $cmd_name)) {
                $cmd_name = $gJConfig->startModule.'~'.$cmd_name;
            }
            $selector = new jSelectorAct($cmd_name);

            include($selector->getPath());
            $ctrl = $selector->getClass();
            $ctrl = new $ctrl();
            $help = $ctrl->help;

            $rep->content = "
Use of the command ". $selector->method ." :
";
            if (isset($help[$selector->method])) {
                $rep->content .= $help[$selector->method]."\n\n";
            } else {
                $rep->content .= "\tNo available help for this command\n\n";
            }
        }
        return $rep;
    }
}
?>
