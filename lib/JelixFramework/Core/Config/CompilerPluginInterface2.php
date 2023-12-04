<?php
/**
 * @package      jelix
 * @subpackage   core
 *
 * @author       Laurent Jouanneau
 * @copyright    2026 Laurent Jouanneau
 *
 * @see          https://jelix.org
 * @licence      GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

namespace Jelix\Core\Config;

/**
 * interface for plugins of Jelix\Core\Config\Compiler.
 */
interface CompilerPluginInterface2 extends CompilerPluginInterface
{
    /**
     * Called for each activated modules. Jelix 1.9.
     *
     * @param object    $config     the configuration object that receive final informations
     */
    public function onModule2($config, \Jelix\Core\Infos\ModuleInfos $moduleInfos);

}
