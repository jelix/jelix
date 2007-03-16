<?php
/**
* @package     jelix-scripts
* @author      Thiriot Christophe
* @contributor Loic Mathaud
* @copyright   2006 Thiriot Christophe
* @link        http://www.jelix.org
* @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
*/


class cleartempCommand extends JelixScriptCommand {

    public  $name = 'cleartemp';
    public  $allowed_options=array();
    public  $allowed_parameters=array();

    public  $syntaxhelp = "";
    public  $help="
    Vide le cache.";


    public function run(){
        try {
            jAppManager::clearTemp();
        }
        catch (Exception $e) {
        	echo "Un ou plusieurs répertoires n'ont pas pu être supprimés.\n" .
                    "Message d'erreur :" . $e->getMessage()."\n";
        }
    }
}


?>
