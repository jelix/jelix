<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Laurent Jouanneau
* @contributor
* @copyright   2006-2012 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

class selectors_actionTest extends \Jelix\UnitTests\UnitTestCase {

    function setUp() : void  {
        self::initClassicRequest(TESTAPP_URL.'index.php');
        jApp::pushCurrentModule('jelix_tests');
        parent::setUp();
    }
    
    function tearDown() : void  {
        jApp::popCurrentModule();
    }

    function testWithModule() {
        $sels=array(
"testapp~ctrl:meth@truc"=>array('testapp','ctrl','meth','truc'),
"testapp~ct_rl:me_th@truc"=>array('testapp','ct_rl','me_th','truc'),
"testapp~:meth@truc"=>array('testapp','default','meth','truc'),
"testapp~:me_th@truc"=>array('testapp','default','me_th','truc'),
"testapp~meth@truc"=>array('testapp','default','meth','truc'),
"testapp~ctrl:@truc"=>array('testapp','ctrl','index','truc'),
"testapp~@truc"=>array('testapp','default','index','truc'),
"testapp~#@truc"=>array('testapp',jApp::coord()->action->controller, jApp::coord()->action->method,'truc'),
"testapp~ctrl:meth"=>array('testapp','ctrl','meth','classic'),
"testapp~:meth"=>array('testapp','default','meth','classic'),
"testapp~meth"=>array('testapp','default','meth','classic'),
"testapp~ctrl:"=>array('testapp','ctrl','index','classic'),
"testapp~"=>array('testapp','default','index','classic'),
"testapp~#"=>array('testapp',jApp::coord()->action->controller, jApp::coord()->action->method,'classic'),
        );
        $this->launchtestsel($sels);
    }


    function testWithoutModule() {
        $sels=array(
"~ctrl:meth@truc"=>false,
"~_meth@truc"=>false,
"~ctrl:@truc"=>false,
"~@truc"=>false,
"~#@truc"=>false,
"~ctrl:meth"=>false,
"~:meth"=>false,
"me.th"=>false,
"~ctrl:"=>false,
"~"=>false,
"~#"=>false,
"a-b~toto"=>false,
"ab~ro-ro"=>false,
"#aaa"=>false,
"##"=>false,
"aa#aa"=>false,
"aaa#"=>false,
"foo~#aaa"=>false, 
"foo~aa#aa"=>false, 
"foo~aaa#"=>false, 
"~@classic"=>false,
"@"=>false,
"#@"=>false,
"aa.bb"=>false,
"aa~bb.cc"=>false,
        );
        $this->launchtestsel($sels);
    }



    function testWithModuleWildcard() {
        $mod = jApp::coord()->action->module;
        $sels=array(
"#~ctrl:meth@truc"=>array($mod,'ctrl','meth','truc'),
"#~:meth@truc"=>array($mod,'default','meth','truc'),
"#~meth@truc"=>array($mod,'default','meth','truc'),
"#~ctrl:@truc"=>array($mod,'ctrl','index','truc'),
"#~@truc"=>array($mod,'default','index','truc'),
"#~#@truc"=>array($mod,jApp::coord()->action->controller, jApp::coord()->action->method,'truc'),
"#~ctrl:meth"=>array($mod,'ctrl','meth','classic'),
"#~:meth"=>array($mod,'default','meth','classic'),
"#~meth"=>array($mod,'default','meth','classic'),
"#~ctrl:"=>array($mod,'ctrl','index','classic'),
"#~"=>array($mod,'default','index','classic'),
"#~#"=>array($mod,jApp::coord()->action->controller, jApp::coord()->action->method,'classic'),
        );
        $this->launchtestsel($sels);
    }

   function testMisc() {
        $sels=array(
"ctrl:meth@truc"=>array('jelix_tests','ctrl','meth','truc'),
":meth@truc"=>array('jelix_tests','default','meth','truc'),
"ctrl:@truc"=>array('jelix_tests','ctrl','index','truc'),
"@truc"=>array('jelix_tests','default','index','truc'),
"#@truc"=>array('jelix_tests',jApp::coord()->action->controller, jApp::coord()->action->method,'truc'),
"ctrl:meth"=>array('jelix_tests','ctrl','meth','classic'),
":meth"=>array('jelix_tests','default','meth','classic'),
"meth"=>array('jelix_tests','default','meth','classic'),
"ctrl:"=>array('jelix_tests','ctrl','index','classic'),
""=>array('jelix_tests','default','index','classic'),
"#"=>array('jelix_tests',jApp::coord()->action->controller, jApp::coord()->action->method,'classic'),
        );
        $this->launchtestsel($sels);
    }

    protected function launchtestsel($list){


        foreach($list as $sel=>$res){
            $valid=true;
            try{
                $s = new jSelectorAct($sel, true);
            }catch(jExceptionSelector $e){
                $valid=false;
            }
            $msg='';
            $ok = ($valid == true && $res !== false) || ( $valid == false && $res === false);
            if($valid &&  $res !== false){
                $ok = $ok
                && $s->module == $res[0]
                && $s->controller == $res[1]
                && $s->method == $res[2]
                && $s->request == $res[3];
                if(!$ok)
                    $msg=' contains unexpected data ('.$s->module.', '.$s->controller.', '.$s->method.', '.$s->request.')';
            }

            $this->assertTrue($ok , ' test of '.$sel. ' (should be '.($res === false ? 'invalid':'valid').')');
            if($msg)
                $this->sendMessage($msg);
        }

    }


    function testFastSel() {
        $list=array(

                array(
                        array('truc', 'testapp', 'ctrl:meth'),
                        array('testapp','ctrl','meth','truc'),
                ),
                array(
                        array('truc', 'testapp', ':meth'),
                        array('testapp','default','meth','truc'),
                ),
                array(
                        array('truc', 'testapp', 'meth'),
                        array('testapp','default','meth','truc'),
                ),
                array(
                        array('truc', 'testapp', 'ctrl:'),
                        array('testapp','ctrl','index','truc'),
                ),
                array(
                        array('truc', 'testapp', 'ctrl:'),
                        array('testapp','ctrl','index','truc'),
                ),
        );
        foreach($list as $test){
            list($sel, $res) = $test;
            $valid=true;
            try{
                $s = new jSelectorActFast($sel[0], $sel[1], $sel[2]);
            }catch(jExceptionSelector $e){
                $valid=false;
            }
            $msg='';
            if ($valid) {
                $valid = $valid 
                        && $s->module == $res[0]
                        && $s->controller == $res[1]
                        && $s->method == $res[2]
                        && $s->request == $res[3];
                if (!$valid)
                    $msg=' contains unexpected data ('.$s->module.', '.$s->controller.', '.$s->method.', '.$s->request.')';
            }

            $this->assertTrue($valid , ' test of '.$sel[1].'~'.$sel[2].'@'.$sel[2]. ' should be valid');
            if($msg)
                $this->sendMessage($msg);
        }
    }
}
