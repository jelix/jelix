<?php
/**
 * @author      Laurent Jouanneau
 * @copyright   2005-2016 Laurent Jouanneau
 *
 * @see        http://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

namespace Jelix\Routing\UrlMapping;

use Jelix\Core\App;

/**
 * a specific selector for user url handler.
 */
class SelectorUrlHandler extends \jSelectorClass
{
    public $type = 'urlhandler';
    protected $_suffix = '.urlhandler.php';

    protected function _createPath()
    {
        if (App::isModuleEnabled($this->module)) {
            $p = App::getModulePath($this->module);
        } else {
            throw new \jExceptionSelector('jelix~errors.selector.module.unknown', $this->toString());
        }
        $this->_path = $p.$this->_dirname.$this->subpath.$this->className.$this->_suffix;

        if (!file_exists($this->_path) || strpos($this->subpath, '..') !== false) { // second test for security issues
            throw new \jExceptionSelector('jelix~errors.selector.invalid.target', array($this->toString(), $this->type));
        }
    }
}
