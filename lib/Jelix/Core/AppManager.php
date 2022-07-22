<?php
/**
 * @author     Loic Mathaud
 * @contributor Laurent Jouanneau
 *
 * @copyright  2006 Loic Mathaud, 2010-2022 Laurent Jouanneau
 *
 * @see        http://www.jelix.org
 * @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */

namespace Jelix\Core;

use Jelix\Core\Config\Compiler;

/**
 * utilities to manage a jelix application.
 *
 * @static
 */
class AppManager
{
    private function __construct()
    {
    }

    /**
     * close the application, by creating a CLOSED file.
     *
     * @param string $message the message to display
     *
     * @since 1.2
     */
    public static function close($message = '')
    {
        $file = App::varConfigPath('CLOSED');
        file_put_contents($file, $message);
        if (App::config()) {
            chmod($file, App::config()->chmodFile);
        }
    }

    /**
     * open the application.
     *
     * @since 1.2
     */
    public static function open()
    {
        if (file_exists(App::varConfigPath('CLOSED'))) {
            unlink(App::varConfigPath('CLOSED'));
        }
    }

    /**
     * tell if the application is opened.
     *
     * @return bool true if the application is opened
     *
     * @since 1.2
     */
    public static function isOpened()
    {
        return !file_exists(App::varConfigPath('CLOSED'));
    }

    public static function clearTemp($path = '')
    {
        if ($path == '') {
            $path = App::tempBasePath();
            if ($path == '') {
                throw new \Exception('default temp base path is not defined', 1);
            }
        }

        if ($path == DIRECTORY_SEPARATOR || $path == '' || $path == '/') {
            throw new \Exception('given temp path is invalid', 2);
        }
        if (!file_exists($path)) {
            throw new \Exception('given temp path does not exists', 3);
        }

        if (!is_writeable($path)) {
            throw new \Exception('given temp path does not exists', 4);
        }

        // do not erase .empty or .dummy files that are into the temp directory
        // for source code repositories
        \jFile::removeDir($path, false, array('.dummy', '.empty', '.svn'));
    }

    /**
     * check if the application is opened. If not, it displays the yourapp/install/closed.html
     * file with an http error (or Jelix/Core/closed.html), and exit.
     * This function should be called in all entry point, before the creation of the coordinator.
     *
     * @see \Jelix\Core\AppManager
     */
    public static function errorIfAppClosed()
    {
        if (!App::isInit()) {
            if (!\jServer::isCLI()) {
                header('HTTP/1.1 500 Internal Server Error');
                header('Content-type: text/html');
            }
            echo 'Jelix App is not initialized!';
            exit(1);
        }
        if (!self::isOpened()) {
            $message = file_get_contents(App::varConfigPath('CLOSED'));

            if (\jServer::isCLI()) {
                echo 'Application closed.'.($message ? "\n{$message}\n" : "\n");
                exit(1);
            }

            // note: we are not supposed to have the configuration loaded here
            // so we cannot use the selected theme or any other configuration parameters
            // like calculated basePath. We mimic what it is done into the configuration compiler
            $basePath = App::urlBasePath();
            if ($basePath == null) {
                try {
                    $urlScript = $_SERVER[Compiler::findServerName()];
                    $basePath = substr($urlScript, 0, strrpos($urlScript, '/')) . '/';
                } catch (\Exception $e) {
                    $basePath = '/';
                }
                $themePath = 'themes/default/';
            }
            else {
                $themePath = 'themes/'.App::config()->theme.'/';
            }

            // html file installed for the current instance of the application
            if (file_exists(App::varPath($themePath.'closed.html'))) {
                $file = App::varPath($themePath.'closed.html');
            }
            else if (file_exists(App::varPath('themes/closed.html'))) {
                $file = App::varPath('themes/closed.html');
            }
            // html file provided by the application
            elseif (file_exists(App::appPath('install/closed.html'))) {
                $file = App::appPath('install/closed.html');
            }
            // default html file
            else {
                $file = __DIR__.'/closed.html';
            }

            header('HTTP/1.1 503 Application not available');
            header('Content-type: text/html');
            echo str_replace(array(
                '%message%',
                '%basePath%',
                '%themePath%',
            ), array(
                $message,
                $basePath,
                $themePath
            ), file_get_contents($file));

            exit(1);
        }
    }

    /**
     * check if the application is NOT installed. If the app is installed, an
     * error message appears and the scripts ends.
     * It should be called only by some scripts
     * like an installation wizard, not by an entry point.
     */
    public static function errorIfAppInstalled()
    {
        if (self::isAppInstalled()) {
            if (\jServer::isCLI()) {
                echo "Application is installed. The script cannot be runned.\n";
            } else {
                header('HTTP/1.1 500 Internal Server Error');
                header('Content-type: text/plain');
                echo "Application is installed. The script cannot be runned.\n";
            }
            exit(1);
        }
    }

    /**
     * @return bool true if the application is installed
     */
    public static function isAppInstalled()
    {
        return file_exists(App::varConfigPath('installer.ini.php'));
    }
}
