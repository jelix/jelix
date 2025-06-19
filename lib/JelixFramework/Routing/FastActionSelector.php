<?php
/**
 * see Jelix/Core/Selector/SelectorInterface.php for documentation about selectors.
 *
 * @author      Laurent Jouanneau
 * @contributor Thibault Piront (nuKs)
 *
 * @copyright   2005-2023 Laurent Jouanneau
 * @copyright   2007 Thibault Piront
 *
 * @see        https://www.jelix.org
 * @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */
namespace Jelix\Routing;

use Jelix\Core\App;
use Jelix\Core\Selector\ModuleSelector;
use Jelix\Core\Selector\Exception;

/**
 * Special Action selector for the router
 * Don't use it ! Only for internal purpose.
 *
 * @internal
 */
class FastActionSelector extends ModuleSelector implements ActionSelectorInterface
{
    protected $type = 'act';
    /**
     * @var string type of request
     */
    public $request = '';
    public $controller = '';
    public $method = '';
    protected $_dirname = 'actions/';

    /**
     * @var string type of request ('classic', 'soap'...)
     *
     * @param $module
     * @param $action
     * @param mixed $requestType
     *
     * @throws Exception
     */
    public function __construct($requestType, $module, $action)
    {
        $this->module = $module;
        $r = explode(':', $action);
        if (count($r) == 1) {
            $this->controller = 'default';
            $this->method = $r[0] == '' ? 'index' : $r[0];
        } else {
            $this->controller = $r[0] == '' ? 'default' : $r[0];
            $this->method = $r[1] == '' ? 'index' : $r[1];
        }
        if (substr($this->method, 0, 2) == '__') {
            throw new Exception('jelix~errors.selector.method.invalid', $this->toString());
        }
        $this->resource = $this->controller.':'.$this->method;
        $this->request = $requestType;
        $this->_createPath();
    }

    protected function _createPath()
    {
        if (!App::isModuleEnabled($this->module)) {
            throw new Exception('jelix~errors.selector.module.unknown', $this->toString());
        }
        $this->_path = App::getModulePath($this->module).'controllers/'.$this->controller.'.'.$this->request.'.php';
    }

    protected function _createCachePath()
    {
        $this->_cachePath = '';
    }

    public function toString($full = false)
    {
        if ($full) {
            return $this->type.':'.$this->module.'~'.$this->resource.'@'.$this->request;
        }

        return $this->module.'~'.$this->resource.'@'.$this->request;
    }

    public function getClass()
    {
        return $this->controller.'Ctrl';
    }

    public function isEqualTo(ActionSelectorInterface $otherAction)
    {
        return $this->module == $otherAction->module
                && $this->controller == $otherAction->controller
                && $this->method == $otherAction->method
                && $this->request == $otherAction->request
                ;
    }
}
