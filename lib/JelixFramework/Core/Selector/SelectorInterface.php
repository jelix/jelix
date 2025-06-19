<?php
/**
 * a selector is a string refering to a file or a ressource, by indicating its module and its name.
 * For example : "moduleName~resourceName". There are several type of selector, depending on the
 * resource type. Selector objects get the real path of the corresponding file, the name of the
 * compiler (if the file has to be compile) etc.
 * So here, there is a selector class for each selector type.
 *
 * @author      Laurent Jouanneau
 * @contributor Christophe Thiriot
 *
 * @copyright   2005-2014 Laurent Jouanneau, 2008 Christophe Thiriot
 *
 * @see        http://www.jelix.org
 * @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

namespace Jelix\Core\Selector;

/**
 * interface of selector classes.
 */
interface SelectorInterface
{
    /**
     * @return string file path corresponding to the resource pointing by the selector
     */
    public function getPath();

    /**
     * @return string file path of the compiled file (if the main file should be compiled by jelix)
     */
    public function getCompiledFilePath();

    /**
     * @return null|object the compiler used to compile file
     */
    public function getCompiler();

    /**
     * @return bool true if the compiler compile many file at one time
     */
    public function useMultiSourceCompiler();

    /**
     * @param bool $full true if you want a full selector ("type:...")
     *
     * @return string the selector
     */
    public function toString($full = false);
}
