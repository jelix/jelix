<?php
/**
* @package     jelix-scripts
* @author      Laurent Jouanneau
* @contributor Loic Mathaud
* @contributor Bastien Jaillot
* @copyright   2005-2009 Laurent Jouanneau, 2007 Loic Mathaud, 2008 Bastien Jaillot
* @link        http://jelix.org
* @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
*/


class createmoduleCommand extends JelixScriptCommand {

    public  $name = 'createmodule';
    public  $allowed_options=array('-nosubdir'=>false, '-nocontroller'=>false, '-cmdline'=>false, '-addinstallzone'=>false, '-defaultmodule'=>false);
    public  $allowed_parameters=array('module'=>true);

    public  $syntaxhelp = "[-nosubdir] [-nocontroller] [-cmdline] [-addinstallzone] [-defaultmodule] MODULE";
    public  $help=array(
        'fr'=>"
    Crée un nouveau module, avec son fichier module.xml, et un contrôleur
    par défaut, ainsi que tous les sous-répertoires courants
    (zones, templates, daos, locales, classes...).

    -nosubdir (facultatif) : ne crée pas tous les sous-repertoires courants
    -nocontroller (facultatif) : ne crée pas de fichier contrôleur par défaut
    -cmdline (facultatif) : crée le module avec un contrôleur pour la ligne de commande
    -addinstallzone (facultatif) : ajoute la zone check_install pour une nouvelle application
    -defaultmodule (facultatif) : le module devient le module par defaut de l'application

    MODULE : le nom du module à créer.",
        'en'=>"
    Create a new module, with all necessary files and sub-directories.

    -nosubdir (optional): don't create sub-directories.
    -nocontroller (optional): don't create a default controller.
    -cmdline (optional): create a controller for command line (jControllerCmdLine)
    -addinstallzone (optional) : add the check_install zone for new application
    -defaultmodule (optional) : the new module become the default module
    MODULE: name of the new module."
    );


    public function run(){
        jxs_init_jelix_env();

        $module = $this->_parameters['module'];
        $initialVersion = '0.1pre';

        // note: since module name are used for name of generated name,
        // only this characters are allowed
        if(preg_match('/([^a-zA-Z_0-9])/', $module)) {
            throw new Exception("'".$module."' is not a valid name for a module");
        }

        $path= $this->getModulePath($module, false);

        if (file_exists($path)) {
            throw new Exception("module '".$module."' already exists");
        }
        $this->createDir($path);
        
        $param = array();
        $param['name'] = $module;
        $param['default_id'] = $module.JELIXS_INFO_DEFAULT_IDSUFFIX;
        $param['version'] = $initialVersion;

        $this->createFile($path.'module.xml','module/module.xml.tpl',$param);

        // create all sub directories of a module
        if(!$this->getOption('-nosubdir')){
            $this->createDir($path.'classes/');
            $this->createDir($path.'zones/');
            $this->createDir($path.'controllers/');
            $this->createDir($path.'templates/');
            $this->createDir($path.'classes/');
            $this->createDir($path.'daos/');
            $this->createDir($path.'forms/');
            $this->createDir($path.'locales/');
            $this->createDir($path.'locales/en_EN/');
            $this->createDir($path.'locales/fr_FR/');
        }

        // create a default controller
        if(!$this->getOption('-nocontroller')){
            $agcommand = jxs_load_command('createctrl');
            $options = array();
            if ($this->getOption('-cmdline')) {
               $options = array('-cmdline'=>true);
            }
            if ($this->getOption('-addinstallzone')) {
                $options = array('-addinstallzone'=>true);
            }
            $agcommand->init($options,array('module'=>$module, 'name'=>'default','method'=>'index'));
            $agcommand->run();
        }

        $isdefault = $this->getOption('-defaultmodule');
        global $entryPointName, $entryPointId, $allEntryPoint;

        $ini = new jIniFileModifier(JELIX_APP_CONFIG_PATH.'defaultconfig.ini.php');
        if ($isdefault) {
            $ini->setValue('startModule', $module);
            $ini->setValue('startAction', 'default:index');
        }
        $ini->setValue($module.'.access', 2 , 'modules');
        $ini->save();

        $list = $this->getEntryPointsList();
        $install = new jIniFileModifier(JELIX_APP_CONFIG_PATH.'installer.ini.php');
        
        foreach ($list as $k => $entryPoint) {
            
            if ($allEntryPoint || $entryPoint['file'] == $entryPointName) {
                $install->setValue($module.'.installed', 1, $entryPoint['id']);
                $install->setValue($module.'.version', $initialVersion, $entryPoint['id']);
            }

            if ($isdefault) {
                // we set the module as default module for one or all entry points.

                $filename = JELIX_APP_CONFIG_PATH.$entryPoint['config'];
    
                // we set the startModule option for all entry points except
                // if an entry point is indicated on the command line
                if (file_exists($filename) &&
                    ($allEntryPoint || $entryPoint['file'] == $entryPointName)) {
                    $ini = new jIniFileModifier($filename);
                    if ($ini->getValue('startModule') != '') {
                        $ini->setValue('startModule', $module);
                        $ini->setValue('startAction', 'default:index');
                    }
                    $ini->save();
                }
            }
        }

        $install->save();
    }
}

