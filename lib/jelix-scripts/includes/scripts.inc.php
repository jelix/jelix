<?php
/**
 * @package     jelix-scripts
 *
 * @author      Laurent Jouanneau
 * @contributor Loic Mathaud
 *
 * @copyright   2005-2016 Laurent Jouanneau
 *
 * @see        http://jelix.org
 * @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
 */
error_reporting(E_ALL);
define('JELIX_SCRIPTS_PATH', __DIR__.'/../');
//require (JELIX_SCRIPTS_PATH.'../jelix/init.php');

if (!jServer::isCLI()) {
    echo "Error: you're not allowed to execute this script outside a command line shell.\n";

    exit(1);
}

if (jApp::isInit()) {
    echo "Error: shouldn't run within an application\n";

    exit(1);
}
