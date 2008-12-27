<?php
/**
* @package     jelix-scripts
* @author      Laurent Jouanneau
* @contributor 
* @copyright   2008 Laurent Jouanneau
* @link        http://jelix.org
* @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
*/

class initadminCommand extends JelixScriptCommand {

    public  $name = 'initadmin';
    public  $allowed_options=array('-noauthdb'=>false,
                                   '-noacl2db'=>false,
                                   '-profile'=>true);
    public  $allowed_parameters=array('entrypoint'=>true);

    public  $syntaxhelp = "[-noauthdb] [-noacl2db] [-profile a_jdb_profile] entrypoint";
    public  $help='';

    function __construct(){
        $this->help= array(
            'fr'=>"
    Initialise l'application avec interface d'administration en utilisant
    le module master_admin ainsi que jAuth et jAcl.

    Les options -noauthdb et -noacl2db indiquent de ne pas utiliser et configurer
    respectivement le driver db pour jAuth et le driver db pour jAcl2. La configuration
    de jAcl2 et de jAuth pour l'accés à l'administration sera donc à votre charge.
    
    L'option -profile permet d'indiquer le profil jDb à utiliser pour les drivers
    db de jAuth et jAcl2.
    
    L'argument entrypoint permet d'indique le point d'entrée qui sera utilisé pour
    l'administration. Attention, si le point d'entrée existe déjà, il sera reconfiguré.
    ",
            'en'=>"
    Initialize the application with a web interface for administration, by activating
    the module master_admin and configuring jAuth and jAcl.
    
    Options -noauthdb and -noacl2db indicate to not use and configure the driver 'db'
    of jAuth and the driver 'db' of jAcl2. So you will have to configure jAuth and/or jAcl2
    by yourself.
    
    The argument 'entrypoint' indicates the entry point to use for the administration.
    Carefull : if the entry point already exists, its configuration will be changed.
    ",
    );
    }

    public function run(){
        jxs_init_jelix_env();
        $entrypoint = $this->getParam('entrypoint');

        if (!file_exists(JELIX_APP_WWW_PATH.$entrypoint.'.php')) {
            try {
                $cmd = jxs_load_command('createentrypoint');
                $cmd->init(array(),array('name'=>$entrypoint));
                $cmd->run();
            } catch (Exception $e) {
                echo "The entrypoint has not been created because of this error: ".$e->getMessage().". No other files have been created.";
            }
        }
        $inifile = new jIniMultiFilesModifier(JELIX_APP_CONFIG_PATH.'defaultconfig.ini.php',
                                          JELIX_APP_CONFIG_PATH.$entrypoint.'/config.ini.php');

        $inifile->setValue('html', 'adminHtmlResponse', 'responses');
        $inifile->setValue('htmlauth', 'adminLoginHtmlResponse', 'responses');
        $inifile->setValue('auth', $entrypoint.'/auth.coord.ini.php', 'coordplugins');
        $inifile->setValue('jacl2', $entrypoint.'/jacl2.coord.ini.php', 'coordplugins');

        $inifile->setValue('startModule', 'master_admin');
        $inifile->setValue('startAction', 'default:index');
        $modulePath = $inifile->getValue("modulesPath");
        if(strpos($modulePath, 'lib:jelix-admin-modules')===false){
            $inifile->setValue('modulesPath', 'lib:jelix-admin-modules/,'.$modulePath);
        }
        
        $inifile->setValue('enableAcl2DbEventListener','on','acl2');

        $params = array();

        $this->createFile(JELIX_APP_PATH.'responses/adminHtmlResponse.class.php','responses/adminHtmlResponse.class.php.tpl',$params);
        $this->createFile(JELIX_APP_PATH.'responses/adminLoginHtmlResponse.class.php','responses/adminLoginHtmlResponse.class.php.tpl',$params);

        $this->createFile(JELIX_APP_PATH.'var/config/'.$entrypoint.'/auth.coord.ini.php','var/config/auth.coord.ini.php.tpl',$params);
        $this->createFile(JELIX_APP_PATH.'var/config/'.$entrypoint.'/jacl2.coord.ini.php','var/config/jacl2.coord.ini.php.tpl',$params);
        
        $authini = new jIniFileModifier(JELIX_APP_CONFIG_PATH.$entrypoint.'/auth.coord.ini.php');
        $authini->setValue('after_login','master_admin~default:index');
        $authini->setValue('timeout','30');

        $profile = $this->getOption('-profile');

        if (!$this->getOption('-noauthdb')) {
            $authini->setValue('dao','jauth~jelixuser', 'Db');
            if ($profile != '')
                $authini->setValue('profile',$profile, 'Db');
            $tools = jDb::getTools($profile);
            $db = jDb::getConnection($profile);
            $path = JELIX_LIB_PATH.'core-modules/jelix/install/sql/';
            if(file_exists($path.'install_jauth.schema.'.$db->dbms.'.sql')) {
                try {
                    $tools->execSQLScript($path.'install_jauth.schema.'.$db->dbms.'.sql');
                    $tools->execSQLScript($path.'install_jauth.data.'.$db->dbms.'.sql');
                } catch(Exception $e) {
                    echo "An error has occured during the execution of SQL script to install jAuth: ".$e->getMessage();
                }
            }
            else {
                echo "Tables and datas for jAuth.db couldn't be created because SQL scripts are not available for the database declared in the profile.\nYou should initialize the database by hand.";
            }    
        }
        else {
            //$inifile->setValue('unusedModules', $inifile->getValue('unusedModules').', jauthdb_admin');
        }

        if (!$this->getOption('-noacl2db')) {
            $tools = jDb::getTools($profile);
            $db = jDb::getConnection($profile);
            $path = JELIX_LIB_PATH.'core-modules/jelix/install/sql/';
            if(file_exists($path.'install_jauth.schema.'.$db->dbms.'.sql')) {
                try {
                    $tools->execSQLScript($path.'install_jacl2.schema.'.$db->dbms.'.sql');
                    $tools->execSQLScript($path.'install_jacl2.data.'.$db->dbms.'.sql');
                } catch(Exception $e) {
                    echo "An error has occured during the execution of SQL script to install jAcl2.db: ".$e->getMessage();
                }
            }
            else {
                echo "Tables and datas for jAcl2.db couldn't be created because SQL scripts are not available for the database declared in the profile.\nYou should initialize the database by hand.";
            }    
        }
        else {
            $inifile->setValue('unusedModules', $inifile->getValue('unusedModules').', jacl2db_admin');
        }

        $authini->save();
        $inifile->save();
    }
}

