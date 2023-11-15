<?php
/**
 * @author     Laurent Jouanneau
 * @copyright  2023 Laurent Jouanneau
 *
 * @see        https://www.jelix.org
 * @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

namespace Jelix\Locale;

use Jelix\Installer\GlobalSetup;
use Jelix\Installer\WarmUp\WarmUpLauncherInterface;

/**
 *
 */
class LocaleWarmUp implements WarmUpLauncherInterface
{
    protected $buildDirectory;
    protected $globalSetup;

    public function __construct(GlobalSetup $globalSetup, $buildDirectory)
    {
        $this->buildDirectory = $buildDirectory;
        $this->globalSetup = $globalSetup;
    }

    public function launch()
    {
        $modules = $this->globalSetup->getMainEntryPoint()->getConfigObj()->_modulesPathList;

        $compiler = new LocaleCompiler($this->buildDirectory);
        foreach($modules as $name => $path) {
            $compiler->compile($name, $path);
        }
    }
}