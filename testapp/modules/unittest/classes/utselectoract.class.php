<?php
/**
* @package     testapp
* @subpackage  unittest module
* @version     $Id$
* @author      Jouanneau Laurent
* @contributor
* @copyright   2006 Jouanneau laurent
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

class UTSelectorAct extends UnitTestCase {

    function testWithModule() {
        $sels=array(
"testapp~ctrl_meth@truc"=>array('testapp','ctrl','meth','truc'),
"testapp~_meth@truc"=>array('testapp','default','meth','truc'),
"testapp~ctrl_@truc"=>array('testapp','ctrl','index','truc'),
"testapp~@truc"=>array('testapp','default','index','truc'),
"testapp~#@truc"=>array('testapp','default','testselectoract','truc'),
"testapp~ctrl_meth"=>array('testapp','ctrl','meth','classic'),
"testapp~_meth"=>array('testapp','default','meth','classic'),
"testapp~ctrl_"=>array('testapp','ctrl','index','classic'),
"testapp~"=>array('testapp','default','index','classic'),
"testapp~#"=>array('testapp','default','testselectoract','classic'),
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
"~ctrl_"=>false,
"~"=>false,
"~#"=>false,
        );
        $this->runtest($sels);
    }



    function testWithModuleWildcard() {
        $sels=array(
"#~ctrl_meth@truc"=>array('unittest','ctrl','meth','truc'),
"#~_meth@truc"=>array('unittest','default','meth','truc'),
"#~ctrl_@truc"=>array('unittest','ctrl','index','truc'),
"#~@truc"=>array('unittest','default','index','truc'),
"#~#@truc"=>array('unittest','default','testselectoract','truc'),
"#~ctrl_meth"=>array('unittest','ctrl','meth','classic'),
"#~_meth"=>array('unittest','default','meth','classic'),
"#~ctrl_"=>array('unittest','ctrl','index','classic'),
"#~"=>array('unittest','default','index','classic'),
"#~#"=>array('unittest','default','testselectoract','classic'),

        );
        $this->runtest($sels);
    }

   function testMisc() {
        $sels=array(
"ctrl_meth@truc"=>array('unittest','ctrl','meth','truc'),
"_meth@truc"=>array('unittest','default','meth','truc'),
"ctrl_@truc"=>array('unittest','ctrl','index','truc'),
"@truc"=>array('unittest','default','index','truc'),
"#@truc"=>array('unittest','default','testselectoract','truc'),
"ctrl_meth"=>array('unittest','ctrl','meth','classic'),
"_meth"=>array('unittest','default','meth','classic'),
"ctrl_"=>array('unittest','ctrl','index','classic'),
""=>array('unittest','default','index','classic'),
"#"=>array('unittest','default','testselectoract','classic'),
        );
        $this->runtest($sels);
    }


    protected function runtest($list){


        foreach($list as $sel=>$res){
            $valid=true;
            try{
                $s = new jSelectorAct($sel);
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

            $this->assertTrue($ok , ' test de '.$sel);
            if($msg)
                $this->sendMessage($msg);
        }

    }
}

?>