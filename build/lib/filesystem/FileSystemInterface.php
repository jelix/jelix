<?php
/**
* @package     jBuildTools
* @author      Laurent Jouanneau
* @contributor
* @copyright   2012 Laurent Jouanneau
* @link        http://jelix.org
* @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
*/


interface FileSystemInterface {

    function setRootPath($rootPath);

    function createDir($dir);

    function copyFile($fileSource, $fileTarget);

    function setFileContent($file, $content);

    function removeFile($file);

    function removeDir($dir);
}

