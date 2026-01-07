<?php
/**
 * @author     Laurent Jouanneau
 * @copyright  2025-2026 Laurent Jouanneau
 *
 * @see        https://www.jelix.org
 * @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

namespace Jelix\Template;

use jAppInstance as AppInstance;
use Jelix\Installer\WarmUp\FilePlace;
use Jelix\Installer\WarmUp\FilePlaceEnum;
use Jelix\Installer\WarmUp\WarmUpLauncherInterface;

/**
 * Launch compilation of all templates of the application
 *
 * @internal
 */
class TemplateWarmup implements WarmUpLauncherInterface
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
        $compiler = new TemplateWarmupCompiler($this->app);
        foreach ($modulesList as $name => $path) {
            $compiler->compileModule($name, $path);
        }
    }

    public function doesItSupportFile(FilePlace $file) : bool
    {
        if ($file->place != FilePlaceEnum::Module
            && $file->place != FilePlaceEnum::App
            && $file->place != FilePlaceEnum::Var) {
            return false;
        }
        return str_ends_with($file->filePath, '.ctpl');
    }

    public function launchOnFile(FilePlace $file) : void
    {
        $compiler = new TemplateWarmupCompiler($this->app);
        $compiler->compileSingleFile($file);
    }
}