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
            // pas de cache, on compile
            $compil=true;
        }else{
            $t = filemtime($file);
            $dc = JELIX_APP_CONFIG_PATH.'defaultconfig.ini.php';
            if( (file_exists($dc) && filemtime($dc)>$t)
                || filemtime(JELIX_APP_CONFIG_PATH.$configFile)>$t){
                // le fichier de conf ou le fichier defaultconfig.ini.php ont ete modifié : on compile
                $compil=true;
            }else{

                // on lit le fichier de conf du cache
#if WITH_BYTECODE_CACHE == 'auto'
                if(BYTECODE_CACHE_EXISTS){
                    include($file);
                    $config = (object) $config;
                }else{
#if ENABLE_PHP_JELIX
                    $config = jelix_read_ini($file);
#else
                    $config = parse_ini_file($file,true);
                    $config = (object) $config;
#endif
                }
#elseif WITH_BYTECODE_CACHE 
                include($file);
                $config = (object) $config;
#else
#if ENABLE_PHP_JELIX
                $config = jelix_read_ini($file);
#else
                $config = parse_ini_file($file,true);
                $config = (object) $config;
#endif
#endif
                // on va verifier tous les chemins
                if($config->compilation['checkCacheFiletime']){
                    foreach($config->_allBasePath as $path){
                        if(!file_exists($path) || filemtime($path)>$t){
                            $compil = true;
                            break;
                        }
                    }
                }
            }
        }
        if($compil){
            require(JELIX_LIB_CORE_PATH.'jConfigCompiler.class.php');
            return jConfigCompiler::read($configFile);
        }else
            return $config;
    }
}


?>