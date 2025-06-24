<?php
/**
 * see jISelector.iface.php for documentation about selectors.
 *
 * @package     jelix
 * @subpackage  core_selector
 *
 * @author      Guillaume Dugas
 * @contributor Laurent Jouanneau
 * @copyright   2012 Guillaume Dugas, 2023-2025 Laurent Jouanneau
 *
 * @see        http://www.jelix.org
 * @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

use Jelix\Dao\CustomRecordClassFileInterface;

/**
 * Selector for dao file
 * syntax : "module~daoRecordName".
 * file : daos/daoRecordName.daorecord.php.
 *
 * @package    jelix
 * @subpackage core_selector
 */
class jSelectorDaoRecord extends jSelectorModule implements CustomRecordClassFileInterface
{
    protected $type = 'daorecord';
    protected $_dirname = 'daos/';
    protected $_suffix = '.daorecord.php';

    public function __construct($sel)
    {
        if (jelix_scan_module_sel($sel, $this)) {
            if ($this->module == '') {
                $this->module = jApp::getCurrentModule();
            }
            $this->_createPath();
        } else if (strpos($sel, '\\') !== false && class_exists($sel)) {
            $this->_path = '';
            $this->resource = $sel;
        } else {
            throw new jExceptionSelector('jelix~errors.selector.invalid.syntax', array($sel, $this->type));
        }
        $this->_createCachePath();
    }

    public function toString($full = false)
    {
        if ($this->_path == '') {
            return $this->resource;
        }

        if ($full) {
            return $this->type.':'.$this->module.'~'.$this->resource;
        }

        return $this->module.'~'.$this->resource;
    }

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
        if ($this->_path == '') {
            return $this->resource;
        }
        return $this->module . '~' . $this->resource;
    }

    /**
     * The class name
     * @return string
     */
    public function getClassName()
    {
        if ($this->_path == '') {
            return $this->resource;
        }
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
