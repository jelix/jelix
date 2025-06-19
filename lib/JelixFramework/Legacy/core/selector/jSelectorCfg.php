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
 * Selector for files stored in the config directory.
 *
 * @deprecated
 */
class jSelectorCfg extends \Jelix\Core\Selector\SimpleFileSelector
{
    protected $type = 'cfg';

    public function __construct($sel)
    {
        $this->_basePath = \Jelix\Core\App::varConfigPath();
        parent::__construct($sel);
    }
}
