<?php
/**
 * @author     Laurent Jouanneau
 * @copyright  2026 Laurent Jouanneau
 *
 * @see        https://www.jelix.org
 * @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

namespace Jelix\DaoUtils;

use Jelix\Core\App;
use Jelix\Core\AppInstance;
use Jelix\Installer\WarmUp\FilePlace;
use Jelix\Installer\WarmUp\FilePlaceEnum;
use Jelix\Installer\WarmUp\WarmUpLauncherInterface;
use Jelix\Profiles\ProfilesReader;
use Jelix\Profiles\ReaderPlugin;
use Jelix\Services\Database\DbProfilePlugin;

/**
 * Launch compilation of all Dao of the application
 *
 * @internal
 */
class DaoWarmup implements WarmUpLauncherInterface
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
        return WarmUpLauncherInterface::STEP_PREINSTALL | WarmUpLauncherInterface::STEP_POSTINSTALL;
    }

    public function launch(array $modulesList, int $step): void
    {
        $compiler = new DaoWarmupCompiler(
            $this->app->appPath,
            $this->app->varPath,
            $this->app->varLibPath,
        );
        $sqlTypes = $this->getSqlTypes();
        foreach ($modulesList as $name => $path) {
            $compiler->compileModule($name, $path, $sqlTypes);
        }
    }

    public function doesItSupportFile(FilePlace $file): bool
    {
        if ($file->place != FilePlaceEnum::Module
            && $file->place != FilePlaceEnum::AppOverloads
            && $file->place != FilePlaceEnum::VarOverloads) {
            return false;
        }
        return str_ends_with($file->filePath, '.dao.xml');
    }

    public function launchOnFile(FilePlace $file): void
    {
        $compiler = new DaoWarmupCompiler(
            $this->app->appPath,
            $this->app->varPath,
            $this->app->varLibPath,
        );
        $sqlTypes = $this->getSqlTypes();
        $compiler->compileSingleFile($file, $sqlTypes);
    }

    protected function getSqlTypes()
    {
        $file = $this->app->configPath.'profiles.ini.php';
        if (!file_exists($file)) {
            return [];
        }

        $compiler = new ProfilesReader(function($name) {

            if ($name == 'jdb') {
                return new DbProfilePlugin('jdb');
            }
            $plugin = App::loadPlugin($name, 'profiles', '.profiles.php', $name.'ProfilesCompiler', $name);
            if (!$plugin) {
                $plugin = new ReaderPlugin($name);
            }
            return $plugin;
        });

        $profiles = $compiler->readFromFile($file);
        $dbProfiles = $profiles->getProfilesOfCategory('jdb');

        $dbTypes = array_unique(array_map(function($profile) {
            return $profile['dbtype'];
        }, $dbProfiles));
        return $dbTypes;
    }
}