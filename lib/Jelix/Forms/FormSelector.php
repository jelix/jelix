<?php
/**
 * see Jelix/Core/Selector/SelectorInterface.php for documentation about selectors.
 *
 * @author      Laurent Jouanneau
 * @copyright   2005-2024 Laurent Jouanneau
 *
 * @see        https://www.jelix.org
 * @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */
namespace Jelix\Forms;

use Jelix\Core\App;
use Jelix\Core\Selector\Exception;

/**
 * Form selector.
 *
 * syntax : "module~formName".
 * file : forms/formName.form.xml .
 */
class FormSelector extends \Jelix\Core\Selector\ModuleSelector
{
    protected $type = 'form';
    protected $_dirname = 'forms/';
    protected $_suffix = '.form.xml';

    protected $escapedModule;
    protected $escapedName;

    public function __construct($sel)
    {
        parent::__construct($sel);
        $this->escapedName = ucfirst($this->resource);
        $this->escapedModule = ucfirst($this->module);
    }

    protected function _createPath()
    {
        if (!App::isModuleEnabled($this->module)) {
            throw new Exception('jelix~errors.selector.module.unknown', $this->toString(true));
        }
        // nothing, useless
    }

    public function getClass()
    {
        return 'Jelix\\BuiltComponents\\Forms\\'.$this->escapedModule.'\\'.$this->escapedName;
    }


    protected function _createCachePath()
    {
        // nothing, useless
    }
}
