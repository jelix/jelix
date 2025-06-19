<?php
/**
 * @author      Laurent Jouanneau
 * @copyright   2018-2023 Laurent Jouanneau
 *
 * @see        http://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

namespace Jelix\Installer\Module\API;

use Jelix\Core\App;

/**
 * @since 1.7
 */
class InstallHelpers extends PreInstallHelpers
{
    use FileHelpersTrait;


    /**
     * default config, main config combined with local config (RW) and live config (RW).
     *
     * @return \Jelix\IniFile\IniModifierArray
     */
    public function getConfigIni()
    {
        $ini = $this->globalSetup->getSystemConfigIni(true);
        $ini['local'] = $this->globalSetup->getLocalConfigIni();
        $ini['live'] = $this->globalSetup->getLiveConfigIni();

        return $ini;
    }

    /**
     * the liveconfig.ini.php file.
     *
     * @return \Jelix\IniFile\IniModifierInterface
     *
     * @since 1.7
     */
    public function getLiveConfigIni()
    {
        return $this->globalSetup->getLiveConfigIni();
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
        $config = $this->getLocalConfigIni();
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
        $config = $this->getLocalConfigIni();
        $this->globalSetup->removeWebAssetsFromConfig($config, $name, $collection);
    }

    public function updateEntryPointFile($entryPointModelFile, $entryPointWebPath, $epType = 'classic')
    {
        if (substr($entryPointWebPath, -4) == '.php') {
            $epFile = $entryPointWebPath;
        } else {
            $epFile = $entryPointWebPath.'.php';
        }

        $epPath = App::wwwPath($epFile);
        if (!file_exists($epPath)) {
            throw new \Exception('The entrypoint '.$entryPointModelFile. ' cannot be updated, as it doesn\'t exist');
        }
        $this->updateOrCreateEntryPointFile($entryPointModelFile, $epPath);
    }

    protected function updateOrCreateEntryPointFile($entryPointFile, $epPath)
    {
        // copy the entrypoint and its configuration
        $this->copyFile($entryPointFile, $epPath, true);

        // change the path to application.init.php into the entrypoint
        // depending on the application, the path of www/ is not always at the same place, relatively to
        // application.init.php
        $appInitFile = App::applicationInitFile();
        $relativePath = \Jelix\FileUtilities\Path::shortestPath(App::wwwPath(), dirname($appInitFile).'/');

        $epCode = file_get_contents($epPath);
        $epCode = preg_replace('#(require\s*\(?\s*[\'"])(.*)(application\.init\.php)([\'"])#m', '\\1'.$relativePath.'/'.basename($appInitFile).'\\4', $epCode);
        file_put_contents($epPath, $epCode);
    }

    public function removeEntryPoint($entryPointName)
    {
        $this->globalSetup->undeclareEntryPoint($entryPointName);
    }

}
