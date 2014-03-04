<?php

/**
* @package     jelix-scripts
* @author      Laurent Jouanneau
* @contributor Julien Issler
* @copyright   2008-2011 Laurent Jouanneau
* @copyright   2009 Julien Issler
* @link        http://jelix.org
* @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
*/

class installappCommand extends JelixScriptCommand {

    public  $name = 'installapp';
    public  $allowed_options = array();
    public  $allowed_parameters = array();

    public  $syntaxhelp = "";
    public  $help = '';

    function __construct($config){
        $this->help= array(
            'fr'=>"
    Installe ou met à jour tous les modules d'une application qui sont activés.
",
            'en'=>"
    Install or upgrade all activated modules of an application.
",
    );
        parent::__construct($config);
    }

    public function run(){
        require_once (JELIX_LIB_PATH.'installer/jInstaller.class.php');

        \Jelix\Core\AppManager::close();
        if ($this->verbose())
            $reporter = new textInstallReporter();
        else
            $reporter = new textInstallReporter('error');

        $installer = new jInstaller($reporter);

        $installer->installApplication();
        try {
            \Jelix\Core\AppManager::clearTemp(\Jelix\Core\App::tempBasePath());
        }
        catch(Exception $e) {
            if ($e->getCode() == 2) {
                echo "Error: bad path in use \\Jelix\\Core\\App::tempBasePath(), it is equals to '".\Jelix\Core\App::tempBasePath()."' !!\n";
                echo "       Jelix cannot clear the content of the temp directory.\n";
                echo "       you must clear it your self.\n";
                echo "       Correct the path in the application.init.php or create the directory\n";
            }
            else echo "Error: ".$e->getMessage();
        }
        \Jelix\Core\AppManager::open();
    }
}
