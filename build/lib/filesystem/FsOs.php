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
        $this->rootPath = rtrim($rootPath, '/').'/';
    }

    function createDir($dir) {
        return jBuildUtils::createDir($this->rootPath.$dir);
    }

    function copyFile($sourcefile, $targetFile) {
        if(!copy($sourcefile, $this->rootPath.$targetFile)){
            return false;
        }
        return true;
    }

    function setFileContent($file, $content) {
        file_put_contents($this->rootPath.$file, $content);
    }

    function removeFile($file) {
        if (!unlink($this->rootPath.$file))
            return false;
        return true;
    }

    function removeDir($dir) {
        if (!file_exists($this->rootPath.$dir)) {
            //echo "cannot remove $dir. It doesn't exist.\n";
            return false;
        }
        jBuildUtils::removeDir($this->rootPath.$dir);
        return true;
    }

}

