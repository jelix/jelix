<?php
/**
* @package     jBuildTools
* @author      Laurent Jouanneau
* @contributor
* @copyright   2012 Laurent Jouanneau
* @link        http://jelix.org
* @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
*/


class FsHg extends FsSvn {
    protected $vcs = 'hg';

    function createDir($dir) {
        jBuildUtils::createDir($dir);
    }
}