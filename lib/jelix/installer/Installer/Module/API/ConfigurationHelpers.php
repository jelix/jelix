<?php
/**
 * @author      Laurent Jouanneau
 * @copyright   2018 Laurent Jouanneau
 * @link        http://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */
namespace Jelix\Installer\Module\API;

use Jelix\Installer\Module\InteractiveConfigurator;
/**
 *
 * @since 1.7
 */
class ConfigurationHelpers extends PreConfigurationHelpers {

    use FileHelpersTrait;

    /**
     * @var InteractiveConfigurator
     */
    protected $interactiveConfigurator;

    function __construct(\Jelix\Installer\GlobalSetup $setup, InteractiveConfigurator $cli) {
        parent::__construct($setup);
        $this->interactiveConfigurator = $cli;
    }

    /**
     * @return InteractiveConfigurator
     */
    public function cli() {
        return $this->interactiveConfigurator;
    }

    /**
     * Main entrypoint of the application (in most of case, index.php)
     * @return \Jelix\Installer\EntryPointConfigurator
     */
    public function getMainEntryPoint() {
        $ep = $this->globalSetup->getMainEntryPoint();
        $flc = $this->globalSetup->forLocalConfiguration();
        return new \Jelix\Installer\EntryPointConfigurator($ep, $this->globalSetup, $flc);
    }

    /**
     * List of entry points of the application
     *
     * @return \Jelix\Installer\EntryPointConfigurator[]
     */
    public function getEntryPointsList() {
        $list = $this->globalSetup->getEntryPointsList();
        $globalSetup = $this->globalSetup;
        $flc = $this->globalSetup->forLocalConfiguration();
        return array_map(function($ep) use($globalSetup, $flc) {
            return new \Jelix\Installer\EntryPointConfigurator($ep, $globalSetup, $flc);
        }, $list);
    }

    /**
     * @param string $type
     * @return \Jelix\Installer\EntryPointConfigurator[]
     */
    public function getEntryPointsByType($type='classic') {
        $list = $this->globalSetup->getEntryPointsByType($type);
        $globalSetup = $this->globalSetup;
        $flc = $this->globalSetup->forLocalConfiguration();
        return array_map(function($ep) use($globalSetup, $flc) {
            return new \Jelix\Installer\EntryPointConfigurator($ep, $globalSetup, $flc);
        }, $list);
    }

    /**
     * @param $epId
     * @return \Jelix\Installer\EntryPointConfigurator
     */
    public function getEntryPointsById($epId) {
        $ep = $this->globalSetup->getEntryPointById($epId);
        if ($ep) {
            $ep = new \Jelix\Installer\EntryPointConfigurator($ep, $this->globalSetup, $this->forLocalConfiguration);
        }
        return $ep;
    }

    /**
     * declare web assets into the main configuration
     * @param string $name the name of webassets
     * @param array $values should be an array with one or more of these keys 'css' (array), 'js'  (array), 'require' (string)
     * @param string $collection the name of the webassets collection
     * @param bool $force
     */
    public function declareGlobalWebAssets($name, array $values, $collection, $force)
    {
        $config = $this->getSingleConfigIni();
        $this->globalSetup->declareWebAssetsInConfig($config, $name, $values, $collection, $force);
    }

    /**
     * remove web assets from the main configuration
     *
     * @param string $name the name of webassets
     * @param string $collection the name of the webassets collection
     */
    public function removeGlobalWebAssets($name, $collection)
    {
        $config = $this->getSingleConfigIni();
        $this->globalSetup->removeWebAssetsFromConfig($config, $name, $collection);
    }

}