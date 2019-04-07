<?php
/**
 * see jISelector.iface.php for documentation about selectors.
 *
 * @package     jelix
 * @subpackage  core_selector
 *
 * @author      Laurent Jouanneau
 * @contributor Loic Mathaud
 *
 * @copyright   2005-2012 Laurent Jouanneau, 2007 Loic Mathaud
 *
 * @see        http://www.jelix.org
 * @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */
class jSelectorDaoDb extends jSelectorDao
{
    public function __construct($sel, $driver, $dbType = null)
    {
        $this->driver = $driver;
        $this->dbType = ($dbType ? $dbType : $driver);
        $this->_compiler = 'jDaoCompiler';
        $this->_compilerPath = JELIX_LIB_PATH.'dao/jDaoCompiler.class.php';
        jSelectorModule::__construct($sel);
    }
}
