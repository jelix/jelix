<?php
/**
* see Jelix/Core/Selector/SelectorInterface.php for documentation about selectors.
*
* @author      Laurent Jouanneau
* @copyright   2016 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     MIT
*/

/**
 * Selector for files stored in the app/config directory
 *
 */
class jSelectorAppCfg extends \Jelix\Core\Selector\SimpleFileSelector {
    protected $type = 'appcfg';
    function __construct($sel){
        $this->_basePath = \Jelix\Core\App::appConfigPath();
        parent::__construct($sel);
    }
}
