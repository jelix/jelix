<?php
/**
 * @package     jelix-modules
 * @subpackage  jpref module
 * @author      Laurent Jouanneau
 * @copyright   2018 Laurent Jouanneau
 * @link        http://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

class jprefModuleConfigurator extends jInstallerModuleConfigurator {

    public function configure() {
        $path = jApp::appConfigPath('preferences.ini.php');
        if (!file_exists($path)) {
            file_put_contents($path, ";<"."?php die(''); ?>\n;for security reasons , don't remove or modify the first line\n\n");
        }
    }

}