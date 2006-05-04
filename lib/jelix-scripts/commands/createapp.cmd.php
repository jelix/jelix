<?php

/**
* @package     jelix-scripts
* @version     $Id$
* @author      Jouanneau Laurent
* @contributor Loic Mathaud
* @copyright   2005-2006 Jouanneau laurent
* @link        http://www.jelix.org
* @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
*/

class createappCommand extends JelixScriptCommand {

    public  $name = 'createapp';
    public  $allowed_options=array('-withdefaultmodule'=>false, '-withcmdline'=>false);
    public  $allowed_parameters=array();

    public  $syntaxhelp = "[-withdefaultmodule] [-withcmdline]";
    public  $help="
    Créer une nouvelle application avec tous les répertoires nécessaires.

    Si l'option -withdefaultmodule est présente, créer également un module du
    même nom que l'application.
    
    Si l'option -withcmdline est présente, créer un point d'entrée afin de développer des
    scripts en ligne de commande.

    Le nom de l'application doit être indiqué
    1) soit en premier paramètre du script jelix.php
          jelix.php --helloApp
    2) soit dans une variable d'environnement JELIX_APP_NAME.
    ";


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
       $this->createDir(JELIX_APP_VAR_PATH.'overloads/');
       $this->createDir(JELIX_APP_VAR_PATH.'themes/');
       $this->createDir(JELIX_APP_VAR_PATH.'themes/default/');
       $this->createDir(JELIX_APP_PATH.'modules');
       $this->createDir(JELIX_APP_PATH.'plugins');
       $this->createDir(JELIX_APP_PATH.'responses');

       $param = array('appname'=>$GLOBALS['APPNAME']);


       $this->createFile(JELIX_APP_PATH.'project.xml','project.xml.tpl',$param);
       $this->createFile(JELIX_APP_CONFIG_PATH.'config.classic.ini.php','config.classic.ini.php.tpl',$param);
       $this->createFile(JELIX_APP_CONFIG_PATH.'dbprofils.ini.php','dbprofils.ini.php.tpl',$param);

       $param['rp_temp']=jxs_getRelativePath(JELIX_APP_PATH, JELIX_APP_TEMP_PATH);
       $param['rp_var'] =jxs_getRelativePath(JELIX_APP_PATH, JELIX_APP_VAR_PATH);
       $param['rp_log'] =jxs_getRelativePath(JELIX_APP_PATH, JELIX_APP_LOG_PATH);
       $param['rp_conf']=jxs_getRelativePath(JELIX_APP_PATH, JELIX_APP_CONFIG_PATH);
       $param['rp_www'] =jxs_getRelativePath(JELIX_APP_PATH, JELIX_APP_WWW_PATH);

       $this->createFile(JELIX_APP_PATH.'application.init.php','application.init.php.tpl',$param);


       $param = array('appname'=>$GLOBALS['APPNAME']);
       $param['rp_jelix']=jxs_getRelativePath(JELIX_APP_WWW_PATH, JELIX_LIB_PATH );
       $param['rp_app']=jxs_getRelativePath(JELIX_APP_WWW_PATH, JELIX_APP_PATH );

       $this->createFile(JELIX_APP_WWW_PATH.'index.php','www/index.php.tpl',$param);
       $this->createFile(JELIX_APP_WWW_PATH.'jsonrpc.php','www/jsonrpc.php.tpl',$param);
       $this->createFile(JELIX_APP_WWW_PATH.'xmlrpc.php','www/xmlrpc.php.tpl',$param);


       if($this->getOption('-withdefaultmodule')){
            $cmd = jxs_load_command('createmodule');
            $cmd->init(array(),array('module'=>$GLOBALS['APPNAME']));
            $cmd->run();
       }
       
       if ($this->getOption('-withcmdline')) {
            $this->createDir(JELIX_APP_CMD_PATH);
            $this->createFile(JELIX_APP_CONFIG_PATH.'config.cmdline.ini.php','config.cmdline.ini.php.tpl',$param);
            $param['rp_cmd'] =jxs_getRelativePath(JELIX_APP_PATH, JELIX_APP_CMD_PATH);
            $this->createFile(JELIX_APP_CMD_PATH.'cmdline.php','scripts/cmdline.php.tpl',$param);
       }

    }
}



?>
