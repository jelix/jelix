<?php
/**
* @package    jelix
* @subpackage utils
* @author     Loic Mathaud
* @contributor Laurent Jouanneau
* @copyright  2006 Loic Mathaud, 2010 Laurent Jouanneau
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

    private function __construct() {}

    /**
     * close the application, by creating a CLOSED file
     * @param string $message the message to display
     * @since 1.2
     */
    public static function close($message='') {
        file_put_contents(JELIX_APP_CONFIG_PATH.'CLOSED', $message);
    }

    /**
     * open the application
     * @since 1.2
     */
    public static function open() {
        if (file_exists(JELIX_APP_CONFIG_PATH.'CLOSED'))
            unlink(JELIX_APP_CONFIG_PATH.'CLOSED');
    }

    /**
     * tell if the application is opened
     * @return boolean true if the application is opened
     * @since 1.2
     */
    public static function isOpened() {
        return !file_exists(JELIX_APP_CONFIG_PATH.'CLOSED');
    }

    public static function clearTemp($path='') {
        if ($path == '') {
            if (!defined('JELIX_APP_TEMP_PATH')) {
                throw new Exception("default temp path is not defined",1);
            }
            $path = JELIX_APP_TEMP_PATH;
        }

        if ($path == DIRECTORY_SEPARATOR || $path == '' || $path == '/') {
            throw new Exception('given temp path is invalid', 2);
        }
        if (!file_exists($path))
            throw new Exception('given temp path does not exists', 3);

        if (!is_writeable($path))
            throw new Exception('given temp path does not exists', 4);

        jFile::removeDir($path, false);
    }
}
