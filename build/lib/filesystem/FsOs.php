<?php
/**
* @package     jBuildTools
* @author      Laurent Jouanneau
* @contributor
* @copyright   2012 Laurent Jouanneau
* @link        http://jelix.org
* @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
*/


class FsOs implements FileSystemInterface {

    protected $rootPath = '';

    function setRootPath($rootPath) {
        $this->rootPath = $rootPath;
    }

    function createDir($dir) {
        jBuildUtils::createDir($dir);
    }

    function copyFile($sourcefile, $targetFile) {
        if(!copy($sourcefile, $targetFile)){
            return false;
        }
        return true;
    }

    function setFileContent($file, $content) {
        file_put_contents($file, $content);
    }

    function removeFile($file) {
        if (!unlink($file))
            return false;
        return true;
    }

    function removeDir($dir) {
        if (!file_exists($dir)) {
            //echo "cannot remove $dir. It doesn't exist.\n";
            return;
        }
        jBuildUtils::removeDir($dir);
    }

}

