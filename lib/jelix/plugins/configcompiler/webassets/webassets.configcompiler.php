<?php
/**
 * @package      jelix
 * @subpackage   core_config_plugin
 *
 * @author       Laurent Jouanneau
 * @copyright    2017 Laurent Jouanneau
 *
 * @see         http://jelix.org
 * @licence      GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */
class webassetsConfigCompilerPlugin implements \jelix\core\ConfigCompilerPluginInterface
{
    public function getPriority()
    {
        return 18;
    }

    public function atStart($config)
    {
        $compiler = new \Jelix\WebAssets\WebAssetsCompiler();
        $compiler->compile($config);
    }

    public function onModule($config, $moduleName, $modulePath, $xml)
    {
    }

    public function atEnd($config)
    {
    }
}
