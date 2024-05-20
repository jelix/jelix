<?php
/**
 * @author      Laurent Jouanneau
 * @copyright   2023-2024 Laurent Jouanneau
 *
 * @see         https://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

namespace Jelix\Installer\WarmUp;


use Jelix\Core\AppInstance;
use Jelix\FileUtilities\Directory;
use Jelix\Locale\LocaleWarmUp;

/**
 * @internal
 */
class WarmUp
{

    /**
     * @var WarmUpLauncherInterface[]
     */
    protected $warmUpLaunchers = array();

    /**
     * @var AppInstance
     */
    protected $app;

    /**
     * @param AppInstance $app
     */
    public function __construct(AppInstance $app)
    {
        $this->app = $app;
        $this->warmUpLaunchers[] = new LocaleWarmUp($app);

        $buildPath = $app->buildPath;
        if (!file_exists($buildPath)) {
            Directory::create($buildPath);
        }
    }

    /**
     * Launch warmup component that are belongs to the corresponding step.
     *
     * @param array $modulesList list of modules to process.
     * @param int $step one of WarmUpLauncherInterface::STEP_ const
     * @return void
     */
    public function launch(array $modulesList, int $step)
    {
        foreach($this->warmUpLaunchers as $warmUpLauncher) {
            if ($warmUpLauncher->getLaunchSteps() & $step) {
                $warmUpLauncher->launch($modulesList, $step);
            }
        }
    }

    /**
     * @param string $file full path of the file to process
     * @return bool
     */
    public function launchForFile($file)
    {
        $filePlace = $this->getFilePlace($file);
        if (!$filePlace) {
            echo "place not found\n";
            return false;
        }

        $launcher = null;
        foreach($this->warmUpLaunchers as $warmUpLauncher){
            if ($warmUpLauncher->doesItSupportFile($filePlace)) {
                $launcher = $warmUpLauncher;
                break;
            }
            echo get_class($warmUpLauncher)." dont support file\n";
        }

        if (!$launcher) {
            echo "launcher not found\n";
            return false;
        }

        $launcher->launchOnFile($filePlace);
        return true;
    }

    protected function getFilePlace($file)
    {
        $varPath = $this->app->varPath;
        if (str_starts_with($file, $varPath.'overloads/')) {
            if (preg_match('!^'.$varPath.'overloads/([^/]+)/(.+)!', $file, $m)) {
                return new FilePlace($file, FilePlaceEnum::VarOverloads, $m[1], $m[2]);
            }
            return null;
        }

        if (str_starts_with($file, $varPath)) {
            return new FilePlace($file, FilePlaceEnum::Var, '', ltrim(str_replace($varPath, '', $file), '/'));
        }

        $appPath = $this->app->appPath;
        if (str_starts_with($file, $appPath.'app/overloads/')) {
            if (preg_match('!^'.$appPath.'app/overloads/([^/]+)/(.+)!', $file, $m)) {
                return new FilePlace($file, FilePlaceEnum::AppOverloads, $m[1], $m[2]);
            }
            return null;
        }

        if (str_starts_with($file, $appPath.'app/')) {
            return new FilePlace($file, FilePlaceEnum::App, '', str_replace($appPath.'app/', '', $file));
        }

        foreach($this->app->getEnabledModulesPaths() as $module => $path) {
            if (str_starts_with($file, $path)) {
                return new FilePlace($file, FilePlaceEnum::Module, $module, ltrim(str_replace($path, '', $file), '/'));
            }
        }
        return null;
    }
}