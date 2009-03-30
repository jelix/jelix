<?php
/**
* @package     jBuildTools
* @author      Laurent Jouanneau
* @contributor
* @copyright   2006-2009 Laurent Jouanneau
* @link        http://jelix.org
* @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
*/


class jBuildUtils {

    static public function createDir ($dir, $vcs = ''){
        if (!file_exists($dir)) {
            self::createDir(dirname($dir), $vcs);
            mkdir($dir, 0775);
            if ($vcs == 'svn') {
                exec("svn add $dir");
            }
            else if ($vcs == 'hg') {
                exec("hg add $dir");
            }
        }
    }

    static public function normalizeDir($dirpath){
        if(substr($dirpath,-1) != '/'){
            $dirpath.='/';
        }
        return $dirpath;
    }
    
    /**
     * @param string $dir  the path of the dir to delete
     * @param string $cmd  the command name to use to remove the directory
     *                     supported commands: 'rm', 'svn', 'hg'
     */
    static public function removeDir ($dir, $cmd = 'rm') {
      if (!file_exists($dir)) {
          //echo "cannot remove $dir. It doesn't exist.\n";
          return;
      }
      switch($cmd) {
          case 'rm':
              self::_removeDir($dir);
              break;
          case 'svn':
              exec("svn remove $dir");
              break;
          case 'hg':
              exec("hg remove $dir");
              break;
          case '':
          case 'none':
              // do nothing
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
    public static function _removeDir($path, $deleteParent=true) {

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
                    self::_removeDir($dirContent->getPathName());
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
