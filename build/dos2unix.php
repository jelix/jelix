<?php

/**
* @package     jBuildTools
* @author      Laurent Jouanneau
* @contributor
* @copyright   2006-2007 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
*/

require_once(__DIR__.'/lib/jCmdUtils.class.php');

// arguments:  directory
// directory: directory path where the files will be converted

try{
    $sws = array('-n'=>false);
    $params = array('dirpath'=>true);

    list($switches, $parameters) = jCmdUtils::getOptionsAndParams($_SERVER['argv'], $sws, $params);

}catch(Exception $e){
    echo "dos2unix error : " , $e->getMessage(),"\n";
    echo "php dos2unix.php [-n] dir
   -n: no modification, test mode\n";
    exit(1);
}

$dirpath = $parameters['dirpath'];

if(substr($dirpath,-1) == '/'){
   $dirpath=substr($dirpath,0,-1);
}

function parsePath($dir){
    global $switches;
    if ($dh = opendir($dir)) {
        $dirlist=array();
        $cdok=false;
        while (($file = readdir($dh)) !== false) {
            if($file == '.svn')
                continue;
            $type= filetype($dir.'/'.$file);
            if($type == 'dir'){
                if($file != '.' && $file !='..' ){
                    $dirlist[]=$file;
                }
            }else{
                if(preg_match("/\.(php|xml|rng|dist|tpl|properties)$/",$file)) {
                    $content = file_get_contents($dir.'/'.$file);
                    if(strpos($content,"\r") !== false) {
                        if (isset($switches['-n'])) {
                            echo $dir.'/'.$file." need to be converted\n";
                        }
                        else {
                            echo " convert ".$dir.'/'.$file."\n";
                            $content = str_replace("\r\n","\n", $content);
                            file_put_contents($dir.'/'.$file,$content);
                        }
                    }
                    else {
                        //echo "  ".$dir.'/'.$file."\n";
                    }
                } else {
                    //echo "! no parsing $file\n";
                }
            }
        }
        closedir($dh);
        foreach($dirlist as $d){
            parsePath( $dir.'/'.$d);
        }
    }else{
        echo "!!! error when reading directory $dir !!!\n";
        exit(1);
    }
}



$output='';
if (is_dir($dirpath)) {
    parsePath($dirpath);
}else{
    echo "wrong directory $dirpath\n";
    exit(1);
}

echo "\n\nend\n";
exit(0);
?>
