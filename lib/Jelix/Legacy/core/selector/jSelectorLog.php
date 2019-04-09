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
 * Selector for files stored in the log directory.
 *
 * @deprecated
 */
class jSelectorLog extends \Jelix\Core\Selector\SimpleFileSelector
{
    protected $type = 'log';

    public function __construct($sel)
    {
        $this->_basePath = \Jelix\Core\App::logPath();
        parent::__construct($sel);
    }
}
