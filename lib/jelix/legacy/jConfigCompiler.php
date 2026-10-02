<?php
/**
 * @package      jelix
 * @subpackage   core
 *
 * @author       Laurent Jouanneau
 * @contributor  Thibault Piront (nuKs), Christophe Thiriot, Philippe Schelté
 *
 * @copyright    2006-2025 Laurent Jouanneau
 * @copyright    2007 Thibault Piront, 2008 Christophe Thiriot, 2008 Philippe Schelté
 *
 * @see         http://www.jelix.org
 * @licence      GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

use Jelix\Core\Config\Compiler;

/**
 * jConfigCompiler merge two ini file in a single array and store it in a temporary file
 * This is a static class.
 *
 * @package  jelix
 * @subpackage core
 * @static
 * @deprecated use Jelix\Core\Config\Compiler instead.
 */
class jConfigCompiler
{
    private function __construct()
    {
    }

    /**
     * read the given ini file, for the current entry point, or for the entrypoint given
     * in $pseudoScriptName. Merge it with the content of other config files
     * It also calculates some options.
     * If you are in a CLI script but you want to load a configuration file for a web entry point
     * or vice-versa, you need to indicate the $pseudoScriptName parameter with the name of the entry point.
     *
     * Merge of configuration files are made in this order:
     * - core/defaultconfig.ini.php
     * - app/system/mainconfig.ini.php
     * - app/system/$configFile
     * - var/config/localconfig.ini.php
     * - var/config/$configFile
     * - var/config/liveconfig.ini.php
     *
     * @param string $configFile       the config file name
     * @param bool   $allModuleInfo    may be true for the installer, which needs all informations
     *                                 else should be false, these extra informations are
     *                                 not needed to run the application
     * @param bool   $isCli            indicate if the configuration to read is for a CLI script or no
     * @param string $pseudoScriptName the name of the entry point, relative to the base path,
     *                                 corresponding to the readed configuration
     *
     * @throws Exception
     *
     * @return object an object which contains configuration values
     */
    public static function read($configFile, $allModuleInfo = false, $isCli = false, $pseudoScriptName = '')
    {

        $compiler = new Compiler(jApp::app(), $configFile, $pseudoScriptName);
        if ($isCli) {
            $config = $compiler->readForCli($allModuleInfo);
        }
        else {
            $config = $compiler->read($allModuleInfo);
        }

        return $config;
    }

    /**
     * Identical to read(), but also stores the result in a temporary file.
     *
     * @param string $configFile       the config file name
     * @param bool   $isCli
     * @param string $pseudoScriptName
     *
     * @throws Exception
     *
     * @return object an object which contains configuration values
     */
    public static function readAndCache($configFile, $isCli = null, $pseudoScriptName = '')
    {
        if ($isCli === null) {
            $isCli = jServer::isCLI();
        }

        $config = self::read($configFile, false, $isCli, $pseudoScriptName);
        jFile::createDir(jApp::tempPath(), $config->chmodDir);
        $filename = self::getCacheFilename($configFile);

        // if bytecode cache is enabled, it's better to store configuration
        // as PHP code, reading performance are much better than reading
        // an ini file (266 times less). However, if bytecode cache is disabled,
        // reading performance are better with ini : 32% better. Json is only 22% better.
        if (BYTECODE_CACHE_EXISTS) {
            if ($f = @fopen($filename, 'wb')) {
                fwrite($f, '<?php $config = '.var_export(get_object_vars($config), true).";\n?>");
                fclose($f);
                chmod($filename, $config->chmodFile);
            } else {
                throw new Exception('Error while writing configuration cache file -- '.$filename);
            }
        } else {
            \Jelix\IniFile\Util::write(get_object_vars($config), $filename, ";<?php die('');?>\n", $config->chmodFile);
        }

        return $config;
    }

    /**
     * return the path of file where to store the cache of the configuration.
     *
     * @param string $configFile the name of the configuration file of the entry
     *                           point into var/config/
     *
     * @return string the full path of the cache
     *
     * @deprecated use AppConfig::getCacheFilename() instead
     */
    public static function getCacheFilename($configFile)
    {
        $filename = jApp::tempPath().str_replace('/','~',$configFile);
        list($domain, $port) = jServer::getDomainPortFromServer();
        if ($domain) {
            $filename .= '.'.$domain.'-'.$port;
        }
        if (BYTECODE_CACHE_EXISTS) {
            $filename .= '.conf.legacy.php';
        } else {
            $filename .= '.resultini.legacy.php';
        }

        return $filename;
    }

    /**
     * @param string $ext
     * @param bool $isCli indicate if it is for a CLI command
     * @return string
     * @throws Exception
     * @deprecated use jServer::findServerName instead
     */
    public static function findServerName($ext = '.php', $isCli = false)
    {
        if ($isCli) {
            return 'SCRIPT_NAME';
        }
        return jServer::findServerName($ext);
    }
}
