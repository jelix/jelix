<?php
/**
 * see jISelector.iface.php for documentation about selectors.
 *
 * @package     jelix
 * @subpackage  core_selector
 *
 * @author      Laurent Jouanneau
 *
 * @copyright   2023 Laurent Jouanneau
 *
 * @see        https://www.jelix.org
 * @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */
use Jelix\Core\App;
/**
 * Action selector for tests
 *
 * it doesn't check the validity of module, controller and method, and may generate fake path
 *
 * @package    jelix
 * @subpackage core_selector
 */
class jSelectorDebugAction extends jSelectorActFast
{
    protected function _createPath()
    {
        if (App::config() && App::isModuleEnabled($this->module)) {
            $this->_path = App::getModulePath($this->module).'controllers/'.$this->controller.'.'.$this->request.'.php';
        }
        else {
            $this->_path = $this->module.'/controllers/'.$this->controller.'.'.$this->request.'.php';
        }

    }

}
