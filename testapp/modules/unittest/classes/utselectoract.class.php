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

class UTEvents extends UnitTestCase {

    function testEvents() {
        $sels=array(
"module~ctrl_meth@truc"=>array('module','ctrl','meth','truc'),
"module~_meth@truc"=>array('module','ctrl','meth','truc'),
"module~ctrl_@truc"=>array('module','ctrl','meth','truc'),
"module~@truc"=>array('module','ctrl','meth','truc'),
"module~#@truc"=>array('module','ctrl','meth','truc'),
"module~ctrl_meth"=>array('module','ctrl','meth','truc'),
"module~_meth"=>array('module','ctrl','meth','truc'),
"module~ctrl_"=>array('module','ctrl','meth','truc'),
"module~"=>array('module','ctrl','meth','truc'),
"module~#"=>array('module','ctrl','meth','truc'),
"~ctrl_meth@truc"=>array('module','ctrl','meth','truc'),
"~_meth@truc"=>array('module','ctrl','meth','truc'),
"~ctrl_@truc"=>array('module','ctrl','meth','truc'),
"~@truc"=>array('module','ctrl','meth','truc'),
"~#@truc"=>array('module','ctrl','meth','truc'),
"~ctrl_meth"=>array('module','ctrl','meth','truc'),
"~_meth"=>array('module','ctrl','meth','truc'),
"~ctrl_"=>array('module','ctrl','meth','truc'),
"~"=>array('module','ctrl','meth','truc'),
"~#"=>array('module','ctrl','meth','truc'),
"#~ctrl_meth@truc"=>array('module','ctrl','meth','truc'),
"#~_meth@truc"=>array('module','ctrl','meth','truc'),
"#~ctrl_@truc"=>array('module','ctrl','meth','truc'),
"#~@truc"=>array('module','ctrl','meth','truc'),
"#~#@truc"=>array('module','ctrl','meth','truc'),
"#~ctrl_meth"=>array('module','ctrl','meth','truc'),
"#~_meth"=>array('module','ctrl','meth','truc'),
"#~ctrl_"=>array('module','ctrl','meth','truc'),
"#~"=>array('module','ctrl','meth','truc'),
"#~#"=>array('module','ctrl','meth','truc'),
"ctrl_meth@truc"=>array('module','ctrl','meth','truc'),
"_meth@truc"=>array('module','ctrl','meth','truc'),
"ctrl_@truc"=>array('module','ctrl','meth','truc'),
"@truc"=>array('module','ctrl','meth','truc'),
"#@truc"=>array('module','ctrl','meth','truc'),
"ctrl_meth"=>array('module','ctrl','meth','truc'),
"_meth"=>array('module','ctrl','meth','truc'),
"ctrl_"=>array('module','ctrl','meth','truc'),
""=>array('module','ctrl','meth','truc'),
"#"=>array('module','ctrl','meth','truc'),

);

        foreach($sels as $sel=>$res){
            $s = new jSelectorAct($sel);
            $this->assertTrue(($s->isValid() == true && $res !== false) || ( $s->isValid() == false && $res === false), ' test validité de '.$sel);
            if($s->isValid() &&  $res !== false){
                
            }
        }

         $temoin == $response, 'évnement simple');
         $this->assertTrue(($response[0]['params'] == 'world'), 'ï¿½enement avec paramï¿½res');
    }
}

?>