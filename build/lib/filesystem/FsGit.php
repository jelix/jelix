<?php
/**
* @package     jBuildTools
* @author      Laurent Jouanneau
* @contributor
* @copyright   2012 Laurent Jouanneau
* @link        http://jelix.org
* @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
*/


class FsGit extends FsHg {
    protected $vcs = 'git';

    function removeDir($dir) {
        if (file_exists($this->rootPath.$dir)) {
            return $this->launchCommand("rm -r $dir");
        }
        return false;
    }
}