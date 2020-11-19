<?php
/**
 * see jISelector.iface.php for documentation about selectors. Here abstract class for many selectors.
 *
 * @package     jelix
 * @subpackage  core_selector
 *
 * @author      Laurent Jouanneau
 * @copyright   2005-2018 Laurent Jouanneau
 *
 * @see        http://www.jelix.org
 * @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

/**
 * Selector for files stored in the log directory.
 *
 * @package    jelix
 * @subpackage core_selector
 */
class jSelectorLog extends jSelectorSimpleFile
{
    protected $type = 'log';

    public function __construct($sel)
    {
        $this->_basePath = jApp::logPath();
        parent::__construct($sel);
    }
}
