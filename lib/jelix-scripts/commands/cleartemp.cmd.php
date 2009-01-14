<?php
/**
* @package     jelix-scripts
* @author      Thiriot Christophe
* @contributor Loic Mathaud
* @contributor Laurent Jouanneau
* @copyright   2006 Thiriot Christophe, 2007 Laurent Jouanneau
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
            jFile::removeDir(JELIX_APP_TEMP_PATH, false);
	    jFile::removeDir(JELIX_APP_REAL_TEMP_PATH, false);
	    if (defined('JELIX_APP_TEMP_CLI_PATH'))
		jFile::removeDir(JELIX_APP_TEMP_CLI_PATH, false);
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

