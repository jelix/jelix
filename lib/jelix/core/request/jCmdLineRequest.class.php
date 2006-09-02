<?php
/**
* @package     jelix
* @subpackage  core
* @version     $Id$
* @author      Jouanneau Laurent
* @contributor Loic Mathaud
* @copyright   2005-2006 Jouanneau laurent
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

/**
 * a request object for scripts used in a command line
 */
class jCmdLineRequest extends jRequest {

    public $type = 'cmdline';

    public $defaultResponseType = 'text';

    protected function _initParams(){
        global $gJConfig;

        $argv = $_SERVER['argv'];
        $scriptName = array_shift($argv); // shift the script name

        if ($_SERVER['argc'] == 1) {
            $argsel = $gJConfig->defaultModule.'~'.$gJConfig->defaultAction;
        } else {
            $argsel = array_shift($argv); // get the module~action selector
            if ($argsel == 'help') {
                $argsel = 'jelix~help_index';
            }
            if (!preg_match('/(?:([\w\.]+)~)/', $argsel)) {
                $argsel = $gJConfig->defaultModule.'~'.$argsel;
            }
        }

        $selector = new jSelectorAct($argsel);

        $this->params = $argv;
        $this->params['module'] = $selector->module;
        $this->params['action'] = $selector->ressource;
    }
}
?>