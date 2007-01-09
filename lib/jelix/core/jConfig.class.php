<?php
/**
* @package  jelix
* @subpackage core
* @author   Jouanneau Laurent
* @contributor
* @copyright 2005-2007 Jouanneau laurent
* @link        http://www.jelix.org
* @licence  GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

/**
 * static class which loads the configuration
 * @package  jelix
 * @subpackage core
 * @static
 */
class jConfig {

    /**
     * this is a static class, so private constructor
     */
    private function __construct (){ }

    /**
     * load and read the configuration of the application
     * The combination of all configuration files (the given file
     * and the defaultconfig.ini.php) is stored
     * in a single temporary file. So it calls the jConfigCompiler
     * class if needed
     * @param string $configFile the config file name
     * @return object it contains all configuration options
     * @see jConfigCompiler
     */
    static public function load($configFile){
        $config=array();
#if WITH_BYTECODE_CACHE == 'auto'
        if(BYTECODE_CACHE_EXISTS)
            $file = JELIX_APP_TEMP_PATH.str_replace('/','~',$configFile).'.conf.php';
        else
            $file = JELIX_APP_TEMP_PATH.str_replace('/','~',$configFile).'.resultini.php';
#elseif WITH_BYTECODE_CACHE 
        $file = JELIX_APP_TEMP_PATH.str_replace('/','~',$configFile).'.conf.php';
#else
        $file = JELIX_APP_TEMP_PATH.str_replace('/','~',$configFile).'.resultini.php';
#endif
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
#if WITH_BYTECODE_CACHE == 'auto'
                if(BYTECODE_CACHE_EXISTS){
                    include($file);
                }else{
                    $config = parse_ini_file($file,true);
                }
#elseif WITH_BYTECODE_CACHE 
                include($file);
#else
                $config = parse_ini_file($file,true);
#endif
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

    /**
     * verify the existance of a path
     */
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