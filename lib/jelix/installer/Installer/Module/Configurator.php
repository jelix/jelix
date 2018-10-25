<?php
/**
* @author      Laurent Jouanneau
* @copyright   2018 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/
namespace Jelix\Installer\Module;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Helper\QuestionHelper;

/**
 * Base class for classes which configure a module
 * @since 1.7
 */
class Configurator implements ConfiguratorInterface {

    use InstallConfigTrait;
    use HelpersTrait;

    /**
     * @var string the version for which the installer is called
     */
    private $version = '0';


    private $forLocalConfiguration = false;

    /**
     * @param string $componentName name of the component
     * @param string $name name of the installer
     * @param string $path the component path
     * @param string $version version of the component
     */
    function __construct ($componentName, $name, $path, $version, $forLocalConfiguration = false) {
        $this->path = $path;
        $this->version = $version;
        $this->name = $name;
        $this->componentName = $componentName;
        $this->forLocalConfiguration = $forLocalConfiguration;
    }


    // ----- ConfiguratorInterface implementation

    /**
     * @inheritdoc
     */
    public function getDefaultParameters() {
        return array();
    }

    /**
     * @inheritdoc
     */
    public function askParameters(InteractiveConfigurator $cli) {

    }


    /**
     * @inheritdoc
     */
    public function preConfigure() {

    }

    /**
     * @inheritdoc
     */
    public function configure() {

    }

    /**
     * @inheritdoc
     */
    public function postConfigure() {

    }


    /**
     * @inheritdoc
     */
    public function preUnconfigure() {

    }

    /**
     * @inheritdoc
     */
    public function unconfigure() {

    }

    /**
     * @inheritdoc
     */
    public function postUnconfigure() {

    }

    // ----- other methods

    final function getVersion() {
        return $this->version;
    }

    /**
     * @return bool true if the configuration is local, false if it is for the
     * application
     */
    final function getConfigurationMode() {
        return $this->forLocalConfiguration;
    }


    /**
     * default config, main config combined with or without local config
     * @return \Jelix\IniFile\IniModifierArray
     */
    protected final function getConfigIni() {
        if ($this->forLocalConfiguration) {
            $ini = $this->globalSetup->getAppConfigIni(true);
            $ini['local'] = $this->globalSetup->getLocalConfigIni();
            return $ini;
        }
        return $this->globalSetup->getAppConfigIni();
    }

    /**
     * main config or local config ini file alone
     * @return \Jelix\IniFile\IniModifier
     */
    protected final function getSingleConfigIni() {
        if ($this->forLocalConfiguration) {
            return $this->globalSetup->getLocalConfigIni();
        }
        return $this->globalSetup->getMainConfigIni();
    }

    /**
     * List of entry points of the application
     *
     * @return \Jelix\Installer\EntryPointConfigurator[]
     */
    protected final function getEntryPointsList() {
        $list = $this->globalSetup->getEntryPointsList();
        $globalSetup = $this->globalSetup;
        $flc = $this->forLocalConfiguration;
        return array_map(function($ep) use($globalSetup, $flc) {
            return new \Jelix\Installer\EntryPointConfigurator($ep, $globalSetup, $flc);
        }, $list);
    }

    /**
     * @param string $type
     * @return \Jelix\Installer\EntryPointConfigurator[]
     */
    protected final function getEntryPointsByType($type='classic') {
        $list = $this->globalSetup->getEntryPointsByType($type);
        $globalSetup = $this->globalSetup;
        $flc = $this->forLocalConfiguration;
        return array_map(function($ep) use($globalSetup, $flc) {
            return new \Jelix\Installer\EntryPointConfigurator($ep, $globalSetup, $flc);
        }, $list);
    }

    /**
     * @param $epId
     * @return \Jelix\Installer\EntryPointConfigurator
     */
    protected final function getEntryPointsById($epId) {
        $ep = $this->globalSetup->getEntryPointById($epId);
        if ($ep) {
            $ep = new \Jelix\Installer\EntryPointConfigurator($ep, $this->globalSetup, $this->forLocalConfiguration);
        }
        return $ep;
    }

}
