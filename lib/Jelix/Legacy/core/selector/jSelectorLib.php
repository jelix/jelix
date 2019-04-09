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
 * Selector for files stored in the lib directory.
 *
 * @deprecated
 */
class jSelectorLib extends \Jelix\Core\Selector\SimpleFileSelector
{
    protected $type = 'lib';

    public function __construct($sel)
    {
        $this->_basePath = LIB_PATH;
        parent::__construct($sel);
    }
}
