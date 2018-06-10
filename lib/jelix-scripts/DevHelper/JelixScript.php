<?php
/**
* @package    jelix-scripts
* @author     Laurent Jouanneau
* @copyright  2011-2016 Laurent Jouanneau
* @link       http://jelix.org
* @licence    http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*/
namespace Jelix\DevHelper;

class JelixScript {

    static $debugMode = false;

    /**
     * load the configuration of jelix-scripts
     * @param string $appname the application name
     * @return Jelix\DevHelper\CommandConfig
     */
    static function loadConfig($appname='') {
        $config = new CommandConfig();

        if ($appname === '')
            $appname = $config->loadFromProject();
        else if ($appname === false) // don't load from project..
            $appname = '';

        // try to find a .jelix-scripts.ini in the current directory or parent directories
        $dir = getcwd();
        $found = false;
        do {
            if (file_exists($dir.DIRECTORY_SEPARATOR.'.jelix-scripts.ini')) {
                $config->loadFromIni($dir.DIRECTORY_SEPARATOR.'.jelix-scripts.ini', $appname);
                $found = true;
            }
            else if (file_exists($dir.DIRECTORY_SEPARATOR.'jelix-scripts.ini')) {
                $config->loadFromIni($dir.DIRECTORY_SEPARATOR.'jelix-scripts.ini', $appname); // windows users don't often use dot files.
                $found = true;
            }
            $previousdir = $dir;
            $dir = dirname($dir);
        }
        while($dir != '.' && $dir != $previousdir && !$found);

        // we didn't find a .jelix-scripts, try to read one from the home directory
        if (!$found) {
            $home = '';
            if (isset($_SERVER['HOME'])) {
                $home = $_SERVER['HOME'];
            }
            else if (isset($_ENV['HOME'])) {
                $home = $_ENV['HOME'];
            }
            else if (isset($_SERVER['USERPROFILE'])) { // windows
                $home = $_SERVER['USERPROFILE'];
            }
            else if (isset($_SERVER['HOMEDRIVE']) && isset($_SERVER['HOMEPATH'])) { // windows
                $home = $_SERVER['HOMEDRIVE'].$_SERVER['HOMEPATH'];
            }

            if ($home) {
                if (file_exists($home.DIRECTORY_SEPARATOR.'.jelix-scripts.ini'))
                    $config->loadFromIni($home.DIRECTORY_SEPARATOR.'.jelix-scripts.ini', $appname);
                else
                    $config->loadFromIni($home.DIRECTORY_SEPARATOR.'jelix-scripts.ini', $appname); // windows users don't often use dot files.
            }
        }

        self::$debugMode = $config->debugMode;

        if (function_exists('date_default_timezone_set')){
           date_default_timezone_set($config->infoTimezone);
        }

        $config->appName = $appname;
        return $config;
    }

    static function checkTempPath() {
        $tempBasePath = \jApp::tempBasePath();

        // we always clean the temp directory. But first, let's check the temp path (see ticket #840)...

        if ($tempBasePath == DIRECTORY_SEPARATOR || $tempBasePath == '' || $tempBasePath == '/') {
            throw new \Exception("Error: bad path in jApp::tempBasePath(), it is equals to '".$tempBasePath."' !!\n".
                                "       Jelix cannot clear the content of the temp directory.\n".
                                "       Correct the path for the temp directory or create the directory you\n".
                                "       indicated with jApp in your application.init.php.\n");
        }
        \jFile::removeDir(\jApp::tempPath(), false, array('.svn', '.dummy', '.empty'));
    }
}
