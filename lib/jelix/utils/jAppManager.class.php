<?php
/**
* @package    jelix
* @subpackage utils
* @author     Loic Mathaud
* @contributor
* @copyright  2006 Loic Mathaud
* @link        http://www.jelix.org
* @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*/

/**
* utilities to manage a jelix application
* @package    jelix
* @subpackage utils
* @since 1.0b1
* @static
*/
class jAppManager {

    public static function clearTemp() {
        if (!defined('JELIX_APP_TEMP_PATH')) {
            echo "Error: JELIX_APP_TEMP_PATH is not defined\n";
            exit(1);
        }

        if (JELIX_APP_TEMP_PATH == DIRECTORY_SEPARATOR || JELIX_APP_TEMP_PATH == '' || JELIX_APP_TEMP_PATH == '/') {
            echo "Error: bad path in JELIX_APP_TEMP_PATH, it is equals to '".JELIX_APP_TEMP_PATH."' !!\n";
            echo "       Jelix cannot clear the content of the temp directory.\n";
            echo "       Correct the path in JELIX_APP_TEMP_PATH or create the directory you\n";
            echo "       indicated into JELIX_APP_TEMP_PATH.\n";
            exit(1);
        }
        jFile::removeDir(JELIX_APP_TEMP_PATH, false);
    }
}
