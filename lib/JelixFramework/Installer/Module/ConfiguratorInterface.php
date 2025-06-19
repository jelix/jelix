<?php
/**
 * @author      Laurent Jouanneau
 * @copyright   2018-2022 Laurent Jouanneau
 *
 * @see        http://jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

namespace Jelix\Installer\Module;

use Jelix\Installer\Module\API\ConfigurationHelpers;
use Jelix\Installer\Module\API\LocalConfigurationHelpers;
use Jelix\Installer\Module\API\PreConfigurationHelpers;

/**
 * Interface for classes which configure a module.
 *
 * @since 1.7
 */
interface ConfiguratorInterface
{
    /**
     * List of possible installation parameters with their default values.
     *
     * @return array
     */
    public function getDefaultParameters();

    /**
     * indicates installation parameters to use.
     *
     * can be called whether the interactive mode is enabled or not
     * (but before preConfigure())
     *
     * @param array $parameters
     */
    public function setParameters($parameters);

    /**
     * return list of installation parameters.
     *
     * @return array
     */
    public function getParameters();

    /**
     * called before configuration of any modules.
     *
     * This is the opportunity to check some things. Throw an exception to
     * interrupt the configuration process.
     *
     * @throws \Exception if the module cannot be configured
     */
    public function preConfigure(PreConfigurationHelpers $helpers);

    /**
     * Configure the module.
     *
     * You can set some configuration parameters in the application configuration
     * files, you can also copy some files into the application, setup the
     * urls mapping etc.
     *
     * @throws \Exception if the module cannot be configured
     */
    public function configure(ConfigurationHelpers $helpers);

    /**
     * Configure the module in the context of the local configuration.
     *
     * You can set some configuration parameters in the local configuration
     * files, you can also copy some files into the application, setup the
     * urls mapping etc.
     *
     * It is useful when the module is installed natively into the application,
     * but you want to do additional configuration setup in an instance of
     * the application, that depends on the local context of the application
     *
     * @throws \Exception if the module cannot be configured
     */
    public function localConfigure(LocalConfigurationHelpers $helpers);

    /**
     * called after the configuration of all modules.
     */
    public function postConfigure(ConfigurationHelpers $helpers);

    /**
     * called before unconfiguration of any modules.
     *
     * This is the opportunity to check some things. Throw an exception to
     * interrupt the configuration process.
     *
     * @throws \Exception if the module cannot be unconfigured
     */
    public function preUnconfigure(PreConfigurationHelpers $helpers);

    /**
     * Unconfigure the module.
     *
     * You can remove some configuration parameters from the application
     * parameters that are not needed for the uninstaller. You can
     * also delete some files you installed into the configure() method,
     * remove the url mapping etc..
     *
     * @throws \Exception if the module cannot be unconfigured
     */
    public function unconfigure(ConfigurationHelpers $helpers);

    /**
     * Unconfigure the module in the context of the local configuration.
     *
     * You can remove some configuration parameters from the application
     * parameters that are not needed for the uninstaller. You can
     * also delete some files you installed into the configure() method,
     * remove the url mapping etc..
     *
     * @throws \Exception if the module cannot be unconfigured
     */
    public function localUnconfigure(LocalConfigurationHelpers $helpers);

    /**
     * called after the unconfiguration of all modules.
     */
    public function postUnconfigure(ConfigurationHelpers $helpers);
}
