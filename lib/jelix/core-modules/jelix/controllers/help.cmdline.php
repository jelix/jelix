<?php
/**
* @package jelix-modules
* @subpackage jelix
* @version  $Id:$
* @author Loic Mathaud
* @copyright 2006 Loic Mathaud
* @link
* @licence  http://www.gnu.org/licenses/gpl.html GNU General Public Licence, see LICENCE file
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
Utilisation générale :
    php cmdline.php help [COMMANDE]

    COMMANDE : nom de la commande à lancer
               'module~controller_action' ou plus simplement
               'action' en fonction de la configuration de l'application
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
Utilisation de la commande ". $selector->method ." :
";
            if (isset($help[$selector->method])) {
                $rep->content .= $help[$selector->method]."\n\n";
            } else {
                $rep->content .= "\tPas d'aide disponible pour cette commande\n\n";
            }
        }
        return $rep;
    }
}
?>
