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
                echo "The entrypoint has not been created because of this error: ".$e->getMessage().". No other files have been created.\n";
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
        $inifile->setValue('driver','db','acl2');
        
        $urlconf = $inifile->getValue($entrypoint, 'simple_urlengine_entrypoints', null, true);
        if($urlconf === null || $urlconf == '') {
            // in defaultconfig
            $inifile->setValue($entrypoint, 'jacl2db_admin~*@classic, jauthdb_admin~*@classic, master_admin~*@classic', 'simple_urlengine_entrypoints', null, true);
            // in the config of the entry point
            $inifile->setValue($entrypoint, 'jacl2db~*@classic, jauth~*@classic, jacl2db_admin~*@classic, jauthdb_admin~*@classic, master_admin~*@classic', 'simple_urlengine_entrypoints');
        }
        else {
            $urlconf2 = $inifile->getValue($entrypoint, 'simple_urlengine_entrypoints');
            
            if(strpos($urlconf, 'jacl2db_admin~*@classic') === false)
                $urlconf .= ',jacl2db_admin~*@classic';
            if(strpos($urlconf, 'jauthdb_admin~*@classic') === false)
                $urlconf .= ',jauthdb_admin~*@classic';
            if(strpos($urlconf, 'master_admin~*@classic') === false)
                $urlconf .= ',master_admin~*@classic';
                
            if(strpos($urlconf2, 'jacl2db_admin~*@classic') === false)
                $urlconf2 .= ',jacl2db_admin~*@classic';
            if(strpos($urlconf2, 'jauthdb_admin~*@classic') === false)
                $urlconf2 .= ',jauthdb_admin~*@classic';
            if(strpos($urlconf2, 'master_admin~*@classic') === false)
                $urlconf2 .= ',master_admin~*@classic';
            if(strpos($urlconf2, 'jacl2db~*@classic') === false)
                $urlconf2 .= ',jacl2db~*@classic';
            if(strpos($urlconf2, 'jauth~*@classic') === false)
                $urlconf2 .= ',jauth~*@classic';

            $inifile->setValue($entrypoint, $urlconf, 'simple_urlengine_entrypoints', null, true);
            $inifile->setValue($entrypoint, $urlconf2, 'simple_urlengine_entrypoints');
        }

        if(null == $inifile->getValue($entrypoint, 'basic_significant_urlengine_entrypoints', null, true)) {
            $inifile->setValue($entrypoint, '1', 'basic_significant_urlengine_entrypoints',null,true);
        }

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
            $authini->setValue('form','jauthdb_admin~jelixuser', 'Db');
            if ($profile != '')
                $authini->setValue('profile',$profile, 'Db');
            $tools = jDb::getTools($profile);
            $db = jDb::getConnection($profile);
            $path = JELIX_LIB_PATH.'core-modules/jelix/install/sql/';
            if(file_exists($path.'install_jauth.schema.'.$db->dbms.'.sql')) {
                try {
                    $tools->execSQLScript($path.'install_jauth.schema.'.$db->dbms.'.sql');
                    $rs = $db->query("SELECT usr_login FROM jlx_user WHERE usr_login='admin'");
                    if(!$rs || !$rs->fetch())
                        $db->exec("INSERT INTO jlx_user (usr_login , usr_password , usr_email) VALUES ('admin', '".md5('admin')."', 'admin@localhost.localdomain')");
                    $rs = null;
                } catch(Exception $e) {
                    echo "An error has occured during the execution of SQL script to install jAuth: ".$e->getMessage()."\n";
                }
            }
            else {
                echo "Tables and datas for jAuth.db couldn't be created because SQL scripts are not available for the database declared in the profile.\nYou should initialize the database by hand.\n";
            }    
        }
        else {
            $inifile->setValue('unusedModules', $inifile->getValue('unusedModules').', jauthdb_admin');
        }

        if (!$this->getOption('-noacl2db')) {
            $tools = jDb::getTools($profile);
            $db = jDb::getConnection($profile);
            $path = JELIX_LIB_PATH.'core-modules/jelix/install/sql/';
                
            $tables = $tools->getTableList();
            if (in_array('jacl2_rights', $tables)) {
                ob_start();
                try {
                    $cmd = jxs_load_command('acl2group');
                    $cmd->init(array(),array('action'=>'createuser', '...'=>array('admin')));
                    $cmd->run();
                } catch(Exception $e) { }
                try {
                    $cmd = jxs_load_command('acl2group');
                    $cmd->init(array(),array('action'=>'adduser', '...'=>array('admins','admin')));
                    $cmd->run();
                } catch(Exception $e) { }

                $subjects = array(
                    'auth.users.list'=>  'jelix~auth.acl.users.list',
                    'auth.users.view'=>   'jelix~auth.acl.users.view',
                    'auth.users.modify'=> 'jelix~auth.acl.users.modify',
                    'auth.users.create'=> 'jelix~auth.acl.users.create',
                    'auth.users.delete'=> 'jelix~auth.acl.users.delete',
                    'auth.users.change.password'=> 'jelix~auth.acl.users.change.password',
                    'auth.user.view'=> 'jelix~auth.acl.user.view',
                    'auth.user.modify'=> 'jelix~auth.acl.user.modify',
                    'auth.user.change.password'=> 'jelix~auth.acl.user.change.password'
                );
                
                foreach ($subjects as $subject=>$label) {
                    try {
                        $cmd = jxs_load_command('acl2right');
                        $cmd->init(array(),array('action'=>'subject_create', '...'=>array($subject,$label)));
                        $cmd->run();
                    } catch(Exception $e) { }
                }

                $rights = array(
                    array('auth.users.list', 'admins'),
                    array('auth.users.view', 'admins'),
                    array('auth.users.modify', 'admins'),
                    array('auth.users.create', 'admins'),
                    array('auth.users.delete', 'admins'),
                    array('auth.users.change.password', 'admins'),
                    array('auth.user.view', 'admins'),
                    array('auth.user.modify', 'admins'),
                    array('auth.user.change.password', 'admins'),
                    array('auth.user.view', 'users'),
                    array('auth.user.modify', 'users'),
                    array('auth.user.change.password', 'users')
                ); 
                foreach ($rights as $right) {
                    try {
                        $cmd = jxs_load_command('acl2right');
                        $cmd->init(array(),array('action'=>'add', '...'=>array($right[1],$right[0])));
                        $cmd->run();
                    } catch(Exception $e) { }
                }
                ob_end_clean();
            }
            else {
                if(file_exists($path.'install_jauth.schema.'.$db->dbms.'.sql')) {
                    try {
                        $tools->execSQLScript($path.'install_jacl2.schema.'.$db->dbms.'.sql');
                        $tools->execSQLScript($path.'install_jacl2.data.'.$db->dbms.'.sql');
                    } catch(Exception $e) {
                        echo "An error has occured during the execution of SQL script to install jAcl2.db: ".$e->getMessage()."\n";
                    }
                }
                else {
                    echo "Tables and datas for jAcl2.db couldn't be created because SQL scripts are not available for the database declared in the profile.\nYou should initialize the database by hand.\n";
                }
            }
        }
        else {
            $inifile->setValue('unusedModules', $inifile->getValue('unusedModules').', jacl2db_admin');
        }

        $authini->save();
        $inifile->save();
    }
}

