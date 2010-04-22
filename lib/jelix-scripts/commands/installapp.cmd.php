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
    public  $allowed_options = array('-v'=>false);
    public  $allowed_parameters = array();

    public  $applicationMustExist = false;

    public  $syntaxhelp = "[-v]";
    public  $help = '';

    function __construct(){
        $this->help= array(
            'fr'=>"
    Installe ou met à jour tout les modules d'une application qui sont activés.

    Option -v : mode verbeux.
    ",
            'en'=>"
    Install or upgrade all activated modules of an application.

    Option -v: verbose mode.
    ",
    );
    }

    public function run(){
        require_once (JELIXS_LIB_PATH.'jelix/installer/jInstaller.class.php');

        jAppManager::close();
        if ($this->getOption("-v"))
            $reporter = new textInstallReporter();
        else
            $reporter = new ghostInstallReporter();

        $installer = new jInstaller($reporter);

        $installer->installApplication();

        jAppManager::clearTemp(JELIX_APP_REAL_TEMP_PATH);
        jAppManager::open();

    }
}