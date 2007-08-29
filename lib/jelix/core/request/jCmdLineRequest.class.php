<?php
/**
* @package     jelix
* @subpackage  core_request
* @author      Laurent Jouanneau
* @contributor Loic Mathaud
* @copyright   2005-2006 Laurent Jouanneau, 2006-2007 Loic Mathaud
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

/**
 * a request object for scripts used in a command line
 * @package     jelix
 * @subpackage  core_request
 */
class jCmdLineRequest extends jRequest {

    public $type = 'cmdline';

    public $defaultResponseType = 'text';

    protected function _initUrlDatas(){ 
        global $gJConfig; 
        $this->url_script_path = '/'; 
        $this->url_script_name = $_SERVER['SCRIPT_NAME']; 
        $this->url_path_info = ''; 
    }

    protected function _initParams(){
        global $gJConfig;

        $argv = $_SERVER['argv'];
        $scriptName = array_shift($argv); // shift the script name

        if ($_SERVER['argc'] == 1) {
            $argsel = $gJConfig->startModule.'~'.$gJConfig->startAction;
        } else {
            $argsel = array_shift($argv); // get the module~action selector
            if ($argsel == 'help') {
                $argsel = 'jelix~help_index';
            }
            if (!preg_match('/(?:([\w\.]+)~)/', $argsel)) {
                $argsel = $gJConfig->startModule.'~'.$argsel;
            }
        }

        $selector = new jSelectorAct($argsel);

        $this->params = $argv;
        $this->params['module'] = $selector->module;
        $this->params['action'] = $selector->resource;
    }
}
?>
