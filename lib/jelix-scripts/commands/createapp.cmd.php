<?php

/**
* @package     jelix-scripts
* @author      Laurent Jouanneau
* @contributor Loic Mathaud
* @contributor Gildas Givaja (bug #83)
* @contributor Christophe Thiriot
* @contributor Bastien Jaillot
* @contributor Dominique Papin
* @copyright   2005-2010 Laurent Jouanneau, 2006 Loic Mathaud, 2007 Gildas Givaja, 2007 Christophe Thiriot, 2008 Bastien Jaillot, 2008 Dominique Papin
* @link        http://www.jelix.org
* @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
*/

class createappCommand extends JelixScriptCommand {

    public  $name = 'createapp';
    public  $allowed_options=array('-nodefaultmodule'=>false,
                                   '-withcmdline'=>false,
                                   '-wwwpath'=>true);
    public  $allowed_parameters=array();

    public  $syntaxhelp = "[-nodefaultmodule] [-withcmdline] [-wwwpath a_path]";
    public  $help='';

    public $applicationMustExist = false;

    function __construct(){
        $this->help= array(
            'fr'=>"
    Crée une nouvelle application avec tous les répertoires nécessaires et un module
    du même nom que l'application.

    Si l'option -nodefaultmodule est présente, le module n'est pas créé.

    Si l'option -withcmdline est présente, crée un point d'entrée afin de
    développer des scripts en ligne de commande.

    Si l'option -wwwpath est présente, sa valeur définit le document root de votre application. 
    wwwpath doit être relatif au répertoire de l'application (valeur par défaut www/). 

    Le nom de l'application doit être indiqué
    1) soit en premier paramètre du script jelix
          ".$_SERVER['argv'][0]." --helloApp
    2) soit dans une variable d'environnement JELIX_APP_NAME.
    ",
            'en'=>"
    Create a new application with all directories and one module named as your application.

    If you give -nodefaultmodule option, it won't create the module. 

    If you give the -withcmdline option, it will create an entry point dedicated to 
    command line scripts. 

    If you give the -wwwpath option, it will replace your application default document root. 
    wwwpath must be relative to your application directory (default value is 'www/').

