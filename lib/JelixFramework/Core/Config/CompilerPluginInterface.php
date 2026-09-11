<?php
/**
 * @author       Laurent Jouanneau
 * @copyright    2012-2026 Laurent Jouanneau
 *
 * @see          https://jelix.org
 * @licence      GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

namespace Jelix\Core\Config;

/**
 * interface for plugins for \Jelix\Core\Config\Compiler.
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
     * @param object                        $config the configuration object
     * @param \Jelix\Core\Infos\ModuleInfos $module the module data
     */
    public function onModule($config, \Jelix\Core\Infos\ModuleInfos $module);

    /**
     * called after processing module information.
     *
     * @param object $config the configuration object
     */
    public function atEnd($config);
}
