<?php

/**
* @package     jelix-scripts
* @author      Jouanneau Laurent
* @contributor
* @copyright   2008 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
*/
require_once (JELIXS_LIB_PATH.'jelix/installer/jIInstallReporter.iface.php');

class installappReporter implements jIInstallReporter {

    public $hasError = false;
    public $hasWarning = false;

    function error($string){
        $this->hasError=true;
        echo "[ERROR] $string \n";
    }

    function warning($string){
        $this->hasWarning=true;
        echo "[WARNING] $string \n";
    }

    function notice($string){
        echo "[NOTICE] $string \n";
    }

    function message($string){
        echo $string."\n";
    }
}


class installappCommand extends JelixScriptCommand {

    public  $name = 'installapp';
    public  $allowed_options=array();
    public  $allowed_parameters=array();

    public  $syntaxhelp = "";
    public  $help='';

    function __construct(){
        $this->help= array(
            'fr'=>"
    Installe une application. EXPERIMENTAL ! En cours de développement.
    ",
            'en'=>"
    Install a new application. EXPERIMENTAL ! Still in development
    ",
    );
    }

    public function run(){
        $installFile = JELIX_APP_PATH.'install/installer.php';
        echo "EXPERIMENTAL ! Still in development !\n";
        if (!file_exists($installFile)) {
            echo "No install script.\nDone.\n";
            return;
        }

        jxs_init_jelix_env();
        include($installFile);

        if (!class_exists('appInstaller',true)) {
            echo "No appInstaller class in install script.\nDone.\n";
            return;
        }

        //@TODO: show information found in project.xml
        //@TODO: check the jelix version according to project.xml
        //@TODO: put rights on temp directory (and create directory ?)

        $reporter = new installappReporter;
        $installer = new appInstaller($reporter, JELIX_APP_PATH.'install/');
        $installer->install();

        if($reporter->hasError)
            echo "\nEnded with errors.\n";
        else if($reporter->hasWarning)
            echo "\nEnded with warning.\n";
        else
            echo "\nSuccessful install.\n";
    }
}

?>