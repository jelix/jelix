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


class articlesModuleInstaller extends jInstallerModule2 {

    function installEntrypoint(\Jelix\Installer\EntryPoint $entryPoint) {
    }

    function uninstallEntrypoint(\Jelix\Installer\EntryPoint $entryPoint) {
        echo "Article module uninstalled is called!\n";
    }
}
