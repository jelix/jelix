<?php
/**
 * @author      Laurent Jouanneau
 * @copyright   2018 Laurent Jouanneau
 *
 * @see        http://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

namespace Jelix\Installer\Module;

/**
 * Base class for installers and uninstallers.
 *
 * @since 1.7
 */
abstract class InstallerAbstract
{
    use InstallConfigTrait;

    /**
     * @var string the default profile name for the component, if it exist. keep it to '' if not
     */
    protected $defaultDbProfile = '';

    /**
     * @param string $componentName   name of the component
     * @param string $name            name of the installer
     * @param string $path            the component path
     * @param string $version         version of the component
     */
    public function __construct($componentName, $name, $path, $version)
    {
        $this->path = $path;
        $this->version = $version;
        $this->name = $name;
        $this->componentName = $componentName;
    }

    public function getDefaultDbProfile()
    {
        return $this->defaultDbProfile;
    }
}
