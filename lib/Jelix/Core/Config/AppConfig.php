<?php
/**
 * @author   Laurent Jouanneau
 * @copyright 2005-2023 Laurent Jouanneau
 *
 * @see        http://www.jelix.org
 * @licence  GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

namespace Jelix\Core\Config;

use Jelix\Core\App;

/**
 * static class which loads the configuration.
 *
 * @static
 * @see \Jelix\Core\Config\Compiler
 */
class AppConfig
{
    /**
     * indicate if the configuration was loading from the cache (true) or
     * if the cache configuration was regenerated (false).
     */
    public static $fromCache = true;

    const sectionsToIgnoreForEp = array(
        'httpVersion', 'timeZone', 'domainName', 'forceHTTPPort', 'forceHTTPSPort',
        'chmodFile', 'chmodDir', 'modules', '_coreResponses', 'compilation',
    );

    /**
     * this is a static class, so private constructor.
     */
    private function __construct()
    {
    }

    /**
     * Loads the configuration by optimizing it and hardening values, and use a cache to avoid reading all
     * configuration files at each call.
     *
     * To be used only for web runtime.
     *
     * @param string $configFile the configuration file name of the entrypoint
     *
     * @return object it contains all configuration parameters
     */
    public static function load($configFile)
    {
        $config = array();
        $file = Compiler::getCacheFilename($configFile);

        self::$fromCache = true;
        if (!file_exists($file)) {
            // no cache, let's compile
            self::$fromCache = false;
        } else {
            $t = filemtime($file);
            $dc = App::mainConfigFile();
            $lc = App::varConfigPath('localconfig.ini.php');
            $lvc = App::varConfigPath('liveconfig.ini.php');
            $appEpConfig = App::appSystemPath($configFile);
            $varEpConfig = App::varConfigPath($configFile);

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
                    $config = \Jelix\IniFile\Util::read($file, true);
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
            $compiler = new Compiler($configFile);

            return $compiler->readAndCache();
        }

        return $config;
    }

    /**
     * Loads the configuration by optimizing it and hardening values, without using a cache.
     *
     * To be used for web runtime and users cli commands.
     *
     * @param string $configFile the configuration file name of the entrypoint
     * @param string $scriptName the entrypoint script name
     * @return object
     * @throws Exception
     */
    public static function loadWithoutCache($configFile, $scriptName = '')
    {
        $compiler = new \Jelix\Core\Config\Compiler($configFile, $scriptName);
        return $compiler->read(false);
    }

    /**
     * Loads the configuration as is, for component that needs to manipulate the configuration content, without
     * configuration values hardened
     *
     * Do not call it for web runtime.
     *
     * @internal
     * @param string $configFile  the entrypoint configuration file
     * @param string $scriptName
     * @return object
     * @throws Exception
     */
    public static function loadForInstaller($configFile, $scriptName = '')
    {
        $compiler = new \Jelix\Core\Config\Compiler($configFile, $scriptName);
        return $compiler->read(true);
    }


    public static function getDefaultConfigFile()
    {
        return __DIR__.'/defaultconfig.ini.php';
    }
}
