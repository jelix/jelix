<?php
/**
* @package     jBuildTools
* @author      Laurent Jouanneau
* @contributor
* @copyright   2006-2012 Laurent Jouanneau
* @link        http://jelix.org
* @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
*/


class jBuildUtils {

    /**
     * create a directory
     * @return boolean  false if the directory did already exist
     */
    static public function createDir ($dir){
        if (!file_exists($dir)) {
            self::createDir(dirname($dir));
            mkdir($dir, 0775);
            return true;
        }
        return false;
    }

    static public function normalizeDir($dirpath){
        if(substr($dirpath,-1) != '/'){
            $dirpath.='/';
        }
        return $dirpath;
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

        if($path == '' || $path == '/' || $path == DIRECTORY_SEPARATOR)
            throw new Exception('The root cannot be removed !!'); //see ticket #840

        $dir = new DirectoryIterator($path);
        foreach ($dir as $dirContent) {
            // file deletion
            if ($dirContent->isFile() || $dirContent->isLink()) {
                unlink($dirContent->getPathName());
            }
            else {
                // recursive directory deletion
                if (!$dirContent->isDot() && $dirContent->isDir()) {
                    self::removeDir($dirContent->getPathName());
              }
            }
        }
        unset($dir); // see bug #733
        unset($dirContent);

        // removes the parent directory
        if ($deleteParent) {
            rmdir($path);
        }
    }
}
