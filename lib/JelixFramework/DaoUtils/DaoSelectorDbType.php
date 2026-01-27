<?php
/**
 * @package     jelix
 * @subpackage  core_selector
 *
 * @author      Laurent Jouanneau
 * @contributor Loic Mathaud
 *
 * @copyright   2005-2026 Laurent Jouanneau, 2007 Loic Mathaud
 *
 * @see        http://www.jelix.org
 * @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */
namespace Jelix\DaoUtils;
use Jelix\Core\App;
use Jelix\Core\Selector\ModuleSelector;

/**
 * A Dao file selector to use when we have only the database SQL Type
 */
class DaoSelectorDbType extends DaoSelector
{
    public function __construct($sel, $driverOrSqlType, $sqlType = null)
    {
        $this->dbType = ($sqlType ?: $driverOrSqlType);
        ModuleSelector::__construct($sel);
        $this->escapedName = ucfirst($this->resource);
        $this->escapedModule = ucfirst($this->module);
        $this->buildPath =  App::varLibPath();
    }
}
