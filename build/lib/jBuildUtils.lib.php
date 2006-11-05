<?php
/**
* @package     jBuildTools
* @author      Jouanneau Laurent
* @contributor
* @copyright   2006 Jouanneau laurent
* @link        http://www.jelix.org
* @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
*/


class jBuildUtils {

    static public function createDir ($dir){
        if (!file_exists($dir)) {
            self::createDir(dirname($dir));
            mkdir($dir, 0775);
        }
    }

    static public function normalizeDir($dirpath){
        if(substr($dirpath,-1) != '/'){
            $dirpath.='/';
        }
        return $dirpath;
    }
}

?>