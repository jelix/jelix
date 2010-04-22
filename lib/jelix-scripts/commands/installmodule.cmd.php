<?php
/**
* @package     jelix-scripts
* @author      Laurent Jouanneau
* @copyright   2009 Laurent Jouanneau
* @link        http://jelix.org
* @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
*/

class installmoduleCommand extends JelixScriptCommand {

    public  $name = 'installmodule';
    public  $allowed_options=array('-v'=>false);
    public  $allowed_parameters=array('module'=>true,'...'=>false);

    public  $applicationMustExist = false;

    public  $syntaxhelp = "[-v] MODULE [MODULE [....]]";
    public  $help = '';

    function __construct(){
        $this->help= array(
            'fr'=>"
    Installe ou met à jour les modules indiqués, même si ils ne sont pas activés.
    Si un point d'entrée est indiqué, le module ne sera installé que pour
    ce point d'entrée.

    Option -v : mode verbeux.
    ",
            'en'=>"
    Install or upgrade given modules even if there are not activated.
    if an entry point is indicated, the module is installed only for this
    entry point.

    Option -v: verbose mode.
    ",
    );
    }

    public function run(){
        require_once (JELIXS_LIB_PATH.'jelix/installer/jInstaller.class.php');

        jAppManager::close();

        $module = $this->getParam('module');
        $modulesList = $this->getParam('...', array());
        array_unshift($modulesList, $module);

        global $entryPointName, $entryPointId, $allEntryPoint;

        if ($this->getOption("-v"))
            $reporter = new textInstallReporter();
        else
            $reporter = new ghostInstallReporter();

        $installer = new jInstaller($reporter);

        if ($allEntryPoint) {
            $installer->installModules($modulesList);
        }
        else {
            $installer->installModules($modulesList, $entryPointName);
        }

        jAppManager::clearTemp(JELIX_APP_REAL_TEMP_PATH);
        jAppManager::open();
    }
}