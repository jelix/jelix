<?php
/**
 * @author     Laurent Jouanneau
 * @copyright  2024 Laurent Jouanneau
 *
 * @see        https://www.jelix.org
 * @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

namespace Jelix\Installer\WarmUp;

/**
 * Indicate where a project file is stored, for compilers
 */
class FilePlace
{

    /**
     * @var string full path to the file
     */
    readonly public string $filePath;

    /**
     * @var string the path of the file, relative to the place directory (without the module directory if it is tied to a module)
     */
    readonly public string $subPath;

    /**
     * @var FilePlaceEnum  the place into the project, where the file is stored
     */
    readonly public FilePlaceEnum $place;

    /**
     * @var string the module name when the file is tied to a module
     */
    readonly public string $module;

    /**
     * @param string $filePath full path to the file
     * @param FilePlaceEnum $place indicate the place into the project, where the file is stored
     * @param string $module the module name when the file is tied to a module
     * @param string $subPath the path of the file, relative to the place directory (without the module directory if it is tied to a module)
     */
    public function __construct($filePath, FilePlaceEnum $place, $module = '', $subPath = '')
    {
        $this->filePath = $filePath;
        $this->place = $place;
        $this->module = $module;
        $this->subPath = $subPath;
    }
}