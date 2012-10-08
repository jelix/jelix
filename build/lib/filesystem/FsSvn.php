<?php
/**
* @package     jBuildTools
* @author      Laurent Jouanneau
* @contributor
* @copyright   2012 Laurent Jouanneau
* @link        http://jelix.org
* @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
*/


class FsSvn extends FsOs {
    protected $vcs = 'svn';

    protected function launchCommand($cmd) {
        $d = getcwd();
        chdir($this->rootPath);
        exec($this->vcs.' '.$cmd);
        chdir($d);
    }

    function createDir($dir) {
        if (!file_exists($dir)) {
            $this->createDir(dirname($dir));
            mkdir($dir, 0775);
            $this->launchCommand("add $dir");
        }
    }

    function copyFile($sourcefile, $targetFile) {
        $addToVcs = !file_exists($targetFile);
        if (parent::copyFile($sourcefile, $targetFile)) {
            if ($addToVcs)
                $this->launchCommand("add $targetFile");
            return true;
        }
        return false;
    }

    function setFileContent($file, $content) {
        $addToVcs = !file_exists($file);
        parent::setFileContent($file, $content);
        if ($addToVcs) {
            $this->launchCommand("add $file");
        }
    }

    function removeFile($file) {
        if (file_exists($file))
            $this->launchCommand("rm $file");
        return true;
    }

    function removeDir($dir) {
        if (file_exists($dir))
            $this->launchCommand("rm $dir");
    }
}

