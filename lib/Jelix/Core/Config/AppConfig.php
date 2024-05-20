<?php
/**
 * @author   Laurent Jouanneau
 * @copyright 2005-2024 Laurent Jouanneau
 *
 * @see      https://www.jelix.org
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


    public static function getStaticBuildFilename($configFile)
    {
        $filename = 'config/static_'.str_replace('/', '~', $configFile);

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
        $cacheFile = self::getCacheFilename($configFile);

        $staticConfigFile = App::buildPath(self::getStaticBuildFilename($configFile));

        if (!file_exists($staticConfigFile)) {
            throw new Exception('Error while reading configuration file: static cache is missing -- run the installer to create it '.$staticConfigFile);
        }

        if (file_exists($cacheFile)) {
            $t = filemtime($cacheFile);
            $lvc = App::varConfigPath('liveconfig.ini.php');
            if ( filemtime($staticConfigFile) < $t
                && (file_exists($lvc) && filemtime($lvc) < $t)
            ) {
                // let's read the cache file
                if (BYTECODE_CACHE_EXISTS) {
                    $config = include $cacheFile;
                    $config = (object) $config;
                } else {
                    $config = \Jelix\IniFile\Util::read($cacheFile, true);
                }
                return $config;
            }
        }

        self::checkEnvironment();

        if (BYTECODE_CACHE_EXISTS) {
            $config = include $staticConfigFile;
            $config = (object) $config;
        } else {
            $config = \Jelix\IniFile\Util::read($staticConfigFile, true);
        }

        $compiler = new Compiler($configFile);

        $config = $compiler->readLiveConfiguration($config);
        \jFile::createDir(App::tempPath(), $config->chmodDir);

        // if bytecode cache is enabled, it's better to store configuration
        // as PHP code, reading performance are much better than reading
        // an ini file (266 times less). However, if bytecode cache is disabled,
        // reading performance are better with ini : 32% better. Json is only 22% better.
        if (BYTECODE_CACHE_EXISTS) {
            if ($f = @fopen($cacheFile, 'wb')) {
                fwrite($f, '<?php return '.var_export(get_object_vars($config), true).";\n?>");
                fclose($f);
                chmod($cacheFile, $config->chmodFile);
            } else {
                throw new Exception('Error while writing configuration cache file -- '.$cacheFile);
            }
        } else {
            IniFileMgr::write(get_object_vars($config), $cacheFile.'.resultini.php', ";<?php die('');?>\n", $config->chmodFile);
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
        $compiler = new Compiler($configFile, $scriptName);
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
        $compiler = new Compiler($configFile, $scriptName);
        return $compiler->read(true);
    }

    public static function checkEnvironment()
    {
        $tempPath = App::tempBasePath();

        if ($tempPath == '/') {
            // if it equals to '/', this is because realpath has returned false in the application.init.php
            // so this is because the path doesn't exist.
            throw new Exception('Application temp directory doesn\'t exist !', 3);
        }

        if (!is_writable($tempPath)) {
            throw new Exception('Application temp base directory is not writable -- ('.$tempPath.')', 4);
        }

        if (!is_writable(App::logPath())) {
            throw new Exception('Application log directory is not writable -- ('.App::logPath().')', 4);
        }
    }

    public static function getDefaultConfigFile()
    {
        return __DIR__.'/defaultconfig.ini.php';
    }
}
