<?php
/**
 * @author      Laurent Jouanneau
 * @copyright   2018 Laurent Jouanneau
 *
 * @see        http://www.jelix.org
 * @licence     MIT
 */

namespace Jelix\Scripts;

use Jelix\Core\Server;
use Jelix\Core\App;

class Utils
{
    public static function checkEnv()
    {
        if (!Server::isCLI()) {
            echo "Error: you're not allowed to execute this script outside a command line shell.\n";

            exit(1);
        }

        if (!App::isInit()) {
            echo "Error: should run within an application\n";

            exit(1);
        }
    }

    public static function checkTempPath()
    {
        $tempBasePath = App::tempBasePath();

        // we always clean the temp directory. But first, let's check the temp path (see ticket #840)...

        if ($tempBasePath == DIRECTORY_SEPARATOR || $tempBasePath == '' || $tempBasePath == '/') {
            throw new \Exception("Error: bad path in App::tempBasePath(), it is equals to '".$tempBasePath."' !!\n".
                "       Jelix cannot clear the content of the temp directory.\n".
                "       Correct the path for the temp directory or create the directory you\n".
                "       indicated with App in your application.init.php.\n");
        }
        //\jFile::removeDir(App::tempPath(), false, array('.svn', '.dummy', '.empty'));
    }
}
