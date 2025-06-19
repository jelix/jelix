<?php
/**
 * @author     Laurent Jouanneau
 * @copyright  2024 Laurent Jouanneau
 *
 * @see        https://www.jelix.org
 * @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

namespace Jelix\Routing\UrlMapping;

use Jelix\Core\AppInstance;
use Jelix\Core\Config\AppConfig;
use Jelix\Installer\WarmUp\FilePlace;
use Jelix\Installer\WarmUp\FilePlaceEnum;
use Jelix\Installer\WarmUp\WarmUpLauncherInterface;

/**
 * @internal
 */
class UrlMapWarmup implements WarmUpLauncherInterface
{
    /**
     * @var AppInstance
     */
    protected $app;

    public function __construct(AppInstance $app)
    {
        $this->app = $app;
    }

    public function getLaunchSteps()
    {
        return WarmUpLauncherInterface::STEP_POSTINSTALL;
    }

    public function launch(array $modulesList, int $step): void
    {

        $urlsFiles = [];
        $urlsLocalFiles = [];

        // an entrypoint may redefine the significantFile or localSignificantFile parameter
        foreach($this->app->getFrameworkInfo()->getEntryPoints() as $ep)
        {
            $config = AppConfig::loadStaticConfiguration($ep->getConfigFile());
            $mapperConfig = new MapperConfig($config->urlengine);

            if (isset($urlsFiles[$mapperConfig->mapFile])
                && isset($urlsLocalFiles[$mapperConfig->localMapFile])
            ) {
                continue;
            }

            $xmlfileSelector = new SelectorUrlXmlMap($mapperConfig->mapFile, $mapperConfig->localMapFile);
            $compiler = new XmlMapParser();
            if (!$compiler->compile($xmlfileSelector)) {
                throw new \Exception('The compiler for the url engine has failed');
            }
            $urlsFiles[$mapperConfig->mapFile] = true;
            $urlsLocalFiles[$mapperConfig->localMapFile] = true;
        }
    }

    public function doesItSupportFile(FilePlace $file) : bool
    {
        $mapperConfig = new MapperConfig($this->app->config->urlengine);

        if ($file->place == FilePlaceEnum::App && $file->filePath == 'system/'.$mapperConfig->mapFile) {
            return true;
        }
        else if ($file->place == FilePlaceEnum::Var && $file->filePath == 'config/'.$mapperConfig->localMapFile) {
            return true;
        }
        return false;
    }

    public function launchOnFile(FilePlace $file) : void
    {
        $this->launch([], 0);
    }
}