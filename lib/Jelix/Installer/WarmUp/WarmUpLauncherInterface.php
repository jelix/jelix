<?php
/**
 * @author      Laurent Jouanneau
 * @copyright   2023-2024 Laurent Jouanneau
 *
 * @see        http://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

namespace Jelix\Installer\WarmUp;

/**
 * Interface for components that need to do processing during the
 * installation of an application.
 *
 * These kind of components can compile and/or generate some files for example
 */
interface WarmUpLauncherInterface
{
    /**
     * @var int the launch() method is called after all pre-installation
     */
    const STEP_PREINSTALL = 1;

    /**
     * @var int the launch() method is called after each module installation
     */
    const STEP_MODULE_INSTALL = 2;

    /**
     * @var int the launch() method is called after each module installation
     */
    const STEP_MODULE_UNINSTALL = 4;

    /**
     * @var int the launch() method is called after all module installations
     */
    const STEP_INSTALL = 8;

    /**
     * @var int the launch() method is called after all post-installation
     */
    const STEP_POSTINSTALL = 16;

    const STEP_ALL = 1 | 2 | 4 | 8 | 16;

    /**
     * @return int a combination of STEP_* const
     */
    public function getLaunchSteps();

    /**
     * Launch processing.
     *
     * It may be called during one or more steps of the installation, according
     * to what it is returned by getLaunchStepsList().
     *
     * @param $modulesList array list of modules. Key=module name, value=module path
     * @param $step int one of STEP_* const.
     * @return void
     */
    public function launch(array $modulesList, int $step) : void;

    /**
     * Check if the given file is supported by the warmup component.
     *
     * Useful when a file is changed during development, and when generated files
     * should be regenerated
     *
     * @param FilePlace $filename the full path of the file
     * @return boolean true if the given fill is supported by the component
     */
    public function doesItSupportFile(FilePlace $file) : bool;

    /**
     * Launch processing on a single file.
     *
     * Called by file watcher, after calling doesItSupportFile().
     *
     * @param FilePlace $file
     */
    public function launchOnFile(FilePlace $file): void;

}