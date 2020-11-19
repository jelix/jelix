<?php
/**
 * @author      Laurent Jouanneau
 * @copyright   2018 Laurent Jouanneau
 *
 * @see        http://www.jelix.org
 * @licence     MIT
 */

namespace Jelix\Scripts;

class Utils
{
    public static function checkEnv()
    {
        if (!\jServer::isCLI()) {
            echo "Error: you're not allowed to execute this script outside a command line shell.\n";
            exit(1);
        }

        if (!\jApp::isInit()) {
            echo "Error: should run within an application\n";
            exit(1);
        }
    }

    public static function checkTempPath()
    {
        $tempBasePath = \jApp::tempBasePath();

        // we always clean the temp directory. But first, let's check the temp path (see ticket #840)...

        if ($tempBasePath == DIRECTORY_SEPARATOR || $tempBasePath == '' || $tempBasePath == '/') {
            throw new \Exception("Error: bad path in jApp::tempBasePath(), it is equals to '".$tempBasePath."' !!\n".
                "       Jelix cannot clear the content of the temp directory.\n".
                "       Correct the path for the temp directory or create the directory you\n".
                "       indicated with jApp in your application.init.php.\n");
        }
        //\jFile::removeDir(\jApp::tempPath(), false, array('.svn', '.dummy', '.empty'));
    }
}
