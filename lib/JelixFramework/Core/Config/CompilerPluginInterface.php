<?php
/**
 * @package      jelix
 * @subpackage   core
 *
 * @author       Laurent Jouanneau
 * @copyright    2012-2026 Laurent Jouanneau
 *
 * @see          https://jelix.org
 * @licence      GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

namespace Jelix\Core\Config;

/**
 * interface for plugins of Jelix\Core\Config\Compiler.
 */
interface CompilerPluginInterface
{
    /**
     * Return the priority of the plugin.
     *
     * A lower number is a higher priority. Numbers lower than 50 are reserved.
     *
     * @return int the level of priority
     */
    public function getPriority();

    /**
     * called before processing module information.
     *
     * @param object $config the configuration object
     */
    public function atStart($config);

    /**
     * called for each activated modules.
     *
     * @param object    $config     the configuration object
     * @param string    $moduleName the module name
     * @param string    $modulePath the path to the module directory
     * @param \SimpleXMLElement $moduleXml  the xml object representing the content of module.xml of the module
     *
     * @deprecated implement CompilerPluginInterface2::onmodule2() instead
     */
    public function onModule($config, $moduleName, $modulePath, $moduleXml);

    /**
     * called after processing module information.
     *
     * @param object $config the configuration object
     */
    public function atEnd($config);
}
