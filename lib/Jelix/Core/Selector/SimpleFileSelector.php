<?php
/**
 * see Jelix/Core/Selector/SelectorInterface.php for documentation about selectors.
 *
 * @author      Laurent Jouanneau
 * @copyright   2005-2020 Laurent Jouanneau
 *
 * @see        http://www.jelix.org
 * @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

namespace Jelix\Core\Selector;

/**
 * base class for simple file selector.
 */
class SimpleFileSelector implements SelectorInterface
{
    protected $type = 'simplefile';
    public $file = '';
    protected $_path;
    protected $_basePath = '';

    public function __construct($sel)
    {
        if (preg_match('/^([\\w_\\-\\.\\/]+)$/', $sel, $m)) {
            $this->file = $m[1];
            $this->_path = $this->_basePath.$m[1];
        } else {
            throw new Exception('jelix~errors.selector.invalid.syntax', array($sel, $this->type));
        }
    }

    public function getPath()
    {
        return $this->_path;
    }

    public function toString($full = false)
    {
        if ($full) {
            return $this->type.':'.$this->file;
        }

        return $this->file;
    }

    public function getCompiler()
    {
        return null;
    }

    public function useMultiSourceCompiler()
    {
        return false;
    }

    public function getCompiledFilePath()
    {
        return '';
    }
}
