<?php

/**
* page for Installation wizard
*
* @package     InstallWizard
* @subpackage  pages
* @author      Laurent Jouanneau
* @copyright   2010 Laurent Jouanneau
* @link        http://jelix.org
* @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
*/


/**
 * page for a wizard, to configure database access for a jelix application
 */
class dbprofileWizPage extends installWizardPage {

    /**
     * action to display the page
     * @param jTpl $tpl the template container
     */
    function show ($tpl) {
        if (!isset($_SESSION['dbprofiles'])) {
            $this->loadProfiles();
        }

        $sections = $_SESSION['dbprofiles']['profiles'];
        $data = $_SESSION['dbprofiles']['data'];

        $ignoreProfiles = isset($this->config['ignoreProfiles'])?$this->config['ignoreProfiles']:'';
        $ignoreProfiles = preg_split("/ *, */", $ignoreProfiles);

        if (count($ignoreProfiles)) {
            $newsections = array();
            foreach($sections as $profile) {
                if(!in_array($profile, $ignoreProfiles))
                    $newsections[] = $profile;
            }
            $tpl->assign('profiles', $newsections);
            $_SESSION['dbprofiles']['profiles'] = $newsections;
        }
        else {
            $tpl->assign('profiles', $sections);
        }

        $tpl->assign($data);

        $drivers = isset($this->config['availabledDrivers'])?$this->config['availabledDrivers']:'mysql';
        $drivers = preg_split("/ *, */",$drivers);
        // TODO check if corresponding extensions are available
        $tpl->assign('drivers', $drivers);

        return true;
    }

    function process() {

        $ini = new jIniFileModifier(JELIX_APP_CONFIG_PATH.'dbProfils.ini.php');
        $hasErrors = false;

        foreach ($_SESSION['dbprofiles']['profiles'] as $profile) {
            $errors = array();
            $params = array();
            $driver = '';

            if(isset($_POST['usepdo'][$profile]) && $_POST['usepdo'][$profile] == 'on') {
                $ini->setValue('usepdo', true, $profile);
            }
            else
                $ini->removeValue('usepdo', $profile);

            if(isset($_POST['persistent'][$profile]) && $_POST['persistent'][$profile] == 'on') {
                $ini->setValue('persistent', true, $profile);
            }
            else
                $ini->removeValue('persistent', $profile);

            if(isset($_POST['force_encoding'][$profile]) && $_POST['force_encoding'][$profile] == 'on') {
                $ini->setValue('force_encoding', true, $profile);
            }
            else
                $ini->removeValue('force_encoding', $profile);

            $ini->setValue('prefix', $_POST['prefix'][$profile], $profile);

            $database = trim($_POST['database'][$profile]);
            if ($database == '') {
                $errors[] = $this->locales['error.missing.database'];
                continue;
            }
            $params['database'] = $database;
            $ini->setValue('database', $database, $profile);

            $driver = $_POST['driver'][$profile];
            $params['driver'] = $driver;
            if ($driver != 'sqlite') {

                $host = trim($_POST['host'][$profile]);
                if ($host == '' && $driver != 'pgsql') {
                    $errors[] = $this->locales['error.missing.host'];
                }
                else {
                    $ini->setValue('host', $host, $profile);
                    $params['host'] = $host;
                }

                $user = trim($_POST['user'][$profile]);
                if ($user == '') {
                    $errors[] = $this->locales['error.missing.user'];
                }
                else {
                    $ini->setValue('user', $user, $profile);
                    $params['user'] = $user;
                }

                $password = trim($_POST['password'][$profile]);
                if ($password == '') {
                    $errors[] = $this->locales['error.missing.password'];
                }
                else {
                    $ini->setValue('password', $password, $profile);
                     $params['password'] = $password;
                }

                if ($_POST['passwordconfirm'][$profile] != $password) {
                    $errors[] = $this->locales['error.invalid.confirm.password'];
                }
            }

            if (!count($errors)) {
                try {
                    if ($ini->getValue('usepdo', $profile)) {
                        $m = 'check_PDO';
                    }
                    else {
                        $m = 'check_'.$driver;
                    }
                    $this->$m($params);
                }
                catch (Exception $e) {
                    $errors[] = $e->getMessage();
                }
            }

            if (count($errors))
                $hasErrors = true;

            $_SESSION['dbprofiles']['data']['errors'][$profile] = $errors;
        }

        if ($hasErrors)
            return false;

        $ini->save();
        unset($_SESSION['dbprofiles']);
        return 0;
    }

