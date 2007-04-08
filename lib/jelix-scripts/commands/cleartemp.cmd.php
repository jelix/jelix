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
            jAppManager::clearTemp();
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


?>
