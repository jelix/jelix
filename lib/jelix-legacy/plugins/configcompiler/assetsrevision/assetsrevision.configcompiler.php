<?php
/**
 * @package      jelix
 * @subpackage   core_config_plugin
 *
 * @author       Laurent Jouanneau
 * @copyright    2022 Laurent Jouanneau
 *
 * @see         https://jelix.org
 * @licence      GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

class assetsrevisionConfigCompilerPlugin implements \Jelix\Core\Config\CompilerPluginInterface
{
    public function getPriority()
    {
        return 17;
    }

    public function atStart($config)
    {
        if ($config->urlengine['assetsRevision'] == 'autoconfig') {
            $config->urlengine['assetsRevision'] = date('ymdHis');
        }
        if ($config->urlengine['assetsRevision'] != '') {
            $config->urlengine['assetsRevQueryUrl'] = $config->urlengine['assetsRevisionParameter'].'='.$config->urlengine['assetsRevision'];
        } else {
            $config->urlengine['assetsRevQueryUrl'] = '';
        }
    }

    public function onModule($config, \Jelix\Core\Infos\ModuleInfos $module)
    {
    }

    public function atEnd($config)
    {
    }
}
