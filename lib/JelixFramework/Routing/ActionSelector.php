<?php
/**
 * see Jelix/Core/Selector/SelectorInterface.php for documentation about selectors.
 *
 * @author      Laurent Jouanneau
 * @contributor Thibault Piront (nuKs)
 *
 * @copyright   2005-2012 Laurent Jouanneau
 * @copyright   2007 Thibault Piront
 *
 * @see        https://www.jelix.org
 * @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */
namespace Jelix\Routing;

use Jelix\Core\App;
use Jelix\Core\Selector\Exception;

/**
 * Generic Action selector.
 *
 * main syntax: "module~action@requestType". module should be a valid module name or # (#=says to get
 * the module of the current request). action should be an action name (controller:method or controller_method).
 * all part are optional, but it should have one part at least.
 *
 * @package    jelix
 * @subpackage core_selector
 */
class ActionSelector extends FastActionSelector
{
    protected $forUrl = false;

    /**
     * @param string $sel               the selector
     * @param bool   $enableRequestPart true if the selector can contain the request part
     * @param bool   $toRetrieveUrl     true if the goal to have this selector is to generate an url
     *
     * @throws Exception
     */
    public function __construct($sel, $enableRequestPart = false, $toRetrieveUrl = false)
    {
        $router = App::router();
        $this->forUrl = $toRetrieveUrl;

        // jSelectorAct is called by the url engine parser, before
        // jcoordinator set its properties, so we set a value to avoid a
        // parameter error on jelix_scan_action_sel. the value doesn't matter
        // since the parser call jSelectorAct only for 404 page
        if ($router->actionName === null) {
            $router->actionName = 'default:index';
        }

        if ($this->_scan_act_sel($sel, $router->actionName)) {
            if ($this->module == '#') {
                $this->module = $router->moduleName;
            } elseif ($this->module == '') {
                $this->module = App::getCurrentModule();
            }

            if ($this->request == '' || !$enableRequestPart) {
                if ($router->request) {
                    $this->request = $router->request->type;
                }
                else {
                    // In the context of a cli command, we don't have request object...
                    $this->request = 'classic';
                }
            }

            $this->_createPath();
        } else {
            throw new Exception('jelix~errors.selector.invalid.syntax', array($sel, $this->type));
        }
    }

    protected function _scan_act_sel($selStr, $actionName)
    {
        if (preg_match('/^(?:([a-zA-Z0-9_\\.]+|\\#)~)?([a-zA-Z0-9_:]+|\\#)?(?:@([a-zA-Z0-9_]+))?$/', $selStr, $m)) {
            $m = array_pad($m, 4, '');
            $this->module = $m[1];
            if ($m[2] == '#') {
                $this->resource = $actionName;
            } else {
                $this->resource = $m[2];
            }
            $r = explode(':', $this->resource);
            if (count($r) == 1) {
                $this->controller = 'default';
                $this->method = $r[0] == '' ? 'index' : $r[0];
            } else {
                $this->controller = $r[0] == '' ? 'default' : $r[0];
                $this->method = $r[1] == '' ? 'index' : $r[1];
            }
            $this->resource = $this->controller.':'.$this->method;
            $this->request = $m[3];

            return true;
        }

        return false;
    }

    protected function _createPath()
    {
        $conf = App::config();
        if (isset($conf->_modulesPathList[$this->module])) {
            $p = $conf->_modulesPathList[$this->module];
        } else {
            throw new Exception('jelix~errors.selector.module.unknown', $this->toString());
        }

        $this->_path = $p.'controllers/'.$this->controller.'.'.$this->request.'.php';
    }
}
