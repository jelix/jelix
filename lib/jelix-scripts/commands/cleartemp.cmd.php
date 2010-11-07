<?php
/**
* @package     jelix-scripts
* @author      Christophe Thiriot
* @contributor Loic Mathaud
* @contributor Laurent Jouanneau
* @copyright   2006 Christophe Thiriot, 2007 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
*/


class cleartempCommand extends JelixScriptCommand {

    public  $name = 'cleartemp';
    public  $allowed_options=array();
    public  $allowed_parameters=array();

    public  $syntaxhelp = "";
    public  $help=array(
            'fr'=>"
    Vide le cache.",
            'en'=>"
    Delete cache files.",
            );


    public function run(){
        try {
            if (!defined('JELIX_APP_TEMP_PATH')) {
                echo "Error: JELIX_APP_TEMP_PATH is not defined\n";
                exit(1);
            }
            if (JELIX_APP_TEMP_PATH == DIRECTORY_SEPARATOR || JELIX_APP_TEMP_PATH == '' || JELIX_APP_TEMP_PATH == '/') {
                echo "Error: bad path in JELIX_APP_TEMP_PATH, it is equals to '".JELIX_APP_TEMP_PATH."' !!\n";
                echo "       Jelix cannot clear the content of the temp directory.\n";
                echo "       Correct the path in JELIX_APP_TEMP_PATH or create the directory you\n";
                echo "       indicated into JELIX_APP_TEMP_PATH.\n";
                exit(1);
            }
            jFile::removeDir(JELIX_APP_TEMP_PATH, false);


            if (!defined('JELIX_APP_REAL_TEMP_PATH')) {
                echo "Error: JELIX_APP_REAL_TEMP_PATH is not defined\n";
                exit(1);
            }
            if (JELIX_APP_REAL_TEMP_PATH == DIRECTORY_SEPARATOR || JELIX_APP_REAL_TEMP_PATH == '' || JELIX_APP_REAL_TEMP_PATH == '/') {
                echo "Error: bad path in JELIX_APP_REAL_TEMP_PATH, it is equals to '".JELIX_APP_REAL_TEMP_PATH."' !!\n";
                echo "       Jelix cannot clear the content of the temp directory.\n";
                echo "       Correct the path in JELIX_APP_REAL_TEMP_PATH or create the directory you\n";
                echo "       indicated into JELIX_APP_REAL_TEMP_PATH.\n";
                exit(1);
            }
            jFile::removeDir(JELIX_APP_REAL_TEMP_PATH, false);


            if (defined('JELIX_APP_TEMP_CLI_PATH')){
                if (JELIX_APP_TEMP_CLI_PATH == DIRECTORY_SEPARATOR || JELIX_APP_TEMP_CLI_PATH == '' || JELIX_APP_TEMP_CLI_PATH == '/') {
                    echo "Error: bad path in JELIX_APP_TEMP_CLI_PATH, it is equals to '".JELIX_APP_TEMP_CLI_PATH."' !!\n";
                    echo "       Jelix cannot clear the content of the temp directory.\n";
                    echo "       Correct the path in JELIX_APP_TEMP_CLI_PATH or create the directory you\n";
                    echo "       indicated into JELIX_APP_TEMP_CLI_PATH.\n";
                    exit(1);
                }
                jFile::removeDir(JELIX_APP_TEMP_CLI_PATH, false);
            }
        }
        catch (Exception $e) {
            if(MESSAGE_LANG == 'fr')
               echo "Un ou plusieurs répertoires n'ont pas pu être supprimés.\n" .
                    "Message d'erreur : " . $e->getMessage()."\n";
            else
               echo "One or more directories couldn't be deleted.\n" .
                    "Error: " . $e->getMessage()."\n";
        }
    }
}
