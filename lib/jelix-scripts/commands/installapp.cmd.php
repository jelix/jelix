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
require_once (JELIXS_LIB_PATH.'jelix/installer/jInstaller.class.php');



class installappCommand extends JelixScriptCommand {

    public  $name = 'installapp';
    public  $allowed_options=array();
    public  $allowed_parameters=array();

    public  $syntaxhelp = "";
    public  $help='';

    function __construct(){
        $this->help= array(
            'fr'=>"
    Installe une application. EXPERIMENTAL !
    ",
            'en'=>"
    Install a new application. EXPERIMENTAL !
    ",
    );
    }

    public function run(){
        $f = JELIX_APP_CONFIG_PATH.'installer.ini.php';
        if(!file_exists($f))
            file_put_contents($f, ';<?php die(); ?>');

        jxs_init_jelix_env();
        $installer = new jInstaller(new textInstallReporter());
        $installer->installApplication();
    }
}