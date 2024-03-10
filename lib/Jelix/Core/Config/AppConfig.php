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
use Jelix\Core\Server;
use Jelix\IniFile\Util as IniFileMgr;

/**
 * static class which loads the configuration.
 *
 * @static
 * @see \Jelix\Core\Config\Compiler
 */
class AppConfig
{
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
     * return the path of file where to store the cache of the configuration.
     *
     * @param string $configFile the name of the configuration file of the entry
     *                           point into var/config/
     *
     * @return string the full path of the cache
     */
    public static function getCacheFilename($configFile)
    {
        $filename = App::tempPath().str_replace('/','~',$configFile);
        list($domain, $port) = Server::getDomainPortFromServer();
        if ($domain) {
            $filename .= '.'.$domain.'-'.$port;
        }
        if (BYTECODE_CACHE_EXISTS) {
            $filename .= '.conf.php';
        } else {
            $filename .= '.resultini.php';
        }

        return $filename;
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
        $file = self::getCacheFilename($configFile);

        $rebuildCache = false;
        if (!file_exists($file)) {
            // no cache, let's compile
            $rebuildCache = true;
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
                $rebuildCache = true;
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
                            $rebuildCache = true;

                            break;
                        }
                    }
                }
            }
        }
        if ($rebuildCache) {
            $compiler = new Compiler($configFile);

            $config = $compiler->read(false);
            $tempPath = App::tempPath();
            \jFile::createDir($tempPath, $config->chmodDir);

            // if bytecode cache is enabled, it's better to store configuration
            // as PHP code, reading performance are much better than reading
            // an ini file (266 times less). However, if bytecode cache is disabled,
            // reading performance are better with ini : 32% better. Json is only 22% better.
            if (BYTECODE_CACHE_EXISTS) {
                if ($f = @fopen($file, 'wb')) {
                    fwrite($f, '<?php $config = '.var_export(get_object_vars($config), true).";\n?>");
                    fclose($f);
                    chmod($file, $config->chmodFile);
                } else {
                    throw new Exception('Error while writing configuration cache file -- '.$file);
                }
            } else {
                IniFileMgr::write(get_object_vars($config), $file.'.resultini.php', ";<?php die('');?>\n", $config->chmodFile);
            }
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
