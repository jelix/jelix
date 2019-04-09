<?php
/**
 * see Jelix/Core/Selector/SelectorInterface.php for documentation about selectors.
 *
 * @author      Laurent Jouanneau
 * @copyright   2005-2014 Laurent Jouanneau
 *
 * @see        http://www.jelix.org
 * @licence     MIT
 */

/**
 * Selector for files stored in the var directory.
 *
 * @deprecated
 */
class jSelectorVar extends \Jelix\Core\Selector\SimpleFileSelector
{
    protected $type = 'var';

    public function __construct($sel)
    {
        $this->_basePath = \Jelix\Core\App::varPath();
        parent::__construct($sel);
    }
}
