<?php
/**
 * @author     Laurent Jouanneau
 * @copyright  2024 Laurent Jouanneau
 *
 * @see        https://www.jelix.org
 * @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

namespace Jelix\Event;

use Jelix\Core\AppInstance;
use Jelix\Installer\WarmUp\FilePlace;
use Jelix\Installer\WarmUp\FilePlaceEnum;
use Jelix\Installer\WarmUp\WarmUpLauncherInterface;

/**
 * @internal
 */
class EventWarmup implements WarmUpLauncherInterface
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
        return WarmUpLauncherInterface::STEP_PREINSTALL;
    }

    public function launch(array $modulesList, int $step): void
    {
        $compiler = new \Jelix\Event\Compiler();

        foreach($modulesList as $name => $path) {
            $compiler->compileListenersFile($path.'/events.xml', $name);
        }
        $compiler->save($this->app->buildPath.'listeners.php');
    }

    public function doesItSupportFile(FilePlace $file) : bool
    {
        if ($file->place != FilePlaceEnum::Module) {
            return false;
        }
        return $file->filePath == 'events.xml';
    }

    public function launchOnFile(FilePlace $file) : void
    {
        $this->launch($this->app->getEnabledModulesPaths(), 0);
    }
}