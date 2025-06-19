<?php
/**
 * @author      Laurent Jouanneau
 * @copyright   2018 Laurent Jouanneau
 *
 * @see        http://jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

namespace Jelix\Installer;

/**
 * entry points properties for configurators.
 *
 * @since 1.7
 */
class EntryPointConfigurator extends EntryPointPreConfigurator
{
    /**
     * Declare web assets into the entry point config.
     *
     * @param string $name       the name of webassets
     * @param array  $values     should be an array with one or more of these keys 'css' (array), 'js'  (array), 'require' (string)
     * @param string $collection the name of the webassets collection
     * @param bool   $force
     */
    public function declareWebAssets($name, array $values, $collection, $force)
    {
        $ini = $this->entryPoint->getSingleConfigIni();
        $this->globalSetup->declareWebAssetsInConfig($ini, $name, $values, $collection, $force);
    }
}
