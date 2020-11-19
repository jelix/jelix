<?php
/**
 * @author      Laurent Jouanneau
 * @copyright   2018 Laurent Jouanneau
 *
 * @see        http://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

namespace Jelix\Installer\Module;

use Jelix\Installer\Module\API\ConfigurationHelpers;
use Jelix\Installer\Module\API\LocalConfigurationHelpers;
use Jelix\Installer\Module\API\PreConfigurationHelpers;

/**
 * Base class for classes which configure a module.
 *
 * @since 1.7
 */
class Configurator implements ConfiguratorInterface
{
    use InstallConfigTrait;

    /**
     * @var string the version for which the installer is called
     */
    private $version = '0';

    /**
     * @param string $componentName name of the component
     * @param string $name          name of the installer
     * @param string $path          the component path
     * @param string $version       version of the component
     */
    public function __construct($componentName, $name, $path, $version)
    {
        $this->path = $path;
        $this->version = $version;
        $this->name = $name;
        $this->componentName = $componentName;
    }

    final public function getVersion()
    {
        return $this->version;
    }

    // ----- ConfiguratorInterface implementation

    /**
     * {@inheritdoc}
     */
    public function getDefaultParameters()
    {
        return array();
    }

    /**
     * List of files or directories to copy.
     *
     * @return string[]
     *                  - keys are relative path to the install/ directory of the module
     *                  - values are target path. Shortcut allowed ('www:', 'config:', 'var:', 'temp:', 'log:')
     */
    public function getFilesToCopy()
    {
        return array();
    }

    /**
     * {@inheritdoc}
     */
    public function preConfigure(PreConfigurationHelpers $helpers)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function configure(ConfigurationHelpers $helpers)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function localConfigure(LocalConfigurationHelpers $helpers)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function postConfigure(ConfigurationHelpers $helpers)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function preUnconfigure(PreConfigurationHelpers $helpers)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function unconfigure(ConfigurationHelpers $helpers)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function localUnconfigure(LocalConfigurationHelpers $helpers)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function postUnconfigure(ConfigurationHelpers $helpers)
    {
    }
}
