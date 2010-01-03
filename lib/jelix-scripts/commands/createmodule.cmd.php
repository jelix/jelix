<?php
/**
* @package     jelix-scripts
* @author      Laurent Jouanneau
* @contributor Loic Mathaud
* @contributor Bastien Jaillot
* @copyright   2005-2010 Laurent Jouanneau, 2007 Loic Mathaud, 2008 Bastien Jaillot
* @link        http://jelix.org
* @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
*/


class createmoduleCommand extends JelixScriptCommand {

    public  $name = 'createmodule';
    public  $allowed_options=array('-nosubdir'=>false, '-nocontroller'=>false, '-cmdline'=>false, '-addinstallzone'=>false, '-defaultmodule'=>false);
    public  $allowed_parameters=array('module'=>true, 'repository'=>false);

    public  $syntaxhelp = "[-nosubdir] [-nocontroller] [-cmdline] [-addinstallzone] [-defaultmodule] MODULE [REPOSITORY]";
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

    MODULE : le nom du module à créer.
    REPOSITORY: le depot de modules où créer le module. même syntaxe que pour modulesPath
                dans la configuration. Le dépôt par défaut est app:module/",
        'en'=>"
    Create a new module, with all necessary files and sub-directories.

    -nosubdir (optional): don't create sub-directories.
    -nocontroller (optional): don't create a default controller.
    -cmdline (optional): create a controller for command line (jControllerCmdLine)
    -addinstallzone (optional) : add the check_install zone for new application
    -defaultmodule (optional) : the new module become the default module
    MODULE: name of the new module.
    REPOSITORY: the path of the directory where to create the module. same syntax as modulesPath
                in the configuration. default repository is app:module/"
    );


    public function run(){
        jxs_init_jelix_env();
        global $entryPointName, $entryPointId, $allEntryPoint, $gJConfig;

        $module = $this->getParam('module');
        $initialVersion = '0.1pre';

        // note: since module name are used for name of generated name,
        // only this characters are allowed
        if ($module == null || preg_match('/([^a-zA-Z_0-9])/', $module)) {
            throw new Exception("'".$module."' is not a valid name for a module");
        }

        // check if the module already exist or not
        $path = '';
        try {
            $path = $this->getModulePath($module);
        }
        catch (Exception $e) {}

        if ($path != '') {
            throw new Exception("module '".$module."' already exists");
        }

        // verify the given repository
        $repository = $this->getParam('repository', 'app:modules/');
        if (substr($repository,-1) != '/')
            $repository .= '/';
        $repositoryPath = str_replace(array('lib:','app:'), array(LIB_PATH, JELIX_APP_PATH), $repository);

        $listRepos = preg_split('/ *, */',$gJConfig->modulesPath);
        $repositoryFound = false;
        foreach($listRepos as $path){
            if(trim($path) == '') continue;
            $p = str_replace(array('lib:','app:'), array(LIB_PATH, JELIX_APP_PATH), $path);
            if (substr($p,-1) != '/')
                $p .= '/';
            if ($p == $repositoryPath) {
                $repositoryFound = true;
                break;
            }
        }

        // the repository doesn't exist in the configuration
        // let's add it into the configuration
        if (!$repositoryFound) {
            if ($allEntryPoint) {
                $ini = new jIniFileModifier(JELIX_APP_CONFIG_PATH.'defaultconfig.ini.php');
            }
            else {
                $list = $this->getEntryPointsList();
                foreach ($list as $k => $entryPoint) {
                    if ($entryPoint['file'] == $entryPointName) {
                        $ini = new jIniFileModifier(JELIX_APP_CONFIG_PATH.$entryPoint['config']);
                        break;
                    }
                }
            }
            if (!$ini) {
                throw new Exception("entry point is unknown");
            }
            $ini->setValue('modulesPath', $gJConfig->modulesPath.','.$repository);
            $ini->save();
            
            $this->createDir($repositoryPath);
        }

        $path = $repositoryPath.$module.'/';
        $this->createDir($path);
        
        $gJConfig = null;

        $param = array();
        $param['module'] = $module;
        $param['default_id'] = $module.JELIXS_INFO_DEFAULT_IDSUFFIX;
        $param['version'] = $initialVersion;

        $this->createFile($path.'module.xml', 'module/module.xml.tpl', $param);

        // create all sub directories of a module
        if (!$this->getOption('-nosubdir')) {
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
            $this->createDir($path.'install/');
            $this->createFile($path.'install/install.php','module/install.tpl',$param);
        }

        $isdefault = $this->getOption('-defaultmodule');

        // activate the module in the application
        $ini = new jIniFileModifier(JELIX_APP_CONFIG_PATH.'defaultconfig.ini.php');
        if ($isdefault) {
            $ini->setValue('startModule', $module);
            $ini->setValue('startAction', 'default:index');
        }
        if ($allEntryPoint)
            $ini->setValue($module.'.access', 2 , 'modules');
        $ini->save();

        $list = $this->getEntryPointsList();
        $install = new jIniFileModifier(JELIX_APP_CONFIG_PATH.'installer.ini.php');

        // install the module for all needed entry points
        foreach ($list as $k => $entryPoint) {

            
            if (!$allEntryPoint || $isdefault) {
                $configFile = JELIX_APP_CONFIG_PATH.$entryPoint['config'];
                $epconfig = new jIniFileModifier($configFile);
                if (!$allEntryPoint && $entryPoint['file'] == $entryPointName) {
                    $epconfig->setValue($module.'.access', 2 , 'modules');
                    $epconfig->save();
                }
            }


            if ($allEntryPoint || $entryPoint['file'] == $entryPointName) {
                $install->setValue($module.'.installed', 1, $entryPoint['id']);
                $install->setValue($module.'.version', $initialVersion, $entryPoint['id']);
                $install->setValue($module.'.sessionid', 0, $entryPoint['id']);
            }

            if ($isdefault) {
                // we set the module as default module for one or all entry points.
                // we set the startModule option for all entry points except
                // if an entry point is indicated on the command line
                if ($allEntryPoint || $entryPoint['file'] == $entryPointName) {
                    if ($epconfig->getValue('startModule') != '') {
                        $epconfig->setValue('startModule', $module);
                        $epconfig->setValue('startAction', 'default:index');
                        $epconfig->save();
                    }
                }
            }
        }

        $install->save();

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
    }
}

