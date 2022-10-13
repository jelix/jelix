<?php
/**
 * @package    jelix
 * @subpackage utils
 *
 * @author     Loic Mathaud
 * @contributor Laurent Jouanneau
 *
 * @copyright  2006 Loic Mathaud, 2010-2022 Laurent Jouanneau
 *
 * @see        http://www.jelix.org
 * @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */

/**
 * utilities to manage a jelix application.
 *
 * @package    jelix
 * @subpackage utils
 *
 * @since 1.0b1
 * @static
 */
class jAppManager
{
    private function __construct()
    {
    }

    /**
     * close the application, by creating a CLOSED file.
     *
     * @param string $message the message to display
     *
     * @since 1.2
     */
    public static function close($message = '')
    {
        $file = jApp::varConfigPath('CLOSED');
        file_put_contents($file, $message);
        if (jApp::config()) {
            chmod($file, jApp::config()->chmodFile);
        }
    }

    /**
     * open the application.
     *
     * @since 1.2
     */
    public static function open()
    {
        if (file_exists(jApp::varConfigPath('CLOSED'))) {
            unlink(jApp::varConfigPath('CLOSED'));
        }
    }

    /**
     * tell if the application is opened.
     *
     * @return bool true if the application is opened
     *
     * @since 1.2
     */
    public static function isOpened()
    {
        return !file_exists(jApp::varConfigPath('CLOSED'));
    }

    /**
     * @param string $path alternative path to the temp path
     * @return bool true if all the content has been removed
     */
    public static function clearTemp($path = '')
    {
        if ($path == '') {
            $path = jApp::tempBasePath();
            if ($path == '') {
                throw new Exception('default temp base path is not defined', 1);
            }
        }

        if ($path == DIRECTORY_SEPARATOR || $path == '' || $path == '/') {
            throw new Exception('The temp path is invalid. The path set into the application.init.php is not correct', 2);
        }
        if (!file_exists($path)) {
            throw new Exception('The temp path does not exists', 3);
        }

        if (!is_writable($path)) {
            throw new Exception('The temp directory is not writable', 4);
        }
        // check if subdirectories are writable
        $dir = new \DirectoryIterator($path);
        foreach ($dir as $dirContent) {
            if (!$dirContent->isDot()) {
                if( !$dir->isWritable()) {
                    unset($dir);
                    throw new Exception('Cannot delete content of temp path because of lack of rights (not writable)', 5);
                }
            }
        }

        // do not erase .empty or .dummy files that are into the temp directory
        // for source code repositories
        return jFile::removeDir($path, false, array('.dummy', '.empty', '.svn'));
    }
}
