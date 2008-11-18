<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Jouanneau Laurent
* @contributor
* @copyright   2007 Jouanneau laurent
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

class UTjDb_profile extends jUnitTestCase {

    function testProfile() {
        $p = jDb::getProfile('jelix_tests_mysql');
        $result= array(
            'driver'=>"mysql",
            'database'=>"testapp_mysql",
            'host'=> "localhost_mysql",
            'user'=> "plop_mysql",
            'password'=> "futchball_mysql",
            'persistent'=> '1',
            'force_encoding'=>1,
            'name'=>'jelix_tests_mysql',
        );

        $this->assertEqual($p, $result);

        $p = jDb::getProfile('forward',true);
        $result= array(
            'driver'=>"mysql",
            'database'=>"jelix_tests_forward",
            'host'=> "localhost_forward",
            'user'=> "plop_forward",
            'password'=> "futchball_forward",
            'persistent'=> '1',
            'force_encoding'=>0,
            'name'=>'jelix_tests_forward',
        );

        $this->assertEqual($p, $result);

        $p = jDb::getProfile('testapp');
        $this->assertEqual($p['name'], 'testapp');
        $p = jDb::getProfile();
        $this->assertEqual($p['name'], 'testapp');
        $p = jDb::getProfile('testapppdo');
        $this->assertEqual($p['name'], 'testapppdo');
    }

    function testVirtualProfile() {
        $profile = array(
            'driver'=>"mysql",
            'database'=>"virtual_mysql",
            'host'=> "localhostv_mysql",
            'user'=> "v_mysql",
            'password'=> "vir_mysql",
            'persistent'=> '1',
            'force_encoding'=>1
        );

        jDb::createVirtualProfile('foobar', $profile);

        $p = jDb::getProfile('foobar');
        $profile['name'] = 'foobar';

        $this->assertEqual($profile, $p);
    }


    function testBadProfile(){
        try {
            $p = jDb::getProfile('abcdef'); // unknow profile
            $this->fail('getting a wrong profile doesn\'t generate an exception');
        }catch(jException $e){
            $this->assertEqual($e->getLocaleKey(),'jelix~db.error.profile.unknow', 'wrong exception on getting a wrong profile ('.$e->getLocaleKey().')');
        }

        try {
            $p = jDb::getProfile('abcdef', true); // unknow profile option
            $this->fail('getting a wrong profile option doesn\'t generate an exception');
        }catch(jException $e){
            $this->assertEqual($e->getLocaleKey(),'jelix~db.error.profile.type.unknow', 'wrong exception on getting a wrong profile option ('.$e->getLocaleKey().')');
        }

        try {
            $p = jDb::getProfile('wrong_profilname', true); // unknow profile name
            $this->fail('getting a profile option with a wrong name doesn\'t generate an exception');
        }catch(jException $e){
            $this->assertEqual($e->getLocaleKey(),'jelix~db.error.profile.unknow', 'wrong exception on getting a profile option with a wrong name ('.$e->getLocaleKey().')');
        }
    }
}


?>