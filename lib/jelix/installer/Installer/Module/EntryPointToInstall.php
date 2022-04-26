<?php
/**
 * @author      Laurent Jouanneau
 * @copyright   2022 Laurent Jouanneau
 *
 * @see        http://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

namespace Jelix\Installer\Module;

use Jelix\Core\Infos\EntryPoint;

/**
 * Define an entry point to install or to uninstall
 * @since 1.7.11
 */
class EntryPointToInstall extends EntryPoint
{
    protected $entryPointFileToCopy;

    protected $configFileToCopy;

    public function __construct($id, $configFileName,
                                $entryPointFileToCopy,
                                $configFileToCopy,
                                $type = 'classic')
    {
        parent::__construct($id, $configFileName, $type);
        $this->entryPointFileToCopy = $entryPointFileToCopy;
        $this->configFileToCopy = $configFileToCopy;
    }

    public function getEntryPointFileToCopy()
    {
        return $this->entryPointFileToCopy;
    }

    public function getConfigFileToCopy()
    {
        return $this->configFileToCopy;
    }

}