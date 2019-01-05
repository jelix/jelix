<?php
/**
 * @package     article
 * @subpackage  article module
 * @author      Laurent Jouanneau
 * @contributor
 * @copyright   2016 Laurent Jouanneau
 * @link        http://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */


class articlesModuleInstaller extends \Jelix\Installer\Module\Uninstaller {

    function uninstall(\Jelix\Installer\Module\API\InstallHelpers $helpers) {
        echo "Article module uninstalled is called!\n";
    }
}
