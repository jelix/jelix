<?php
/**
* @package    jelix
* @subpackage utils
* @author Laurent Jouanneau
* @contributor Christophe Thiriot
* @contributor Bastien Jaillot
* @contributor Loic Mathaud
* @contributor Olivier Demah (#733)
* @contributor Cedric (fix bug ticket 56)
* @contributor Julien Issler
* @copyright   2005-2016 Laurent Jouanneau, 2006 Christophe Thiriot, 2006 Loic Mathaud, 2008 Bastien Jaillot, 2008 Olivier Demah, 2009-2010 Julien Issler
* @link        http://www.jelix.org
* @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*/

use \Jelix\FileUtilities\File;
use \Jelix\FileUtilities\Directory;
use \Jelix\FileUtilities\Path;

/**
 * A class helper to read or create files
 * @package    jelix
 * @subpackage utils
 */
class jFile {
    /**
    * Reads the content of a file.
    * @param string $filename the filename we're gonna read
    * @return string the content of the file. false if cannot read the file
    */
    public static function read ($filename){
        return @file_get_contents ($filename, false);
    }

    /**
    * Write a file to the disk.
    * This function is heavily based on the way smarty process its own files.
    * Is using a temporary file and then rename the file. We guess the file system will be smarter than us, avoiding a writing / reading
    *  while renaming the file.
    * This method comes from CopixFile class of Copix framework
    * @author     Gérald Croes
    * @copyright  2001-2005 CopixTeam
    * @link http://www.copix.org
    */
    public static function write ($file, $data, $chmod=null){
        if (!$chmod && jApp::config()) {
            $chmod = jApp::config()->chmodFile;
        }
        return File::write($file, $data, $chmod);
    }

    /**
    * create a directory
    * It creates also all necessary parent directory
    * @param string $dir the path of the directory
    */
    public static function createDir ($dir, $chmod=null){
        if ($chmod === null && jApp::config()) {
            $chmod =  jApp::config()->chmodDir;
        }
        return Directory::create($dir, $chmod);
    }

    /**
     * Recursive function deleting a directory
     *
     * @param string $path The path of the directory to remove recursively
     * @param boolean $deleteParent If the path must be deleted too
     * @param array $except filenames and suffix of filename, for files to NOT delete
     * @return bool true if all the content has been removed
     * @throws jException
     * @since 1.0b1
     * @author Loic Mathaud
     */
    public static function removeDir($path, $deleteParent=true, $except=array()) {
        if (is_array($except) && count($except)) {
            return Directory::removeExcept($path, $except, $deleteParent);
        }
        else {
            return Directory::remove($path, $deleteParent);
        }
    }

    /**
     * get the MIME Type of a file
     *
     * @param string $file The full path of the file
     * @return string the MIME type of the file
     * @since 1.1.6
     * @deprecated  use \Jelix\FileUtilities\File::getMimeType() instead
     */
    public static function getMimeType($file){
        trigger_error("jFile::getMimeType is deprecated. Use \\Jelix\\FileUtilities\\File::getMimeType() instead.", E_USER_DEPRECATED);
        return File::getMimeType($file);
    }

    /**
     * get the MIME Type of a file, only with its name
     *
     * @param string $fileName the file name
     * @return string the MIME type of the file
     * @since 1.1.10
     */
    public static function getMimeTypeFromFilename($fileName){
        $mimetype = File::getMimeTypeFromFilename($fileName);
        if ($mimetype == 'application/octet-stream' &&
            jApp::config() &&
            isset(jApp::config()->mimeTypes[$ext])) {
            return jApp::config()->mimeTypes[$ext];
        }
        return $mimetype;
    }

    /**
     * parse a path replacing Jelix shortcuts parts (var:, temp:, www:, app:, lib:)
     *
     * @param string $path the path with parts to replace
     * @return string the path which is a system valid path
     */
    public static function parseJelixPath($path){
        return str_replace(
            array('lib:', 'app:', 'var:', 'temp:', 'www:', 'log:', 'varconfig:', 'appconfig:'),
            array(LIB_PATH, jApp::appPath(), jApp::varPath(), jApp::tempPath(),
                jApp::wwwPath(), jApp::logPath(), jApp::varConfigPath(), jApp::appConfigPath()),
            $path );
    }

    /**
     * replace a path with Jelix shortcuts parts (var:, temp:, www: app:, lib:)
     *
     * @param string $path the system valid path
     * @param string $beforeShortcut a string to be output before the Jelix shortcut
     * @param string $afterShortcut a string to be output after the Jelix shortcut
     * @return string the path with Jelix shortcuts parts
     */
    public static function unparseJelixPath($path, $beforeShortcut='', $afterShortcut=''){

        if (strpos($path, LIB_PATH) === 0) {
            $shortcutPath = LIB_PATH;
            $shortcut = 'lib:';
        }
        elseif (strpos($path, jApp::tempPath()) === 0) {
            $shortcutPath = jApp::tempPath();
            $shortcut = 'temp:';
        }
        elseif (strpos($path, jApp::wwwPath()) === 0) {
            $shortcutPath = jApp::wwwPath();
            $shortcut = 'www:';
        }
        elseif (strpos($path, jApp::varPath()) === 0) {
            $shortcutPath = jApp::varPath();
            $shortcut = 'var:';
        }
        elseif (strpos($path, jApp::appPath()) === 0) {
            $shortcutPath = jApp::appPath();
            $shortcut = 'app:';
        }
        else {
            $shortcutPath = dirname(jApp::appPath());
            $shortcut = 'app:';
            while ($shortcutPath != '.' && $shortcutPath != '') {
                $shortcut .= '../';
                if (strpos($path, $shortcutPath) === 0) {
                    break;
                }
                $shortcutPath = dirname($shortcutPath);
            }
            if ($shortcutPath =='.')
                $shortcutPath = '';
        }
        if ($shortcutPath != '') {
            $cut = ($shortcutPath[0] == '/'?0:1);
            $path = $beforeShortcut.$shortcut.$afterShortcut.substr($path, strlen($path)+$cut);
        }

        return $path;
    }

    /**
     * calculate the shortest path between two directories
     * @param string $from  absolute path from which we should start
     * @param string $to  absolute path to which we should go
     * @return string relative path between the two path
     * @deprecated use \Jelix\FileUtilities\Path::shortestPath() instead
     */
    public static function shortestPath($from, $to) {
        trigger_error("jFile::shortestPath() is deprecated. Use \\Jelix\\FileUtilities\\Path::shortestPath() instead.", E_USER_DEPRECATED);
        return Path::shortestPath($from, $to);
    }

    /**
     * normalize a path : translate '..', '.', replace '\' by '/' and so on..
     * support windows path.
     * @param string $path
     * @return string the normalized path
     * @deprecated Use \Jelix\FileUtilities\Path::normalizePath() instead
     */
    public static function normalizePath($path) {
        trigger_error("jFile::normalizePath is deprecated. Use \\Jelix\\FileUtilities\\Path::normalizePath() instead.", E_USER_DEPRECATED);
        return Path::normalizePath($path);
    }
}
