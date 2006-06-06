<?php
/**
* @package  jelix
* @subpackage core
* @version  $Id$
* @author   Jouanneau Laurent
* @contributor
* @copyright 2005-2006 Jouanneau laurent
* @link        http://www.jelix.org
* @licence  GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/


class jConfig {


    private function __construct (){ }

    /**
     * lecture de la configuration du framework
     */

    static public function load($configFile){
        $config=array();
        $file = JELIX_APP_TEMP_PATH.$configFile.'.resultini.php';
        $compil=false;
        if(!file_exists($file)){
            $compil=true;
        }else{
            $t = filemtime($file);
            $dc = JELIX_APP_CONFIG_PATH.'defaultconfig.ini.php';
            if( (file_exists($dc) && filemtime($dc)>$t)
                || filemtime(JELIX_APP_CONFIG_PATH.$configFile)>$t){
                $compil=true;
            }else{
                $config = parse_ini_file($file,true);
                $config = (object) $config;
                if($config->compilation['checkCacheFiletime']){
                    $compil = self::_verifpath($config->modulesPath,$t);
                    if(!$compil){
                        $compil = self::_verifpath($config->pluginsPath,$t);
                        if(!$compil){
                            $compil = self::_verifpath($config->tplpluginsPath,$t);
                        }
                    }
                }
            }
        }
        if($compil){
            require_once(JELIX_LIB_CORE_PATH.'jConfigCompiler.class.php');
            return jConfigCompiler::read($configFile);
        }else
            return $config;
    }

    private static function _verifpath($list, $time){
        $list = split(' *, *',$list);
        foreach($list as $path){
            $path = str_replace(array('lib:','app:'), array(LIB_PATH, JELIX_APP_PATH), $path);
            if(!file_exists($path)){
                trigger_error($path.' path given in the config doesn\'t exist',E_USER_ERROR);
                exit;
            }
            if(filemtime($path)>$time){
                return true;
            }
        }
        return false;
    }
}


?>