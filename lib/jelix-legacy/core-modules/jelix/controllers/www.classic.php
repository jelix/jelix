<?php
/**
 * @package    jelix-modules
 * @subpackage jelix-module
 *
 * @author     Laurent Jouanneau
 * @copyright  2011-2023 Laurent Jouanneau
 * @licence    http://www.gnu.org/licenses/gpl.html GNU General Public Licence, see LICENCE file
 */

/**
 * @package    jelix-modules
 * @subpackage jelix-module
 */
class wwwCtrl extends jController
{
    public function getfile()
    {
        $module = $this->param('targetmodule');

        if (!jApp::isModuleEnabled($module) || !jApp::config()->modules[$module.'.enabled']) {
            throw new jException('jelix~errors.module.untrusted', $module);
        }

        $dir = jApp::getModulePath($module).'www/';
        $filename = realpath($dir.str_replace('..', '', $this->param('file')));

        return $this->getFileResponse($filename);
    }
}
