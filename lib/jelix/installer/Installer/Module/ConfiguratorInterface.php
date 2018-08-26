<?php
/**
 * @author      Laurent Jouanneau
 * @copyright   2018 Laurent Jouanneau
 * @link        http://jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */
namespace Jelix\Installer\Module;

/**
 * Interface for classes which configure a module
 * @since 1.7
 */
interface ConfiguratorInterface {

    /**
     * List of possible installation parameters with their default values
     *
     * @return array
     */
    public function getDefaultParameters();

    /**
     * indicates installation parameters to use.
     *
     * can be called whether the interactive mode is enabled or not
     * (but before askParameters())
     *
     * @param array $parameters
     */
    public function setParameters($parameters);

    /**
     * It should asks installation parameters to the user on the console.
     *
     * It is called when the interactive mode is enabled. It should fill
     * itself its installation parameters.
     * The implementation should provides methods or components to
     * ask informations on the console.
     *
     * @throws \Exception  if an error occurs during the installation.
     */
    public function askParameters();

    /**
     * return list of installation parameters
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
    public function preConfigure();


    /**
     * Configure the module
     *
     * You can set some configuration parameters in the application configuration
     * files, you can also copy some files into the application, setup the
     * urls mapping etc..
     *
     * @throws \Exception if the module cannot be configured
     */
    public function configure();

    /**
     * called after the configuration of all modules.
     */
    public function postConfigure();

    /**
     * called before unconfiguration of any modules.
     *
     * This is the opportunity to check some things. Throw an exception to
     * interrupt the configuration process.
     *
     * @throws \Exception if the module cannot be unconfigured
     */
    public function preUnconfigure();


    /**
     * Unconfigure the module
     *
     * You can remove some configuration parameters from the application
     * parameters that are not needed for the uninstaller. You can
     * also delete some files you installed into the configure() method,
     * remove the url mapping etc..
     *
     * @throws \Exception if the module cannot be unconfigured
     */
    public function unconfigure();

    /**
     * called after the unconfiguration of all modules.
     */
    public function postUnconfigure();


}