    The application name should be provided by either of this two ways:
    1) by given the name as parameter. Example for a helloApp application
          ".$_SERVER['argv'][0]." --helloApp
    2) or by given the name in the environment variable: JELIX_APP_NAME.
    ",
    );
    }

    public function run() {
        require_once (LIB_PATH.'clearbricks/jelix.inc.php');
        require_once (JELIXS_LIB_PATH.'jelix/installer/jInstaller.class.php');

        if (file_exists(JELIX_APP_PATH)) {
            throw new Exception("this application is already created");
        }

        $this->createDir(JELIX_APP_PATH);

        if ($p = $this->getOption('-wwwpath')) {
            $wwwpath = path::real(JELIX_APP_PATH.$p, false).'/';
        }
        else {
            $wwwpath = JELIX_APP_WWW_PATH;
        }

        $this->createDir(JELIX_APP_REAL_TEMP_PATH);
        $this->createDir(JELIX_APP_CLI_TEMP_PATH);
        $this->createDir(JELIX_APP_TEMP_PATH);
        $this->createDir($wwwpath);
        $this->createDir(JELIX_APP_VAR_PATH);
        $this->createDir(JELIX_APP_LOG_PATH);
        $this->createDir(JELIX_APP_CONFIG_PATH);
        $this->createDir(JELIX_APP_CONFIG_PATH.'index/');
        $this->createDir(JELIX_APP_VAR_PATH.'overloads/');
        $this->createDir(JELIX_APP_VAR_PATH.'themes/');
        $this->createDir(JELIX_APP_VAR_PATH.'themes/default/');
        $this->createDir(JELIX_APP_VAR_PATH.'uploads/');
        $this->createDir(JELIX_APP_VAR_PATH.'sessions/');
        $this->createDir(JELIX_APP_VAR_PATH.'mails/');
        $this->createDir(JELIX_APP_PATH.'install');
        $this->createDir(JELIX_APP_PATH.'modules');
        $this->createDir(JELIX_APP_PATH.'plugins');
        $this->createDir(JELIX_APP_PATH.'plugins/coord/');
        $this->createDir(JELIX_APP_PATH.'plugins/tpl/');
        $this->createDir(JELIX_APP_PATH.'plugins/tpl/common');
        $this->createDir(JELIX_APP_PATH.'plugins/tpl/html');
        $this->createDir(JELIX_APP_PATH.'plugins/tpl/text');
        $this->createDir(JELIX_APP_PATH.'plugins/db/');
        $this->createDir(JELIX_APP_PATH.'plugins/auth/');
        $this->createDir(JELIX_APP_PATH.'responses');
        $this->createDir(JELIX_APP_PATH.'scripts');

        $param = array();
        $param['default_id'] = $GLOBALS['APPNAME'].JELIXS_INFO_DEFAULT_IDSUFFIX;

        if($this->getOption('-nodefaultmodule')) {
            $param['tplname']    = 'jelix~defaultmain';
            $param['modulename'] = 'jelix';
        }
        else {
            // note: since module name are used for name of generated name,
            // only this characters are allowed
            $param['modulename'] = preg_replace('/([^a-zA-Z_0-9])/','_',$GLOBALS['APPNAME']);
            $param['tplname']    = $param['modulename'].'~main';
        }

        $param['config_file'] = 'index/config.ini.php';

        $param['rp_temp']  = jxs_getRelativePath(JELIX_APP_PATH, JELIX_APP_REAL_TEMP_PATH, true);
        $param['rp_var']   = jxs_getRelativePath(JELIX_APP_PATH, JELIX_APP_VAR_PATH,  true);
        $param['rp_log']   = jxs_getRelativePath(JELIX_APP_PATH, JELIX_APP_LOG_PATH,  true);
        $param['rp_conf']  = jxs_getRelativePath(JELIX_APP_PATH, JELIX_APP_CONFIG_PATH, true);
        $param['rp_www']   = jxs_getRelativePath(JELIX_APP_PATH, $wwwpath,  true);
        $param['rp_cmd']   = jxs_getRelativePath(JELIX_APP_PATH, JELIX_APP_CMD_PATH,  true);
        $param['rp_jelix'] = jxs_getRelativePath(JELIX_APP_PATH, JELIX_LIB_PATH, true);
        $param['rp_app']   = jxs_getRelativePath($wwwpath, JELIX_APP_PATH, true);

        $this->createFile(JELIX_APP_PATH.'.htaccess', 'htaccess_deny', $param);
        $this->createFile(JELIX_APP_PATH.'project.xml','project.xml.tpl', $param);
        $this->createFile(JELIX_APP_CONFIG_PATH.'defaultconfig.ini.php', 'var/config/defaultconfig.ini.php.tpl', $param);
        //$this->createFile(JELIX_APP_CONFIG_PATH.'installer.ini.php', 'var/config/installer.ini.php.tpl', $param);
        $this->createFile(JELIX_APP_CONFIG_PATH.'dbprofils.ini.php', 'var/config/dbprofils.ini.php.tpl', $param);
        $this->createFile(JELIX_APP_CONFIG_PATH.'cache.ini.php', 'var/config/cache.ini.php.tpl', $param);
        $this->createFile(JELIX_APP_CONFIG_PATH.'kvprofiles.ini.php', 'var/config/kvprofiles.ini.php.tpl', $param);
        $this->createFile(JELIX_APP_CONFIG_PATH.'index/config.ini.php', 'var/config/index/config.ini.php.tpl', $param);
        $this->createFile(JELIX_APP_PATH.'responses/myHtmlResponse.class.php', 'responses/myHtmlResponse.class.php.tpl', $param);
        $this->createFile(JELIX_APP_PATH.'install/installer.php','installer/installer.php.tpl',$param);

        $this->createFile($wwwpath.'index.php', 'www/index.php.tpl',$param);
        $this->createFile($wwwpath.'.htaccess', 'htaccess_allow',$param);

        $param['php_rp_temp'] = $this->convertRp($param['rp_temp']);
        $param['php_rp_var']  = $this->convertRp($param['rp_var']);
        $param['php_rp_log']  = $this->convertRp($param['rp_log']);
        $param['php_rp_conf'] = $this->convertRp($param['rp_conf']);
        $param['php_rp_www']  = $this->convertRp($param['rp_www']);
        $param['php_rp_cmd']  = $this->convertRp($param['rp_cmd']);

        $this->createFile(JELIX_APP_PATH.'application.init.php','application.init.php.tpl',$param);
    
        $param['rp_temp_app']     = $param['rp_temp'];
        $param['php_rp_temp_app'] = $param['php_rp_temp'];
        $param['rp_temp']         = jxs_getRelativePath(JELIX_APP_PATH, JELIX_APP_TEMP_PATH, true);
        $param['rp_temp_cli']     = jxs_getRelativePath(JELIX_APP_PATH, JELIX_APP_CLI_TEMP_PATH, true);
        $param['php_rp_temp']     = $this->convertRp($param['rp_temp']);
        $param['php_rp_temp_cli'] = $this->convertRp($param['rp_temp_cli']);
        
        $this->createFile(JELIX_APP_PATH.'jelix-scripts.init.php','jelix-scripts.init.php.tpl',$param);
    
        $param['rp_temp']     = jxs_getRelativePath(JELIX_APP_PATH, JELIX_APP_CLI_TEMP_PATH, true);
        $param['php_rp_temp'] = $this->convertRp($param['rp_temp']);

        $this->createFile(JELIX_APP_PATH.'application-cli.init.php','application.init.php.tpl',$param);

        $installer = new jInstaller(new textInstallReporter('warning'));
        $installer->installApplication();

        $moduleok = true;

        if (!$this->getOption('-nodefaultmodule')) {
            try {
                $cmd = jxs_load_command('createmodule');
                $cmd->init(array('-addinstallzone'=>true), array('module'=>$param['modulename']));
                $cmd->run();
                $this->createFile(JELIX_APP_PATH.'modules/'.$param['modulename'].'/templates/main.tpl', 'module/main.tpl.tpl', $param);
            } catch (Exception $e) {
                $moduleok = false;
                echo "The module has not been created because of this error: ".$e->getMessage()."\nHowever the application has been created\n";
            }
        }

        if ($this->getOption('-withcmdline')) {
            if(!$this->getOption('-nodefaultmodule') && $moduleok){
                $agcommand = jxs_load_command('createctrl');
                $options = array('-cmdline'=>true);
                $agcommand->init($options,array('module'=>$param['modulename'], 'name'=>'default','method'=>'index'));
                $agcommand->run();
            }
            $agcommand = jxs_load_command('createentrypoint');
            $options = array('-type'=>'cmdline');
            $parameters = array('name'=>$param['modulename']);
            $agcommand->init($options, $parameters);
            $agcommand->run();
        }
    }
    
    protected function convertRp($rp) {
        if (strpos($rp, '../') !== false) {
            return "realpath(JELIX_APP_PATH.'".$rp."').'/'";
        }
        else {
            if(strpos($rp, './') === 0)
                $rp = substr($rp, 2);
            return "JELIX_APP_PATH.'".$rp."'";
        }
    }
}
