<?php
/**
 * @author     Laurent Jouanneau
 * @copyright  2024 Laurent Jouanneau
 *
 * @see        https://www.jelix.org
 * @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

namespace Jelix\Forms\Compiler;

use Jelix\Core\AppInstance;
use Jelix\Installer\WarmUp\FilePlace;
use Jelix\Installer\WarmUp\FilePlaceEnum;
use Jelix\Installer\WarmUp\WarmUpLauncherInterface;

/**
 * @internal
 */
class FormsWarmup implements WarmUpLauncherInterface
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
        $compiler = new \Jelix\Forms\Compiler\FormCompiler(
            $this->app->appPath,
            $this->app->varPath,
            $this->app->buildPath,
        );

        foreach($modulesList as $name => $path) {
            $compiler->compileModule($name, $path);
        }
    }

    public function doesItSupportFile(FilePlace $file) : bool
    {
        if ($file->place != FilePlaceEnum::Module) {
            return false;
        }
        return str_ends_with($file->filePath, '.form.xml');
    }

    public function launchOnFile(FilePlace $file) : void
    {
        $compiler = new \Jelix\Forms\Compiler\FormCompiler(
            $this->app->appPath,
            $this->app->varPath,
            $this->app->buildPath,
        );
        $compiler->compileFile($file->module, $file->filePath, str_replace('forms/', '', $file->subPath));
    }
}