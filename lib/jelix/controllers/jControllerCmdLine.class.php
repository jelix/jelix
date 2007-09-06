<?php
/**
* @package    jelix
* @subpackage controllers
* @author     Loic Mathaud
* @contributor
* @copyright  2006 Loic Mathaud
* @link        http://www.jelix.org
* @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*
*/

/**
 * a base class for controllers used in command line application
 * @package    jelix
 * @subpackage controllers
 * @since 1.0a3
 */
class jControllerCmdLine extends jController {

    protected $_options;
    protected $_parameters;

    protected $allowed_options;
    protected $allowed_parameters;

    /**
    *
    * @param jRequest $request
    */
    function __construct ($request){
        $this->request = $request;
        $params = $this->request->params;
        unset($params['module']);
        unset($params['action']);
        $action = new jSelectorAct($this->request->params['action']);
        list($this->_options,$this->_parameters) = jCmdUtils::getOptionsAndParams($params,$this->allowed_options[$action->method] , $this->allowed_parameters[$action->method]);

    }

    protected function param ($parName, $parDefaultValue=null, $useDefaultIfEmpty=false){
        if (isset($this->_parameters[$param])) {
            if($this->_parameters[$param] == '' && $useDefaultIfEmpty)
                return $parDefaultValue;
            else
                return $this->_parameters[$param];
        } else {
            return $parDefaultValue;
        }
    }

    protected function option($name) {
        if (isset($this->_options[$name])) {
            return $this->_options[$name];
        } else {
            return false;
        }
    }

}

?>
