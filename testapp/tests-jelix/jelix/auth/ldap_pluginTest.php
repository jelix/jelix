<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Tahina Ramaroson
* @contributor Sylvain de Vathaire, laurent Jouanneau
* @copyright   NEOV 2009, 2012 laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

/**
* Tests API driver LDAP for jAuth
* @package     testapp
* @subpackage  jelix_tests module
*/

define ("NB_USERS_LDAP",3);

class ldap_pluginAuthTest extends jUnitTestCase {

    protected $config;

    protected $listenersBackup;

    function setUp(){
        parent::setUp();
        if(!file_exists(jApp::appSystemPath().'auth_ldap.coord.ini.php')) {
            $this->config = null;
            $this->markTestSkipped('Ldap plugin for jauth is not tested because there isn\'t configuration.'.
                               ' To test it, you should create and configure an auth_ldap.coord.ini.php file.');
            return;
        }
        self::initClassicRequest(TESTAPP_URL.'index.php');
        jApp::pushCurrentModule('jelix_tests');

        $conf = parse_ini_file(jApp::appSystemPath().'auth_ldap.coord.ini.php',true, INI_SCANNER_TYPED);
        jAuth::loadConfig($conf);

        require_once( JELIX_LIB_PATH.'plugins/coord/auth/auth.coord.php');
        jApp::coord()->plugins['auth'] = new AuthCoordPlugin($conf);

        $this->config = & jApp::coord()->plugins['auth']->config;
        $_SESSION[$this->config['session_name']] = new jAuthDummyUser();

        // disable listener of jacl2db so testldap could be remove without
        // verifying if there is still an admin
        $this->listenersBackup = jApp::config()->disabledListeners;
        jApp::config()->disabledListeners['AuthCanRemoveUser'] = 'jacl2db~jacl2db';
        jEvent::clearCache();
        $cacheFile = jApp::tempPath('compiled/'.jApp::config()->urlengine['urlScriptId'].'.events.php');
        if (file_exists($cacheFile)) {
            unlink($cacheFile);
        }
    }

    function tearDown(){
        jApp::popCurrentModule();
        unset(jApp::coord()->plugins['auth']);
        unset($_SESSION[$this->config['session_name']]);
        $this->config = null;
        jAcl2DbUserGroup::removeUser('testldap');
        jApp::config()->disabledListeners = $this->listenersBackup;
        jEvent::clearCache();
        $cacheFile = jApp::tempPath('compiled/'.jApp::config()->urlengine['urlScriptId'].'.events.php');
        if (file_exists($cacheFile)) {
            unlink($cacheFile);
        }
    }

    public function testUsersList() {

        $myUsersLDAP = jAuth::getUserList('j*');

        $this->assertEquals(2, count($myUsersLDAP));
        $users="<array>
            <object>
                <string property=\"login\">john</string>
                <string property=\"email\">john@jelix.org</string>
                <string property=\"displayName\">John Doe</string>
                <string property=\"givenName\">John</string>
                <string property=\"password\" value=\"\" />
            </object>
            <object>
                <string property=\"login\">jane</string>
                <string property=\"email\">jane@jelix.org</string>
                <string property=\"displayName\">Jane Doe</string>
                <string property=\"givenName\">Jane</string>
                <string property=\"password\" value=\"\" />
            </object>
        </array>";

        $this->assertComplexIdenticalStr($myUsersLDAP, $users);
    }

    public function testLogin() {
        $this->assertFalse(jAuth::verifyPassword('john', 'wrongpass'));
        $user1 = jAuth::verifyPassword('john', 'passjohn');
        $this->assertNotFalse($user1);
        $userCheck="<object>
                <string property=\"login\">john</string>
                <string property=\"email\">john@jelix.org</string>
                <string property=\"displayName\">John Doe</string>
                <string property=\"givenName\">John</string>
                <string property=\"password\" value=\"\" />
            </object>";
        $this->assertComplexIdenticalStr($user1, $userCheck);

        $user1 = jAuth::verifyPassword('jane', 'passjane');
        $this->assertNotFalse($user1);
        $userCheck="<object>
                <string property=\"login\">jane</string>
                <string property=\"email\">jane@jelix.org</string>
                <string property=\"displayName\">Jane Doe</string>
                <string property=\"givenName\">Jane</string>
                <string property=\"password\" value=\"\" />
            </object>";
        $this->assertComplexIdenticalStr($user1, $userCheck);
    }

    public function testGetUser() {
        $user1 = jAuth::getUser('john');
        $this->assertNotFalse($user1);
        $userCheck="<object>
                <string property=\"login\">john</string>
                <string property=\"email\">john@jelix.org</string>
                <string property=\"displayName\">John Doe</string>
                <string property=\"givenName\">John</string>
                <string property=\"password\" value=\"\" />
            </object>";
        $this->assertComplexIdenticalStr($user1, $userCheck);
    }

    public function testCreateUpdateUser() {
        $myUser = jAuth::createUserObject("testldap", "passtest");
        $this->assertTrue($myUser instanceof jAuthUserLDAP);
        $myUser->displayName = 'test ldap';
        $myUser->email = 'test@jelix.org';
        $myUser->givenName = 'test';
        $myUser->cn = 'Test Ldap';
        $myUser->sn = 'testou';


        jAuth::saveNewUser($myUser);

        $myUserLDAP = jAuth::getUser("testldap");
        $user="
            <object class=\"jAuthUserLDAP\">
                <string property=\"login\">testldap</string>
                <string property=\"email\">test@jelix.org</string>
                <string property=\"displayName\">test ldap</string>
                <string property=\"givenName\">test</string>
                <string property=\"cn\">Test Ldap</string>
                <string property=\"sn\">testou</string>
                <string property=\"password\" value=\"\" />
            </object>
            ";
        $this->assertComplexIdenticalStr($myUserLDAP,$user);

        $myUserLDAP->email = "test2@jelix.org";
        jAuth::updateUser($myUserLDAP);

        $myUserLDAP = jAuth::getUser("testldap");
        $user="
            <object class=\"jAuthUserLDAP\">
                <string property=\"login\">testldap</string>
                <string property=\"email\">test2@jelix.org</string>
                <string property=\"displayName\">test ldap</string>
                <string property=\"givenName\">test</string>
                <string property=\"password\" value=\"\" />
                <string property=\"cn\">Test Ldap</string>
                <string property=\"sn\">testou</string>
            </object>
            ";
        $this->assertComplexIdenticalStr($myUserLDAP,$user);
    }

    /**
     * @depends testCreateUpdateUser
     */
    public function testChangePassword() {
        $this->assertNotFalse(jAuth::verifyPassword("testldap","passtest"));
        $this->assertTrue(jAuth::changePassword("testldap","newpass"));
        $this->assertNotFalse(jAuth::verifyPassword("testldap","newpass"));
    }

    /**
     * @depends testChangePassword
     */
    public function testDeleteUser() {
        $this->assertTrue(jAuth::removeUser("testldap"));
        $this->assertFalse(jAuth::verifyPassword("testldap","newpass"));
        $this->assertFalse(jAuth::getUser("testldap"));
    }

}
