<?php
/**
 * @author     Laurent Jouanneau
 * @copyright  2023-2024 Laurent Jouanneau
 *
 * @see        https://www.jelix.org
 * @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

namespace Jelix\Locale;

use Jelix\Core\AppInstance;
use Jelix\Installer\WarmUp\FilePlace;
use Jelix\Installer\WarmUp\WarmUpLauncherInterface;

/**
 * @internal
 */
class LocaleWarmUp implements WarmUpLauncherInterface
{
    /**
     * @var AppInstance
     */
    protected $app;

    public function __construct(AppInstance $app)
    {
        $this->app = $app;
    }

    public function launch()
    {
        $modules = $this->app->config->_modulesPathList;

        $compiler = new LocaleCompiler($this->app->appPath, $this->app->varPath, $this->app->buildPath);
        foreach($modules as $name => $path) {
            $compiler->compileModule($name, $path);
        }
    }

    public function doesItSupportFile(FilePlace $file)
    {
        return str_ends_with($file->filePath, '.properties');
    }

    public function launchOnFile(FilePlace $file)
    {
        $compiler = new LocaleCompiler($this->app->appPath, $this->app->varPath, $this->app->buildPath);
        $compiler->compileSingleFile($file);
    }
}