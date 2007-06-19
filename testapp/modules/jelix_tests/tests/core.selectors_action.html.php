<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Jouanneau Laurent
* @contributor
* @copyright   2006-2007 Jouanneau laurent
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

class UTSelectorAct extends UnitTestCase {

    function testWithModule() {
        $sels=array(
"testapp~ctrl_meth@truc"=>array('testapp','ctrl','meth','truc'),
"testapp~_meth@truc"=>array('testapp','default','meth','truc'),
"testapp~meth@truc"=>array('testapp','default','meth','truc'),
"testapp~ctrl_@truc"=>array('testapp','ctrl','index','truc'),
"testapp~@truc"=>array('testapp','default','index','truc'),
"testapp~#@truc"=>array('testapp',$GLOBALS['gJCoord']->action->controller, $GLOBALS['gJCoord']->action->method,'truc'),
"testapp~ctrl_meth"=>array('testapp','ctrl','meth','classic'),
"testapp~_meth"=>array('testapp','default','meth','classic'),
"testapp~meth"=>array('testapp','default','meth','classic'),
"testapp~ctrl_"=>array('testapp','ctrl','index','classic'),
"testapp~"=>array('testapp','default','index','classic'),
"testapp~#"=>array('testapp',$GLOBALS['gJCoord']->action->controller, $GLOBALS['gJCoord']->action->method,'classic'),
        );
        $this->runtest($sels);
    }


    function testWithoutModule() {
        $sels=array(
"~ctrl_meth@truc"=>false,
"~_meth@truc"=>false,
"~ctrl_@truc"=>false,
"~@truc"=>false,
"~#@truc"=>false,
"~ctrl_meth"=>false,
"~_meth"=>false,
"me.th"=>false,
"~ctrl_"=>false,
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
        $this->runtest($sels);
    }



    function testWithModuleWildcard() {
        $mod = $GLOBALS['gJCoord']->action->module;
        $sels=array(
"#~ctrl_meth@truc"=>array($mod,'ctrl','meth','truc'),
"#~_meth@truc"=>array($mod,'default','meth','truc'),
"#~meth@truc"=>array($mod,'default','meth','truc'),
"#~ctrl_@truc"=>array($mod,'ctrl','index','truc'),
"#~@truc"=>array($mod,'default','index','truc'),
"#~#@truc"=>array($mod,$GLOBALS['gJCoord']->action->controller, $GLOBALS['gJCoord']->action->method,'truc'),
"#~ctrl_meth"=>array($mod,'ctrl','meth','classic'),
"#~_meth"=>array($mod,'default','meth','classic'),
"#~meth"=>array($mod,'default','meth','classic'),
"#~ctrl_"=>array($mod,'ctrl','index','classic'),
"#~"=>array($mod,'default','index','classic'),
"#~#"=>array($mod,$GLOBALS['gJCoord']->action->controller, $GLOBALS['gJCoord']->action->method,'classic'),
        );
        $this->runtest($sels);
    }

   function testMisc() {
        $sels=array(
"ctrl_meth@truc"=>array('jelix_tests','ctrl','meth','truc'),
"_meth@truc"=>array('jelix_tests','default','meth','truc'),
"ctrl_@truc"=>array('jelix_tests','ctrl','index','truc'),
"@truc"=>array('jelix_tests','default','index','truc'),
"#@truc"=>array('jelix_tests',$GLOBALS['gJCoord']->action->controller, $GLOBALS['gJCoord']->action->method,'truc'),
"ctrl_meth"=>array('jelix_tests','ctrl','meth','classic'),
"_meth"=>array('jelix_tests','default','meth','classic'),
"meth"=>array('jelix_tests','default','meth','classic'),
"ctrl_"=>array('jelix_tests','ctrl','index','classic'),
""=>array('jelix_tests','default','index','classic'),
"#"=>array('jelix_tests',$GLOBALS['gJCoord']->action->controller, $GLOBALS['gJCoord']->action->method,'classic'),
        );
        $this->runtest($sels);
    }

    protected function runtest($list){


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
                    $msg=' contient ces données inattendues ('.$s->module.', '.$s->controller.', '.$s->method.', '.$s->request.')';
            }

            $this->assertTrue($ok , ' test de '.$sel. ' (devrait être '.($res === false ? 'invalide':'valide').')');
            if($msg)
                $this->sendMessage($msg);
        }

    }


    function testFastSel() {
        $list=array(

                array(
                        array('truc', 'testapp', 'ctrl_meth'),
                        array('testapp','ctrl','meth','truc'),
                ),
                array(
                        array('truc', 'testapp', '_meth'),
                        array('testapp','default','meth','truc'),
                ),
                array(
                        array('truc', 'testapp', 'meth'),
                        array('testapp','default','meth','truc'),
                ),
                array(
                        array('truc', 'testapp', 'ctrl_'),
                        array('testapp','ctrl','index','truc'),
                ),
                array(
                        array('truc', 'testapp', 'ctrl_'),
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
            if($valid){
                $valid = $valid 
                && $s->module == $res[0]
                && $s->controller == $res[1]
                && $s->method == $res[2]
                && $s->request == $res[3];
                if(!$valid)
                    $msg=' contient ces données inattendues ('.$s->module.', '.$s->controller.', '.$s->method.', '.$s->request.')';
            }

            $this->assertTrue($valid , ' test de '.$sel. ' (devrait être '.($res === false ? 'invalide':'valide').')');
            if($msg)
                $this->sendMessage($msg);
        }

    }


}

?>