<?php
/**
 * @author      Laurent Jouanneau
 * @copyright   2018-2022 Laurent Jouanneau
 *
 * @see        http://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

namespace Jelix\Installer\Module\API;

use Jelix\Installer\Module\InteractiveConfigurator;

/**
 * @since 1.7
 */
class ConfigurationHelpers extends PreConfigurationHelpers
{
    use FileHelpersTrait;

    /**
     * @var InteractiveConfigurator
     */
    protected $interactiveConfigurator;

    public function __construct(\Jelix\Installer\GlobalSetup $setup, InteractiveConfigurator $cli)
    {
        parent::__construct($setup);
        $this->interactiveConfigurator = $cli;
    }

    /**
     * @return InteractiveConfigurator
     */
    public function cli()
    {
        return $this->interactiveConfigurator;
    }

    /**
     * Main entrypoint of the application (in most of case, index.php).
     *
     * @return \Jelix\Installer\EntryPointConfigurator
     */
    public function getMainEntryPoint()
    {
        $ep = $this->globalSetup->getMainEntryPoint();
        $flc = $this->globalSetup->forLocalConfiguration();

        return new \Jelix\Installer\EntryPointConfigurator($ep, $this->globalSetup, $flc);
    }

    /**
     * List of entry points of the application.
     *
     * @return \Jelix\Installer\EntryPointConfigurator[]
     */
    public function getEntryPointsList()
    {
        $globalSetup = $this->globalSetup;
        $list = $globalSetup->getEntryPointsList();
        $flc = $globalSetup->forLocalConfiguration();

        return array_map(function ($ep) use ($globalSetup, $flc) {
            return new \Jelix\Installer\EntryPointConfigurator($ep, $globalSetup, $flc);
        }, $list);
    }

    /**
     * @param string $type
     *
     * @return \Jelix\Installer\EntryPointConfigurator[]
     */
    public function getEntryPointsByType($type = 'classic')
    {
        $globalSetup = $this->globalSetup;
        $list = $globalSetup->getEntryPointsByType($type);
        $flc = $globalSetup->forLocalConfiguration();

        return array_map(function ($ep) use ($globalSetup, $flc) {
            return new \Jelix\Installer\EntryPointConfigurator($ep, $globalSetup, $flc);
        }, $list);
    }

    /**
     * @param $epId
     *
     * @return \Jelix\Installer\EntryPointConfigurator
     */
    public function getEntryPointsById($epId)
    {
        $ep = $this->globalSetup->getEntryPointById($epId);
        if ($ep) {
            $ep = new \Jelix\Installer\EntryPointConfigurator($ep, $this->globalSetup, $this->globalSetup->forLocalConfiguration());
        }

        return $ep;
    }

    /**
     * declare web assets into the main configuration.
     *
     * @param string $name       the name of webassets
     * @param array  $values     should be an array with one or more of these keys 'css' (array), 'js'  (array), 'require' (string)
     * @param string $collection the name of the webassets collection
     * @param bool   $force
     */
    public function declareGlobalWebAssets($name, array $values, $collection, $force)
    {
        $config = $this->getSingleConfigIni();
        $this->globalSetup->declareWebAssetsInConfig($config, $name, $values, $collection, $force);
    }

    /**
     * remove web assets from the main configuration.
     *
     * @param string $name       the name of webassets
     * @param string $collection the name of the webassets collection
     */
    public function removeGlobalWebAssets($name, $collection)
    {
        $config = $this->getSingleConfigIni();
        $this->globalSetup->removeWebAssetsFromConfig($config, $name, $collection);
    }

    /**
     * Create a new entry point.
     *
     * @param string $entryPointModelFile the entrypoint file to copy
     * @param string $entryPointWebPath   the path of the entrypoint to create into the www directory
     * @param string $configFileName      name of the configuration file. path relative to app/system or var/config
     * @param string $epType              type of the entry point (classic)
     * @param string $configFileModel     a configuration file to copy as $configFileName. default is an empty content.
     *
     * @throws \Exception
     */
    public function createEntryPoint(
        $entryPointModelFile,
        $entryPointWebPath,
        $configFileName,
        $epType = 'classic',
        $configFileModel = ''
    ) {
        // create the entrypoint file

        if (substr($entryPointWebPath, -4) == '.php') {
            $epFile = $entryPointWebPath;
            $epId = substr($entryPointWebPath, 0, -4);
        } else {
            $epFile = $entryPointWebPath.'.php';
            $epId = $entryPointWebPath;
        }

        if ($epType == 'cmdline') {
            if (!file_exists(\jApp::scriptsPath($epFile))) {
                $this->copyFile($entryPointModelFile, \jApp::scriptsPath($epFile));
            }
        } else {
            $newEpPath = \jApp::wwwPath($epFile);
            if (!file_exists($newEpPath)) {
                $this->copyFile($entryPointModelFile, $newEpPath);

                // change the path to application.init.php into the entrypoint
                // depending of the application, the path of www/ is not always at the same place, relatively to
                // application.init.php
                $appInitFile = \jApp::applicationInitFile();
                $relativePath = \Jelix\FileUtilities\Path::shortestPath(\jApp::wwwPath(), dirname($appInitFile).'/');

                $epCode = file_get_contents($newEpPath);
                $epCode = preg_replace('#(require\s*\(?\s*[\'"])(.*)(application\.init\.php)([\'"])#m', '\\1'.$relativePath.'/'.basename($appInitFile).'\\4', $epCode);
                file_put_contents($newEpPath, $epCode);
            }
        }

        // create the configuration file
        $configFilePath = $this->configFilePath($configFileName);
        if (!file_exists($configFilePath)) {
            if ($configFileModel) {
                $this->copyFile($configFileModel, $configFilePath);
            } else {
                file_put_contents($configFilePath, ';<'.'?php die(\'\');?'.'>');
            }
        }

        // declare the entry point
        $this->globalSetup->declareNewEntryPoint($epId, $epType, $configFileName);
    }

    /**
     * Remove an entry point that was installed by a module
     *
     * @param string $entryPointWebPath   the path of the entrypoint to create into the www directory
     * @param string $configFileName      name of the configuration file. path relative to app/system or var/config
     * @param string $epType              type of the entry point (classic)
     *
     * @throws \Exception
     * @since 1.7.11
     */
    public function removeEntryPoint(
        $entryPointWebPath,
        $configFileName,
        $epType = 'classic'
    ) {

        if (substr($entryPointWebPath, -4) == '.php') {
            $epFile = $entryPointWebPath;
            $epId = substr($entryPointWebPath, 0, -4);
        } else {
            $epFile = $entryPointWebPath.'.php';
            $epId = $entryPointWebPath;
        }

        if ($epType == 'cmdline') {
            if (file_exists(\jApp::scriptsPath($epFile))) {
                unlink(\jApp::scriptsPath($epFile));
            }
        } else {
            $newEpPath = \jApp::wwwPath($epFile);
            if (file_exists($newEpPath)) {
                unlink($newEpPath);
            }
        }

        // remove the configuration file
        $configFilePath = $this->configFilePath($configFileName);
        if (file_exists($configFilePath)) {
            unlink($configFilePath);
        }

        // undeclare the entry point
        $this->globalSetup->undeclareEntryPoint($epId);
    }
}
