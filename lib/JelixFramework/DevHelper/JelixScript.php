<?php
/**
 * @author     Laurent Jouanneau
 * @copyright  2011-2016 Laurent Jouanneau
 *
 * @see       http://jelix.org
 * @licence    http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */

namespace Jelix\DevHelper;

class JelixScript
{
    public static $debugMode = false;

    /**
     * load the configuration of jelix-scripts.
     *
     * @param string $appname the application name. leave empty to load it from
     *                        the project file
     *
     * @return CommandConfig
     */
    public static function loadConfig($appname = '')
    {
        $config = new CommandConfig();

        if ($appname === '') {
            $appname = $config->loadFromProject(\jApp::appPath('project.xml'));
        } elseif ($appname === false) {
            // don't load from project..
            $appname = '';
        } else {
            $config->appName = $appname;
        }

        // try to find a .jelix-scripts.ini in the current directory or parent directories
        $dir = getcwd();
        $found = false;
        do {
            if (file_exists($dir.DIRECTORY_SEPARATOR.'.jelix-scripts.ini')) {
                $config->loadFromIni($dir.DIRECTORY_SEPARATOR.'.jelix-scripts.ini', $appname);
                $found = true;
            } elseif (file_exists($dir.DIRECTORY_SEPARATOR.'jelix-scripts.ini')) {
                $config->loadFromIni($dir.DIRECTORY_SEPARATOR.'jelix-scripts.ini', $appname); // windows users don't often use dot files.
                $found = true;
            }
            $previousdir = $dir;
            $dir = dirname($dir);
        } while ($dir != '.' && $dir != $previousdir && !$found);

        // we didn't find a .jelix-scripts, try to read one from the home directory
        if (!$found) {
            $home = '';
            if (isset($_SERVER['HOME'])) {
                $home = $_SERVER['HOME'];
            } elseif (isset($_ENV['HOME'])) {
                $home = $_ENV['HOME'];
            } elseif (isset($_SERVER['USERPROFILE'])) { // windows
                $home = $_SERVER['USERPROFILE'];
            } elseif (isset($_SERVER['HOMEDRIVE'], $_SERVER['HOMEPATH'])) { // windows
                $home = $_SERVER['HOMEDRIVE'].$_SERVER['HOMEPATH'];
            }

            if ($home) {
                if (file_exists($home.DIRECTORY_SEPARATOR.'.jelix-scripts.ini')) {
                    $config->loadFromIni($home.DIRECTORY_SEPARATOR.'.jelix-scripts.ini', $appname);
                } else {
                    $config->loadFromIni($home.DIRECTORY_SEPARATOR.'jelix-scripts.ini', $appname);
                } // windows users don't often use dot files.
            }
        }

        self::$debugMode = $config->debugMode;

        if (function_exists('date_default_timezone_set')) {
            date_default_timezone_set($config->infoTimezone);
        }

        return $config;
    }
}
