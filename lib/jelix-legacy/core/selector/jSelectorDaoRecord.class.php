<?php
/**
 * see Jelix/Core/Selector/SelectorInterface.php for documentation about selectors.
 *
 * @package     jelix
 * @subpackage  core_selector
 *
 * @author      Guillaume Dugas
 * @contributor Laurent Jouanneau
 * @copyright   2012 Guillaume Dugas, 2021 Laurent Jouanneau
 *
 * @see        http://www.jelix.org
 * @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

/**
 * Selector for dao file
 * syntax : "module~daoRecordName".
 * file : daos/daoRecordName.daorecord.php.
 *
 * @package    jelix
 * @subpackage core_selector
 */
class jSelectorDaoRecord extends \Jelix\Core\Selector\ModuleSelector implements \Jelix\Dao\CustomRecordClassFileInterface
{
    protected $type = 'daorecord';
    protected $_dirname = 'daos/';
    protected $_suffix = '.daorecord.php';

    protected function _createCachePath()
    {
        $this->_cachePath = '';
    }

    /**
     * A name that allow to identify easily the dao.
     *
     * @return string a filename, a URI or another identifier
     */
    function getName()
    {
        return $this->module . '~' . $this->resource;
    }

    /**
     * The class name
     * @return string
     */
    public function getClassName()
    {
        return $this->resource.'DaoRecord';
    }

    /**
     * Path of the PHP file containing the class. It can be empty if the class
     * can be autoloaded
     *
     * @return string
     */
    public function getPath()
    {
        return $this->_path;
    }

}
