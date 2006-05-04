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


class jCmdLineRequest extends jRequest {

    public $type = 'cmdline';

    public $defaultResponseType = 'text';

    protected function _initParams(){
        if($_SERVER['argc'] < 2){
            die("Error: selector is missing\n");
        }
        $argv = $_SERVER['argv'];

        $scriptName = array_shift($argv); // shift the script name
        $argsel = array_shift($argv); // get the module~action selector
        $selector = new jSelectorAct($argsel);

        $this->params = $argv;
        $this->params['module'] = $selector->module;
        $this->params['action'] = $selector->controller .'_'. $selector->method;
        $this->url  = null; // no URL in command line mode
    }
}
?>
