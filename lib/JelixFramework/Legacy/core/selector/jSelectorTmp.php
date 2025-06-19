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
 * Selector for files stored in the temp directory.
 *
 * @deprecated
 */
class jSelectorTmp extends \Jelix\Core\Selector\SimpleFileSelector
{
    protected $type = 'tmp';

    public function __construct($sel)
    {
        $this->_basePath = \Jelix\Core\App::tempPath();
        parent::__construct($sel);
    }
}
