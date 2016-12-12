<?php
/**
* @author   Laurent Jouanneau
* @copyright 2005-2014 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence  GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

namespace Jelix\Core;

/**
 * static class which loads the configuration
 * @static
 */
class Config {

    /**
     * indicate if the configuration was loading from the cache (true) or
     * if the cache configuration was regenerated (false)
     */
    public static $fromCache = true;

    /**
     * this is a static class, so private constructor
     */
    private function __construct (){ }

    /**
     * load and read the configuration of the application
     * The combination of all configuration files (the given file
     * and the mainconfig.ini.php) is stored
     * in a single temporary file. So it calls the jConfigCompiler
     * class if needed
     * @param string $configFile the config file name
     * @return object it contains all configuration options
     * @see Jelix\Core\Config\Compiler
     */
    static public function load($configFile){
        $config = array();
        $file = App::tempPath().str_replace('/','~',$configFile);

        if (BYTECODE_CACHE_EXISTS)
            $file .= '.conf.php';
        else
            $file .= '.resultini.php';

        self::$fromCache = true;
        if (!file_exists($file)) {
            // no cache, let's compile
            self::$fromCache = false;
        }
        else {
            $t = filemtime($file);
            $dc = App::mainConfigFile();
            $lc = App::varConfigPath('localconfig.ini.php');

            if ((file_exists($dc) && filemtime($dc)>$t)
                || filemtime(App::appConfigPath($configFile))>$t
                || (file_exists($lc) && filemtime($lc)>$t)){
                // one of the config files have been modified: let's compile
                self::$fromCache = false;
            }
            else {
                // let's read the cache file
                if (BYTECODE_CACHE_EXISTS) {
                    include($file);
                    $config = (object) $config;
                }
                else {
                    $config = \Jelix\IniFile\Util::read($file, true);
                }

                // we check all directories to see if it has been modified
                if ($config->compilation['checkCacheFiletime']) {
                    foreach ($config->_allBasePath as $path) {
                        if (!file_exists($path) || filemtime($path)>$t) {
                            self::$fromCache = false;
                            break;
                        }
                    }
                }
            }
        }
        if (!self::$fromCache) {
            $compiler = new Config\Compiler($configFile);
            return $compiler->readAndCache();
        }
        else {
            return $config;
        }
    }

    static function getDefaultConfigFile() {
        return __DIR__.'/Config/defaultconfig.ini.php';
    }
}

