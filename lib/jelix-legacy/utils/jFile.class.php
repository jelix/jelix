<?php
/**
 * @package    jelix
 * @subpackage utils
 *
 * @author Laurent Jouanneau
 * @contributor Christophe Thiriot
 * @contributor Bastien Jaillot
 * @contributor Loic Mathaud
 * @contributor Olivier Demah (#733)
 * @contributor Cedric (fix bug ticket 56)
 * @contributor Julien Issler
 *
 * @copyright   2005-2024 Laurent Jouanneau, 2006 Christophe Thiriot, 2006 Loic Mathaud, 2008 Bastien Jaillot, 2008 Olivier Demah, 2009-2010 Julien Issler
 *
 * @see        http://www.jelix.org
 * @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */
use Jelix\FileUtilities\Directory;
use Jelix\FileUtilities\File;
use Jelix\FileUtilities\Path;

/**
 * A class helper to read or create files.
 *
 * @package    jelix
 * @subpackage utils
 */
class jFile
{
    /**
     * Reads the content of a file.
     *
     * @param string $filename the filename we're gonna read
     *
     * @return string the content of the file. false if cannot read the file
     */
    public static function read($filename)
    {
        return @file_get_contents($filename, false);
    }

    /**
     * Write a file to the disk.
     * This function is heavily based on the way smarty process its own files.
     * Is using a temporary file and then rename the file. We guess the file system will be smarter than us, avoiding a writing / reading
     *  while renaming the file.
     * This method comes from CopixFile class of Copix framework.
     *
     * @author     Gérald Croes
     * @copyright  2001-2005 CopixTeam
     *
     * @see http://www.copix.org
     *
     * @param mixed      $file
     * @param mixed      $data
     * @param null|mixed $chmod
     */
    public static function write($file, $data, $chmod = null)
    {
        if (jApp::config()) {
            if (!$chmod) {
                $chmod = jApp::config()->chmodFile;
            }
            Directory::$defaultChmod = jApp::config()->chmodDir;
        }
        if ($data === null) {
            $data = '';
        }

        return File::write($file, $data, $chmod);
    }

    /**
     * create a directory
     * It creates also all necessary parent directory.
     *
     * @param string     $dir   the path of the directory
     * @param null|mixed $chmod
     */
    public static function createDir($dir, $chmod = null)
    {
        if ($chmod === null && jApp::config()) {
            $chmod = jApp::config()->chmodDir;
        }

        return Directory::create($dir, $chmod);
    }

    /**
     * Recursive function deleting a directory.
     *
     * @param string $path         The path of the directory to remove recursively
     * @param bool   $deleteParent If the path must be deleted too
     * @param array  $except       filenames and suffix of filename, for files to NOT delete
     *
     * @throws Exception
     *
     * @return bool true if all the content has been removed
     *
     * @since 1.0b1
     *
     * @author Loic Mathaud
     */
    public static function removeDir($path, $deleteParent = true, $except = array())
    {
        if (is_array($except) && count($except)) {
            return Directory::removeExcept($path, $except, $deleteParent);
        }

        return Directory::remove($path, $deleteParent);
    }

    /**
     * copy the whole content of a directory into an other.
     *
     * @param string $sourcePath the path of the directory content. It does not create
     *                           the directory itself into the target directory.
     * @param string $targetPath the full path of the directory to where to copy
     *                           the content. The directory is created if it does not exists.
     * @param mixed  $overwrite
     *
     * @since 1.6.19
     */
    public static function copyDirectoryContent($sourcePath, $targetPath, $overwrite = false)
    {
        if (jApp::config()) {
            Directory::$defaultChmod = jApp::config()->chmodDir;
        }
        Directory::copy($sourcePath, $targetPath, $overwrite);
    }

    /**
     * get the MIME Type of a file, only with its name.
     *
     * @param string $fileName the file name
     *
     * @return string the MIME type of the file
     *
     * @since 1.1.10
     */
    public static function getMimeTypeFromFilename($fileName)
    {
        if (jApp::config()
            && !property_exists(jApp::config(), 'FileMimeTypeRegistered')
        ) {
            jApp::config()->FileMimeTypeRegistered = true;
            if (property_exists(jApp::config(), 'mimeTypes')
                && is_array(jApp::config()->mimeTypes)
            ) {
                File::registerMimeTypes(jApp::config()->mimeTypes);
            }
        }

        return File::getMimeTypeFromFilename($fileName);
    }

    /**
     * parse a path replacing Jelix shortcuts parts (var:, temp:, www:, app:, lib:).
     *
     * @param string $path the path with parts to replace
     *
     * @return string the path which is a system valid path
     */
    public static function parseJelixPath($path)
    {
        $path = str_replace(
            array('lib:', 'app:', 'var:', 'temp:', 'www:', 'log:', 'varconfig:',
                'appconfig:', 'appsystem:', ),
            array(LIB_PATH, jApp::appPath(), jApp::varPath(), jApp::tempPath(),
                jApp::wwwPath(), jApp::logPath(), jApp::varConfigPath(),
                jApp::appSystemPath(), jApp::appSystemPath(), ),
            $path
        );
        if (strpos($path, 'jelixwww:') === 0 && jApp::config()) {
            $path = jApp::config()->urlengine['jelixWWWPath'].'/'.substr($path, 9);
        }

        return $path;
    }

    /**
     * replace a path with Jelix shortcuts parts (var:, temp:, www: app:, lib:).
     *
     * @param string $path           the system valid path
     * @param string $beforeShortcut a string to be output before the Jelix shortcut
     * @param string $afterShortcut  a string to be output after the Jelix shortcut
     *
     * @return string the path with Jelix shortcuts parts
     */
    public static function unparseJelixPath($path, $beforeShortcut = '', $afterShortcut = '')
    {
        if (strpos($path, LIB_PATH) === 0) {
            $shortcutPath = LIB_PATH;
            $shortcut = 'lib:';
        } elseif (strpos($path, jApp::tempPath()) === 0) {
            $shortcutPath = jApp::tempPath();
            $shortcut = 'temp:';
        } elseif (strpos($path, jApp::wwwPath()) === 0) {
            $shortcutPath = jApp::wwwPath();
            $shortcut = 'www:';
        } elseif (strpos($path, jApp::varPath()) === 0) {
            $shortcutPath = jApp::varPath();
            $shortcut = 'var:';
        } elseif (strpos($path, jApp::appPath()) === 0) {
            $shortcutPath = jApp::appPath();
            $shortcut = 'app:';
        } else {
            $shortcutPath = dirname(jApp::appPath());
            $shortcut = 'app:';
            while ($shortcutPath != '.' && $shortcutPath != '') {
                $shortcut .= '../';
                if (strpos($path, $shortcutPath) === 0) {
                    break;
                }
                $shortcutPath = dirname($shortcutPath);
            }
            if ($shortcutPath == '.') {
                $shortcutPath = '';
            }
        }
        if ($shortcutPath != '') {
            $cut = ($shortcutPath[0] == '/' ? 0 : 1);
            $path = $beforeShortcut.$shortcut.$afterShortcut.substr($path, strlen($path) + $cut);
        }

        return $path;
    }
}
