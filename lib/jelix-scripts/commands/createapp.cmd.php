<?php

/**
* @package     jelix-scripts
* @author      Jouanneau Laurent
* @contributor Loic Mathaud
* @contributor Gildas Givaja (bug #83)
* @copyright   2005-2007 Jouanneau laurent, 2006 Loic Mathaud, 2007 Gildas Givaja
* @link        http://www.jelix.org
* @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
*/

class createappCommand extends JelixScriptCommand {

    public  $name = 'createapp';
    public  $allowed_options=array('-withdefaultmodule'=>false, '-withcmdline'=>false);
    public  $allowed_parameters=array();

    public  $syntaxhelp = "[-withdefaultmodule] [-withcmdline] [-withrpc]";
    public  $help='';

    function __construct(){
        $this->help= array(
            'fr'=>"
    Créer une nouvelle application avec tous les répertoires nécessaires.

    Si l'option -withdefaultmodule est présente, créer également un module du
    même nom que l'application.

    Si l'option -withcmdline est présente, créer un point d'entrée afin de 
    développer desscripts en ligne de commande.

    Le nom de l'application doit être indiqué
    1) soit en premier paramètre du script jelix
          ".$_SERVER['argv'][0]." --helloApp
    2) soit dans une variable d'environnement JELIX_APP_NAME.
    ",
            'en'=>"
    Create a new application with all directories

    If you give -withdefaultmodule option, it create also a module which have 
    the same name of the application.

    If you give the -withcmdline option, it will create an entry point for
    command line script.

    The application name should be provided by either of this two ways:
    1) by given the name as parameter. Example for a helloApp application
          ".$_SERVER['argv'][0]." --helloApp
    2) or by given the name in the environment variable: JELIX_APP_NAME.
    ",
    );
    }

    public function run(){
       if(file_exists(JELIX_APP_PATH)){
           die("Erreur : application déjà existante\n");
       }

       $this->createDir(JELIX_APP_PATH);
       $this->createDir(JELIX_APP_TEMP_PATH);
       $this->createDir(JELIX_APP_WWW_PATH);
       $this->createDir(JELIX_APP_VAR_PATH);
       $this->createDir(JELIX_APP_LOG_PATH);
       $this->createDir(JELIX_APP_CONFIG_PATH);
       $this->createDir(JELIX_APP_CONFIG_PATH.'index/');
       $this->createDir(JELIX_APP_CONFIG_PATH.'jsonrpc/');
       $this->createDir(JELIX_APP_CONFIG_PATH.'xmlrpc/');
       $this->createDir(JELIX_APP_VAR_PATH.'overloads/');
       $this->createDir(JELIX_APP_VAR_PATH.'themes/');
       $this->createDir(JELIX_APP_VAR_PATH.'themes/default/');
       $this->createDir(JELIX_APP_VAR_PATH.'uploads/');
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

       $param = array('appname'=>$GLOBALS['APPNAME']);

       $this->createFile(JELIX_APP_PATH.'project.xml','project.xml.tpl',$param);
       $this->createFile(JELIX_APP_CONFIG_PATH.'defaultconfig.ini.php','var/config/defaultconfig.ini.php.tpl',$param);
       $this->createFile(JELIX_APP_CONFIG_PATH.'dbprofils.ini.php','var/config/dbprofils.ini.php.tpl',$param);
       $this->createFile(JELIX_APP_CONFIG_PATH.'index/config.ini.php','var/config/index/config.ini.php.tpl',$param);

       $param['rp_temp']=jxs_getRelativePath(JELIX_APP_PATH, JELIX_APP_TEMP_PATH,true);
       $param['rp_var'] =jxs_getRelativePath(JELIX_APP_PATH, JELIX_APP_VAR_PATH,true);
       $param['rp_log'] =jxs_getRelativePath(JELIX_APP_PATH, JELIX_APP_LOG_PATH,true);
       $param['rp_conf']=jxs_getRelativePath(JELIX_APP_PATH, JELIX_APP_CONFIG_PATH,true);
       $param['rp_www'] =jxs_getRelativePath(JELIX_APP_PATH, JELIX_APP_WWW_PATH,true);
       $param['rp_cmd'] =jxs_getRelativePath(JELIX_APP_PATH, JELIX_APP_CMD_PATH,true);

       $this->createFile(JELIX_APP_PATH.'application.init.php','application.init.php.tpl',$param);

       $param = array('appname'=>$GLOBALS['APPNAME']);
       $param['rp_jelix']=jxs_getRelativePath(JELIX_APP_WWW_PATH, JELIX_LIB_PATH ,true);
       $param['rp_app']=jxs_getRelativePath(JELIX_APP_WWW_PATH, JELIX_APP_PATH,true );

       $this->createFile(JELIX_APP_WWW_PATH.'index.php','www/index.php.tpl',$param);
       
       if($this->getOption('-withdefaultmodule')){
            $cmd = jxs_load_command('createmodule');
            $cmd->init(array(),array('module'=>$GLOBALS['APPNAME']));
            $cmd->run();
       }

       if ($this->getOption('-withcmdline')) {
            $this->createDir(JELIX_APP_CMD_PATH);
            $this->createDir(JELIX_APP_CONFIG_PATH.'cmdline');
            $this->createFile(JELIX_APP_CONFIG_PATH.'cmdline/config.ini.php','var/config/cmdline/config.ini.php.tpl',$param);
            $param['rp_cmd'] =jxs_getRelativePath(JELIX_APP_PATH, JELIX_APP_CMD_PATH,true);
            $this->createFile(JELIX_APP_CMD_PATH.'cmdline.php','scripts/cmdline.php.tpl',$param);
       }

       if($this->getOption('-withrpc')){
          $this->createFile(JELIX_APP_WWW_PATH.'jsonrpc.php','www/jsonrpc.php.tpl',$param);
          $this->createFile(JELIX_APP_WWW_PATH.'xmlrpc.php','www/xmlrpc.php.tpl',$param);
          $this->createFile(JELIX_APP_CONFIG_PATH.'jsonrpc/config.ini.php','var/config/jsonrpc/config.ini.php.tpl',$param);
          $this->createFile(JELIX_APP_CONFIG_PATH.'xmlrpc/config.ini.php','var/config/xmlrpc/config.ini.php.tpl',$param);
       }
    }
}

?>