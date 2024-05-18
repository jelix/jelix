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
 * Interface for components that need to do processing at the end of the
 * installation of an application.
 *
 * The component can compile and/or generate some files for example
 */
interface WarmUpLauncherInterface
{

    /**
     * Launch processing
     * @return mixed
     */
    public function launch();

    /**
     * Check if the given file is supported by the warmup component.
     *
     * Useful when a file is changed during development, and generated files
     * should be regenerated
     *
     * @param FilePlace $filename the full path of the file
     * @return boolean
     */
    public function doesItSupportFile(FilePlace $file);


    public function launchOnFile(FilePlace $file);

}