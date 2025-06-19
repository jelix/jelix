<?php
/**
 * @author      Laurent Jouanneau
 * @copyright   2018-2022 Laurent Jouanneau
 *
 * @see        http://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

namespace Jelix\Installer\Module;

use Jelix\Installer\Module\API\ConfigurationHelpers;
use Jelix\Installer\Module\API\LocalConfigurationHelpers;
use Jelix\Installer\Module\API\PreConfigurationHelpers;
use Jelix\Routing\UrlMapping\EntryPointUrlModifier;
use Jelix\Routing\UrlMapping\MapEntry\MapInclude;
use Jelix\Routing\UrlMapping\MapEntry\ModuleUrl;

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
     *                    Target can be an array containing the path and a boolean
     *                    indicating if, when the target already exists, it should be
     *                    overwritten (true) or not (false). By default it is overwritten.
     */
    public function getFilesToCopy()
    {
        return array();
    }

    /**
     * List of entrypoint to create
     *
     * Return the list of entrypoint that your module need to install.
     * No need to call yourself ConfigurationHelpers::createEntryPoint()
     * and ConfigurationHelpers::removeEntryPoint().
     * These entrypoints will be removed automatically when you will
     * deconfigure the module.
     *
     * @return EntryPointToInstall[]
     * @since 1.7.11
     */
    public function getEntryPointsToCreate()
    {
        return array();
    }

    /**
     * List of Url to declare on entrypoints.
     *
     * By default, it searches an urls.xml file in the module, and if there is
     * one, it attach it on the main entrypoint with the module name as path info.
     * Else it declares the module on the main entrypoint, to have automatic
     * urls.
     * If you want to setup urls on specific entrypoints, you must override
     * this method.
     *
     * @param EntryPointUrlModifier $registerOnEntryPoint
     * @return void
     */
    public function declareUrls(EntryPointUrlModifier $registerOnEntryPoint)
    {
        if (file_exists($this->path.'urls.xml')) {
            $registerOnEntryPoint->havingName(
                'index',
                array(
                    new MapInclude('urls.xml', '/'.$this->componentName),
                )
            );
        }
        else {
            $registerOnEntryPoint->havingName(
                'index',
                array(
                    new ModuleUrl('/'.$this->componentName),
                )
            );
        }
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
