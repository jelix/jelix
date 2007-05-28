<?php
/**
* @package     testapp
* @subpackage  unittest module
* @author      Jouanneau Laurent
* @contributor
* @copyright   2007 Jouanneau laurent
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/
require_once(dirname(__FILE__).'/junittestcase.class.php');

class UTjDb_profile extends jUnitTestCase {

    function testProfil() {
        $p = jDb::getProfil('unittest_mysql');
        $result= array(
            'driver'=>"mysql",
            'database'=>"testapp_mysql",
            'host'=> "localhost_mysql",
            'user'=> "plop_mysql",
            'password'=> "futchball_mysql",
            'persistent'=> '1',
            'force_encoding'=>1,
            'name'=>'unittest_mysql',
        );

        $this->assertEqual($p, $result);

        $p = jDb::getProfil('forward',true);
        $result= array(
            'driver'=>"mysql",
            'database'=>"unittest_forward",
            'host'=> "localhost_forward",
            'user'=> "plop_forward",
            'password'=> "futchball_forward",
            'persistent'=> '1',
            'force_encoding'=>0,
            'name'=>'unittest_forward',
        );

        $this->assertEqual($p, $result);

        $p = jDb::getProfil('testapp');
        $this->assertEqual($p['name'], 'testapp');
        $p = jDb::getProfil();
        $this->assertEqual($p['name'], 'testapp');
        $p = jDb::getProfil('testapppdo');
        $this->assertEqual($p['name'], 'testapppdo');
    }

    function testBadProfil(){
        try {
            $p = jDb::getProfil('abcdef'); // unknow profil
            $this->fail('getting a wrong profil doesn\'t generate an exception');
        }catch(jException $e){
            $this->assertEqual($e->getMessage(),'jelix~db.error.profil.unknow', 'wrong exception on getting a wrong profil ('.$e->getMessage().')');
        }

        try {
            $p = jDb::getProfil('abcdef', true); // unknow profil option
            $this->fail('getting a wrong profil option doesn\'t generate an exception');
        }catch(jException $e){
            $this->assertEqual($e->getMessage(),'jelix~db.error.profil.type.unknow', 'wrong exception on getting a wrong profil option ('.$e->getMessage().')');
        }

        try {
            $p = jDb::getProfil('wrong_profilname', true); // unknow profil name
            $this->fail('getting a profil option with a wrong name doesn\'t generate an exception');
        }catch(jException $e){
            $this->assertEqual($e->getMessage(),'jelix~db.error.profil.unknow', 'wrong exception on getting a profil option with a wrong name ('.$e->getMessage().')');
        }
    }
}


?>