    protected function loadProfiles () {
        $file = JELIX_APP_CONFIG_PATH.'dbProfils.ini.php';

        if (file_exists($file)) {

        }
        elseif (file_exists(JELIX_APP_CONFIG_PATH.'dbProfils.ini.php.dist')) {
             copy(JELIX_APP_CONFIG_PATH.'dbProfils.ini.php.dist', $file);
        }
        else {
            file_put_contents($file, ";<?php die(''); ?>
;for security reasons, don't remove or modify the first line

[default]
driver=mysql
database=
host=localhost
user=
password=
persistent = on
force_encoding = on
");
        }

        $ini = new jIniFileModifier($file);

        $data = array(
            'driver'=>array(),
            'database'=>array(),
            'host'=>array(),
            'user'=>array(),
            'password'=>array(),
            'passwordconfirm'=>array(),
            'persistent'=>array(),
            'prefix'=>array(),
            'usepdo'=>array(),
            'force_encoding'=>array(),
        );

        $profiles = $ini->getSectionList();
        foreach($profiles as $profile) {
            $driver = $ini->getValue('driver', $profile);
            if ($driver == 'pdo') {
                $data['usepdo'][$profile] = true;
                $dsn = $ini->getValue('dsn', $profile);
                $data['driver'][$profile] = substr($dsn,0,strpos($dsn,':'));
                if (preg_match("/host=([^;]*)(;|$)/", $dsn, $m)) {
                    $data['host'][$profile] = $m[1];
                }
                else {
                    $host = $ini->getValue('host', $profile);
                    $data['host'][$profile] = ($host===null?'':$host);
                }
                if (preg_match("/dbname=([^;]*)(;|$)/", $dsn, $m)) {
                    $data['database'][$profile] = $m[1];
                }
                else {
                    $host = $ini->getValue('database', $profile);
                    $data['database'][$profile] = ($host===null?'':$host);
                }
            }
            else {
                $data['usepdo'][$profile] = $ini->getValue('usepdo', $profile);
                $data['driver'][$profile] = $ini->getValue('driver', $profile);
                $data['database'][$profile] = $ini->getValue('database', $profile);
                $data['host'][$profile] = $ini->getValue('host', $profile);
            }

            $data['user'][$profile] = $ini->getValue('user', $profile);
            $data['password'][$profile] = $ini->getValue('password', $profile);
            $data['passwordconfirm'][$profile] = $data['password'][$profile];
            $data['persistent'][$profile] = $ini->getValue('persistent', $profile);
            $data['force_encoding'][$profile] = $ini->getValue('force_encoding', $profile);
            $data['prefix'][$profile] = $ini->getValue('prefix', $profile);
            $data['errors'][$profile] = array();
        }

        $_SESSION['dbprofiles']['profiles'] = $profiles;
        $_SESSION['dbprofiles']['data'] = $data;
    }

    protected function check_mssql($params) {
        if(!function_exists('mssql_connect')) {
            throw new Exception($this->locales['error.extension.mssql.not.installed']);
        }
        if ($cnx = @mssql_connect ($params['host'], $params['user'], $params['password'])) {
            if(!mssql_select_db ($params['database'], $cnx))
                throw new Exception($this->locales['error.no.database']);
            mssql_close($cnx);
        }
        else {
            throw new Exception($this->locales['error.no.connection']);
        }
        return true;
    }

    protected function check_mysql($params) {
        if(!function_exists('mysql_connect')) {
            throw new Exception($this->locales['error.extension.mysql.not.installed']);
        }
        if ($cnx = @mysql_connect ($params['host'], $params['user'], $params['password'])) {
            if(!mysql_select_db ($params['database'], $cnx))
                throw new Exception($this->locales['error.no.database']);
            mysql_close($cnx);
        }
        else {
            throw new Exception($this->locales['error.no.connection']);
        }
        return true;
    }

    protected function check_oci($params) {
    }

    protected function check_pgsql($params) {
        if(!function_exists('pg_connect')) {
            throw new Exception($this->locales['error.extension.pgsql.not.installed']);
        }

        $str = '';

        // we do a distinction because if the host is given == TCP/IP connection else unix socket
        if($params['host'] != '')
            $str = 'host=\''.$params['host'].'\''.$str;

        if (isset($params['port'])) {
            $str .= ' port=\''.$params['port'].'\'';
        }

        if ($params['database'] != '') {
            $str .= ' dbname=\''.$params['database'].'\'';
        }

        // we do isset instead of equality test against an empty string, to allow to specify
        // that we want to use configuration set in environment variables
        if (isset($params['user'])) {
            $str .= ' user=\''.$params['user'].'\'';
        }

        if (isset($params['password'])) {
            $str .= ' password=\''.$params['password'].'\'';
        }

        if (isset($params['timeout']) && $params['timeout'] != '') {
            $str .= ' connect_timeout=\''.$params['timeout'].'\'';
        }

        if ($cnx = @pg_connect ($str)) {
            pg_close($cnx);
        }
        else {
            throw new Exception($this->locales['error.no.connection']);
        }
        return true;
    }

    protected function check_sqlite($params) {
        if(!function_exists('sqlite_open')) {
            throw new Exception($this->locales['error.extension.sqlite.not.installed']);
        }
        if ($cnx = @sqlite_open (JELIX_APP_VAR_PATH. 'db/sqlite/'.$params['database'])) {
            sqlite_close($cnx);
        }
        else {
            throw new Exception($this->locales['error.no.connection']);
        }
        return true;
    }

    protected function check_PDO($params) {
        $dsn = $params['driver'].':host='.$params['host'].';dbname='.$params['database'];
        if ($params['driver'] == 'sqlite') {
            $user = '';
            $password = '';
        }
        else {
            $user = $params['user'];
            $password = $params['password'];
        }

        unset ($params['driver']);
        unset ($params['host']);
        unset ($params['database']);
        unset ($params['user']);
        unset ($params['password']);

        $pdo = new PDO($dsn, $user, $password, $params);
        $pdo = null;
    }
    
}
