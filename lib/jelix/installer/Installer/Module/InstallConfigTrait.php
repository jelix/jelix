<?php
/**
 * @author      Laurent Jouanneau
 * @copyright   2018 Laurent Jouanneau
 * @link        http://jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */
namespace Jelix\Installer\Module;

/**
 * Trait for installer/configurator classes
 *
 * @since 1.7
 */
trait InstallConfigTrait
{
    /**
     * @var string name of the component
     */
    private $componentName;

    /**
     * @var string name of the installer
     */
    private $name;


    /**
     * The path of the module
     * @var string
     */
    private $path;

    /**
     * parameters for the installer, indicated in the configuration file or
     * dynamically, by a launcher in a command line for instance.
     * @var array
     */
    protected $parameters = array();

    final function getName() {
        return $this->name;
    }

    final function getPath() {
        return $this->path;
    }

    /**
     * @inheritdoc
     */
    public final function setParameters($parameters) {
        $this->parameters = $parameters;
    }


    final function getParameter($name) {
        if (isset($this->parameters[$name]))
            return $this->parameters[$name];
        else
            return null;
    }

    /**
     * @inheritdoc
     */
    public final function getParameters() {
        return $this->parameters;
    }



}