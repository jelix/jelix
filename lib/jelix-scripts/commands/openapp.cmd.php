<?php
/**
* @package     jelix-scripts
* @author      Laurent Jouanneau
* @copyright   2010 Laurent Jouanneau
* @link        http://jelix.org
* @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
*/

class openappCommand extends JelixScriptCommand {

    public  $name = 'openapp';
    public  $allowed_options = array();
    public  $allowed_parameters = array();

    public  $syntaxhelp = "";
    public  $help = '';

    function __construct(){
        $this->help= array(
            'fr'=>"
    Active l'application
    ",
            'en'=>"
    Activate the application
    ",
    );
    }

    public function run(){
        jAppManager::open();
    }
}