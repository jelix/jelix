<?php
/**
* @package    jelix
* @subpackage utils
* @author Laurent Jouanneau
* @contributor Thiriot Christophe
* @contributor Bastien Jaillot
* @contributor Loic Mathaud
* @contributor Cedric (fix bug ticket 56)
* @copyright   2005-2006 Laurent Jouanneau, 2006 Thiriot Christophe, 2006 Loic Mathaud, 2008 Bastien Jaillot
* @link        http://www.jelix.org
* @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*/


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
    * @author     Croes Gérald
    * @copyright  2001-2005 CopixTeam
    * @link http://www.copix.org
    */
    public static function write ($file, $data){
        $_dirname = dirname($file);

        //asking to create the directory structure if needed.
        self::createDir ($_dirname);

        if(!@is_writable($_dirname)) {
            // cache_dir not writable, see if it exists
            if(!@is_dir($_dirname)) {
                throw new jException('jelix~errors.file.directory.notexists', array ($_dirname));
            }
            throw new jException('jelix~errors.file.directory.notwritable', array ($file, $_dirname));
        }

        // write to tmp file, then rename it to avoid
        // file locking race condition
        $_tmp_file = tempnam($_dirname, 'wrt');

        if (!($fd = @fopen($_tmp_file, 'wb'))) {
            $_tmp_file = $_dirname . '/' . uniqid('wrt');
            if (!($fd = @fopen($_tmp_file, 'wb'))) {
                throw new jException('jelix~errors.file.write.error', array ($file, $_tmp_file));
            }
        }

        fwrite($fd, $data);
        fclose($fd);

        // Delete the file if it allready exists (this is needed on Win,
        // because it cannot overwrite files with rename()
        if ($GLOBALS['gJConfig']->isWindows && file_exists($file)) {
            unlink($file);
        }
        rename($_tmp_file, $file);
        @chmod($file,  0664);

        return true;
    }

    /**
    * create a directory
    * It creates also all necessary parent directory
    * @param string $dir the path of the directory
    */
    public static function createDir ($dir){
        // recursive feature on mkdir() is broken with PHP 5.0.4 for Windows
        // so should do own recursion
        if (!file_exists($dir)) {
            self::createDir(dirname($dir));
            mkdir($dir, 0775);
        }
    }

    /**
     * Recursive function deleting a directory
     *
     * @param string $path The path of the directory to remove recursively
     * @param boolean $deleteParent If the path must be deleted too
     * @since 1.0b1
     * @author Loic Mathaud
     */
    public static function removeDir($path, $deleteParent=true) {
        $dir = new DirectoryIterator($path);
        foreach ($dir as $dirContent) {
        	// file deletion
            if ($dirContent->isFile() || $dirContent->isLink()) {
        		unlink($dirContent->getPathName());
        	} else {
        		// recursive directory deletion
                if (!$dirContent->isDot() && $dirContent->isDir()) {
                    self::removeDir($dirContent->getPathName());
        		}
        	}
        }
        // removes the parent directory
        if ($deleteParent) {
            rmdir($path);
        }
    }
}
?>
