<?php
/**
* @package    jelix
* @subpackage utils
* @version    $Id:$
* @author     Loic Mathaud
* @contributor
* @copyright  2006 Loic Mathaud
* @link        http://www.jelix.org
* @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*/

/**
*
* @package    jelix
* @subpackage utils
*/
class jAppManager {

    public static function clearTemp() {
        jFile::removeDir(JELIX_APP_TEMP_PATH, false);
    }
}

?>
