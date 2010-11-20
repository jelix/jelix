<?php

/**
* @package     jelix-scripts
* @author      Laurent Jouanneau
* @contributor 
* @copyright   2008-2010 Laurent Jouanneau
* @link        http://jelix.org
* @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
*/

class createentrypointCommand extends JelixScriptCommand {

    public  $name = 'createentrypoint';
    public  $allowed_options = array('-type'=>true, '-dont-copy-config'=>false);
    public  $allowed_parameters = array('name'=>true, 'config'=>false);

    public  $syntaxhelp = "[-type a_type] [-copy-config configfile] NAME [CONFIG]";
    public  $help = '';

    function __construct(){
        $this->help= array(
            'fr'=>"
    Crée un nouveau point d'entrée dans le répertoire www de l'application, en
    utilisant le nom NAME donné en paramètre, et installe l'application
    pour ce point d'entrée. Le fichier de configuration CONFIG sera utilisé
    pour ce point d'entrée.
    il sera crée si il n'existe pas déjà. Lors de la création, le fichier
    de configuration indiqué dans le paramètre -copy-config sera utilisé comme
    modèle.

    L'option -type indique le type de point d'entrée : classic, jsonrpc,
    xmlrpc, rdf, soap, cmdline.

    Le nom du point d'entrée peut être un chemin sous-repertoire/foo.php.
    ",
            'en'=>"
    Create a new entry point in the www directory of the application, by using
    the name NAME given as a parameter. It will use the configuration file
    CONFIG. This file will be created if it doesn't exist, and in this case,
    the file indicated as the optional parameter --copy-config will be used as
    a model. 
    
    The -type option indicates the type of the entry point: classic, jsonrpc,
    xmlrpc, rdf, soap, cmdline.
    
    The name of the entry point can contain a subdirectory.
    ",
    );
    }

