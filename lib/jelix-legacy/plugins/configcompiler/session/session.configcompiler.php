<?php
/**
 * @package      jelix
 * @subpackage   core
 *
 * @author       Laurent Jouanneau
 * @copyright    2012 Laurent Jouanneau
 *
 * @see         http://jelix.org
 * @licence      GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */
class sessionConfigCompilerPlugin implements \Jelix\Core\Config\CompilerPluginInterface
{
    public function getPriority()
    {
        return 5;
    }

    public function atStart($config)
    {
        if ($config->sessions['storage'] == 'files') {
            $config->sessions['files_path'] = jFile::parseJelixPath($config->sessions['files_path']);
        }
    }

    public function onModule($config, Jelix\Core\Infos\ModuleInfos $module)
    {
    }

    public function atEnd($config)
    {
    }
}
