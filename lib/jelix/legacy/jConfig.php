<?php
/**
 * @package  jelix
 * @subpackage core
 *
 * @author   Laurent Jouanneau
 * @copyright 2005-2026 Laurent Jouanneau
 *
 * @see        http://www.jelix.org
 * @licence  GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

use Jelix\Core\Config\AppConfig;

/**
 * static class which loads the configuration.
 *
 * It does not use the cache generated during the warmup.
 *
 * @package  jelix
 * @subpackage core
 * @static
 * @deprecated use Jelix\Core\Config\AppConfig instead
 */
class jConfig
{
    /**
     * indicate if the configuration was loading from the cache (true) or
     * if the cache configuration was regenerated (false).
     */
    public static $fromCache = true;

    const sectionsToIgnoreForEp = array(
        'httpVersion', 'timeZone', 'domainName', 'forceHTTPPort', 'forceHTTPSPort',
        'chmodFile', 'chmodDir',
        'modules', '_coreResponses', 'compilation',
    );

    /**
     * this is a static class, so private constructor.
     */
    private function __construct()
    {
    }

    /**
     * load and read the configuration of the application
     * The combination of all configuration files (the given file
     * and the mainconfig.ini.php) is stored
     * in a single temporary file. So it calls the jConfigCompiler
     * class if needed.
     *
     * @param string $configFile the config file name
     *
     * @return object it contains all configuration options
     *
     * @see jConfigCompiler
     */
    public static function load($configFile)
    {
        $config = array();
        $file = jConfigCompiler::getCacheFilename($configFile);

        self::$fromCache = true;
        if (!file_exists($file)) {
            // no cache, let's compile
            self::$fromCache = false;
        } else {
            $t = filemtime($file);
            $dc = jApp::mainConfigFile();
            $lc = jApp::varConfigPath('localconfig.ini.php');
            $lvc = jApp::varConfigPath('liveconfig.ini.php');
            $appEpConfig = jApp::appSystemPath($configFile);
            $varEpConfig = jApp::varConfigPath($configFile);

            if ((file_exists($dc) && filemtime($dc) > $t)
                || (file_exists($appEpConfig) && filemtime($appEpConfig) > $t)
                || (file_exists($varEpConfig) && filemtime($varEpConfig) > $t)
                || (file_exists($lc) && filemtime($lc) > $t)
                || (file_exists($lvc) && filemtime($lvc) > $t)
            ) {
                // one of the config files have been modified: let's compile
                self::$fromCache = false;
            } else {
                // let's read the cache file
                if (BYTECODE_CACHE_EXISTS) {
                    include $file;
                    $config = (object) $config;
                } else {
                    $config = \jFile::mergeIniFile($file);
                }

                // we check all directories to see if it has been modified
                if ($config->compilation['checkCacheFiletime']) {
                    foreach ($config->_allBasePath as $path) {
                        if (!file_exists($path) || filemtime($path) > $t) {
                            self::$fromCache = false;

                            break;
                        }
                    }
                }
            }
        }
        if (!self::$fromCache) {
            return jConfigCompiler::readAndCache($configFile);
        }

        return $config;
    }

    /**
     * @deprecated use AppConfig::getDefaultConfigFile() instead
     * @return string
     */
    public static function getDefaultConfigFile()
    {
        return AppConfig::getDefaultConfigFile();
    }
}