    public function run() {

        // retrieve the type of entry point we want to create
        $type = $this->getOption('-type');
        if (!$type)
            $type = 'classic';
        else if(!in_array($type, array('classic','jsonrpc','xmlrpc','rdf','soap','cmdline')))
            throw new Exception("invalid type");

        // retrieve the name of the entry point
        $name = $this->getParam('name');
        if (preg_match('/(.*)\.php$/', $name, $m)) {
            $name = $m[1];
        }

        // the full path of the entry point
        if ($type == 'cmdline') {
            $entryPointFullPath = JELIX_APP_CMD_PATH.$name.'.php';
            $entryPointTemplate = 'scripts/cmdline.php.tpl';
        }
        else {
            $entryPointFullPath = JELIX_APP_WWW_PATH.$name.'.php';
            $entryPointTemplate = 'www/'.($type=='classic'?'index':$type).'.php.tpl';
        }

        if (file_exists($entryPointFullPath)) {
            throw new Exception("the entry point already exists");
        }
        
        $entryPointDir = dirname($entryPointFullPath).'/';

        $this->loadProjectXml();

        // retrieve the config file name
        $configFile = $this->getParam('config');

        if ($configFile == null) {
            if ($type == 'cmdline') {
                $configFile = 'cmdline/'.$name.'.ini.php';  
            }
            else {
                $configFile = $name.'/config.ini.php';
            }
        }

        // let's create the config file if needed
        if (!file_exists(JELIX_APP_CONFIG_PATH.$configFile)) {
            $this->createDir(dirname(JELIX_APP_CONFIG_PATH.$configFile));
            // the file doesn't exists
            // if there is a -copy-config parameter, we copy this file
            $originalConfig = $this->getOption('-copy-config');
            if ($originalConfig) {
                if (! file_exists(JELIX_APP_CONFIG_PATH.$originalConfig)) {
                    throw new Exception ("unknown original configuration file");
                }
                file_put_contents(JELIX_APP_CONFIG_PATH.$configFile,
                                  file_get_contents(JELIX_APP_CONFIG_PATH.$originalConfig));
            }
            else {
                // else we create a new config file, with the startmodule of the default
                // config as a module name.
                $defaultConfig = parse_ini_file(JELIX_APP_CONFIG_PATH.'defaultconfig.ini.php', true);
                
                $param = array();
                if (isset($defaultConfig['startModule']))
                    $param['modulename'] = $defaultConfig['startModule'];
                else
                    $param['modulename'] = 'jelix';
                
                $this->createFile(JELIX_APP_CONFIG_PATH.$configFile,
                                  'var/config/index/config.ini.php.tpl',
                                  $param);
            }
        }

        require_once (JELIXS_LIB_PATH.'jelix/utils/jIniMultiFilesModifier.class.php');
        $inifile = new jIniMultiFilesModifier(JELIX_APP_CONFIG_PATH.'defaultconfig.ini.php',
                                              JELIX_APP_CONFIG_PATH.$configFile);
        $param = array();
        $param['modulename'] = $inifile->getValue('startModule');
        // creation of the entry point
        $this->createDir($entryPointDir);
        $param['rp_app']   = jxs_getRelativePath($entryPointDir, JELIX_APP_PATH, true);
        $param['config_file'] = $configFile;

        $this->createFile($entryPointFullPath, $entryPointTemplate, $param);

        if ($type == 'cmdline') {
            if (!file_exists(JELIX_APP_PATH.'application-cli.init.php')) {
                $this->createDir(substr(JELIX_APP_TEMP_PATH,-1).'-cli');
                $param2['rp_temp']= jxs_getRelativePath(JELIX_APP_PATH, substr(JELIX_APP_TEMP_PATH,0,-1).'-cli', true);
                $param2['rp_var'] = jxs_getRelativePath(JELIX_APP_PATH, JELIX_APP_VAR_PATH,  true);
                $param2['rp_log'] = jxs_getRelativePath(JELIX_APP_PATH, JELIX_APP_LOG_PATH,  true);
                $param2['rp_conf']= jxs_getRelativePath(JELIX_APP_PATH, JELIX_APP_CONFIG_PATH, true);
                $param2['rp_www'] = jxs_getRelativePath(JELIX_APP_PATH, JELIX_APP_WWW_PATH,  true);
                $param2['rp_cmd'] = jxs_getRelativePath(JELIX_APP_PATH, JELIX_APP_CMD_PATH,  true);
                $param2['rp_jelix'] = jxs_getRelativePath(JELIX_APP_PATH, JELIX_LIB_PATH, true);
                $param2['rp_app']   = jxs_getRelativePath(JELIX_APP_WWW_PATH, JELIX_APP_PATH, true);
                
                $param2['php_rp_temp'] = $this->convertRp($param2['rp_temp']);
                $param2['php_rp_var']  = $this->convertRp($param2['rp_var']);
                $param2['php_rp_log']  = $this->convertRp($param2['rp_log']);
                $param2['php_rp_conf'] = $this->convertRp($param2['rp_conf']);
                $param2['php_rp_www']  = $this->convertRp($param2['rp_www']);
                $param2['php_rp_cmd']  = $this->convertRp($param2['rp_cmd']);
                
                $this->createFile(JELIX_APP_PATH.'application-cli.init.php',
                                  'application.init.php.tpl',$param2);
            }
        }
        else {

            if (null === $inifile->getValue($name, 'simple_urlengine_entrypoints', null, true)) {
                $inifile->setValue($name, '', 'simple_urlengine_entrypoints', null, true);
            }

            if (null === $inifile->getValue($name, 'basic_significant_urlengine_entrypoints', null, true)) {
                $inifile->setValue($name, '1', 'basic_significant_urlengine_entrypoints', null, true);
            }
            $inifile->save();
        }

        $this->updateProjectXml($name.".php", $configFile , $type);

        require_once (JELIXS_LIB_PATH.'jelix/installer/jInstaller.class.php');
        $installer = new jInstaller(new textInstallReporter('warning'));
        $installer->installEntryPoint($name.".php");
    }

    protected function updateProjectXml ($fileName, $configFileName, $type) {

        $elem = $this->projectXml->createElementNS(JELIX_NAMESPACE_BASE.'project/1.0', 'entry');
        $elem->setAttribute("file", $fileName);
        $elem->setAttribute("config", $configFileName);
        $elem->setAttribute("type", $type);

        $ep = $this->projectXml->documentElement->getElementsByTagName("entrypoints");

        if (!$ep->length) {
            $ep = $this->projectXml->createElementNS(JELIX_NAMESPACE_BASE.'project/1.0', 'entrypoints');
            $doc->documentElement->appendChild($ep);
            $ep->appendChild($elem);
        }
        else
            $ep->item(0)->appendChild($elem);

        $this->projectXml->save(JELIX_APP_PATH.'project.xml');
    }
}

