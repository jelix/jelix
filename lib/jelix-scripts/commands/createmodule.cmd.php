<?php
/**
* @package     jelix-scripts
* @author      Jouanneau Laurent
* @contributor Loic Mathaud
* @contributor Bastien Jaillot
* @copyright   2005-2007 Jouanneau laurent, 2007 Loic Mathaud, 2008 Bastien Jaillot
* @link        http://www.jelix.org
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

        // note: since module name are used for name of generated name,
        // only this characters are allowed
        if(preg_match('/([^a-zA-Z_0-9])/', $this->_parameters['module'])) {
            throw new Exception("the name '".$this->_parameters['module']."' is not valid for a module");
        }
        
        $path= $this->getModulePath($this->_parameters['module'], false);

        if(file_exists($path)){
            throw new Exception("module '".$this->_parameters['module']."' already exists");
        }
        $this->createDir($path);
        
        $param = array();
        $param['name']=$this->_parameters['module'];
        $param['default_id'] = $this->_parameters['module'].JELIXS_INFO_DEFAULT_IDSUFFIX;

        $this->createFile($path.'module.xml','module.xml.tpl',$param);

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

        if(!$this->getOption('-nocontroller')){
            $agcommand = jxs_load_command('createctrl');
            $options = array();
            if ($this->getOption('-cmdline')) {
               $options = array('-cmdline'=>true);
            }
            if ($this->getOption('-addinstallzone')) {
                $options = array('-addinstallzone'=>true);
            }
            $agcommand->init($options,array('module'=>$this->_parameters['module'], 'name'=>'default','method'=>'index'));
            $agcommand->run();
        }
        $inifiles = array(JELIX_APP_CONFIG_PATH.'index/config.ini.php',
                        JELIX_APP_CONFIG_PATH.'cmdline/config.ini.php',
                        JELIX_APP_CONFIG_PATH.'jsonrpc/config.ini.php',
                        JELIX_APP_CONFIG_PATH.'xmlrpc/config.ini.php',
                        );
        $isdefault = $this->getOption('-defaultmodule');
        foreach($inifiles as $k=> $filename) {
            if(!file_exists($filename))
                continue;
            try {
                $ini = new jIniFileModifier($filename);
                if ($isdefault && $k == 0) {
                    $ini->setValue('startModule', $this->_parameters['module']);
                    $ini->setValue('startAction', 'default:index');
                }
                else if ($ini->getValue('startModule') == '')
                    $ini->setValue('startModule', $this->_parameters['module']);
                $ini->save();
            }catch(Exception $e){
                echo "Error during the modification of an ini file: ".$e->getMessage()."\n";
            }
        }
    }
}

