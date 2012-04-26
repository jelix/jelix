<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Tahina Ramaroson
* @contributor Sylvain de Vathaire, laurent Jouanneau
* @copyright   NEOV 2009
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

/**
* Tests API driver LDAP for jAuth
* @package     testapp
* @subpackage  jelix_tests module
*/

define ("NB_USERS_LDAP",3);

class UTjAuth_LDAP extends jUnitTestCase {

    protected $config;

    public function setUpRun (){
        if(!file_exists(jApp::configPath().'auth_ldap.coord.ini.php')) {
            $this->config = null;
            return;
        }
        $conf = parse_ini_file(jApp::configPath().'auth_ldap.coord.ini.php',true);

        require_once( JELIX_LIB_PATH.'plugins/coord/auth/auth.coord.php');
        jApp::coord()->plugins['auth'] = new AuthCoordPlugin($conf);

        $this->config = & jApp::coord()->plugins['auth']->config;
        $_SESSION[$this->config['session_name']] = new jAuthDummyUser();
    }

    function skip() {
        $this->skipIf($this->config === null, 'Ldap plugin for jauth is not tested because there isn\'t configuration.'.
                               ' To test it, you should create and configure an auth_ldap.coord.ini.php file.');
    }

    public function tearDownRun (){
        unset(jApp::coord()->plugins['auth']);
        unset($_SESSION[$this->config['session_name']]);
        $this->config = null;
    }

    public function testAll (){
        for($i=1;$i<=NB_USERS_LDAP;$i++){

            $myUser=jAuth::createUserObject("testldap usr {$i}","pass{$i}");
            $this->assertTrue($myUser instanceof jAuthUserLDAP);

            jAuth::saveNewUser($myUser);
            $myUserLDAP=jAuth::getUser("testldap usr {$i}");

            $user="
            <object class=\"jAuthUserLDAP\">
                <string property=\"login\" value=\"testldap usr {$i}\" />
                <string property=\"email\" value=\"\" />
                <array property=\"cn\">array('testldap usr {$i}')</array>
                <array property=\"distinguishedName\">array('CN=testldap usr {$i},{$this->config['ldap']['searchBaseDN']}')</array>
                <array property=\"name\">array('testldap usr {$i}')</array>
                <string property=\"password\" value=\"\" />
            </object>
            ";

            $this->assertComplexIdenticalStr($myUserLDAP,$user);

            $myUser->email="usr{$i}.testldap@domain.com";
            jAuth::updateUser($myUser);
            $myUserLDAP=jAuth::getUser("testldap usr {$i}");

            $user="
            <object>
                <string property=\"login\" value=\"testldap usr {$i}\" />
                <array property=\"email\">array('usr{$i}.testldap@domain.com')</array>
                <array property=\"cn\">array('testldap usr {$i}')</array>
                <array property=\"distinguishedName\">array('CN=testldap usr {$i},{$this->config['ldap']['searchBaseDN']}')</array>
                <array property=\"name\">array('testldap usr {$i}')</array>
                <string property=\"password\" value=\"\" />
            </object>
            ";

            $this->assertComplexIdenticalStr($myUserLDAP,$user);

            $this->assertTrue(jAuth::verifyPassword("testldap usr {$i}","pass{$i}"));
            $this->assertTrue(jAuth::changePassword("testldap usr {$i}","newpass{$i}"));

        }

        $myUsersLDAP=jAuth::getUserList('testldap usr*');

        $users="<array>";
        for($i=1;$i<=NB_USERS_LDAP;$i++){
            $users.="
            <object>
                <array property=\"login\">array('testldap usr {$i}')</array>
                <array property=\"email\">array('usr{$i}.testldap@domain.com')</array>
                <array property=\"cn\">array('testldap usr {$i}')</array>
                <array property=\"distinguishedName\">array('CN=testldap usr {$i},{$this->config['ldap']['searchBaseDN']}')</array>
                <array property=\"name\">array('testldap usr {$i}')</array>
                <string property=\"password\" value=\"\" />
            </object>
            ";
        }
        $users.="</array>";

        $this->assertComplexIdenticalStr($myUsersLDAP,$users);

        for($i=1;$i<=NB_USERS_LDAP;$i++){

            $this->assertTrue(jAuth::removeUser("testldap usr {$i}"));
        }

        $myUsersLDAP=jAuth::getUserList('testldap usr*');

        $this->assertFalse(count($myUsersLDAP)>0);

    }


}

?>