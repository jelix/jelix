<?php

/**
* @package     jelix-scripts
* @author      Laurent Jouanneau
* @contributor Julien Issler
* @copyright   2008-2009 Laurent Jouanneau
* @copyright   2009 Julien Issler
* @link        http://jelix.org
* @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
*/

class installappCommand extends JelixScriptCommand {

    public  $name = 'installapp';
    public  $allowed_options=array();
    public  $allowed_parameters=array();

    public  $applicationMustExist = false;

    public  $syntaxhelp = "";
    public  $help = '';

    function __construct(){
        $this->help= array(
            'fr'=>"
    Installe ou met à jour tout les modules d'une application.
    ",
            'en'=>"
    Install or upgrade all modules of an application.
    ",
    );
    }

    public function run(){
        require_once (JELIXS_LIB_PATH.'jelix/installer/jInstaller.class.php');

        $installer = new jInstaller(new textInstallReporter());
        $installer->installApplication();
    }
}