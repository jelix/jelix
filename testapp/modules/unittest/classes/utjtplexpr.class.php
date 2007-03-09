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

require_once(JELIX_LIB_TPL_PATH.'jTplCompiler.class.php');
require_once(dirname(__FILE__).'/junittestcase.class.php');

class testJtplCompiler extends jTplCompiler {

   public function testParseExpr($string, $allowed=array(), $exceptchar=array(';'), $splitArgIntoArray=false){
        return $this->_parseFinal($string, $allowed, $exceptchar, $splitArgIntoArray);
   }

   public function testParseVarExpr($string){
        return $this->_parseFinal($string,$this->_allowedInVar);
   }

   public function testParseForeachExpr($string){
        return $this->_parseFinal($string,$this->_allowedInForeach, array(';','!'));
   }

   public function testParseAnyExpr($string){
        return $this->_parseFinal($string, $this->_allowedInExpr, array());
   }

   public function testParseAssignExpr($string){
        return $this->_parseFinal($string,$this->_allowedAssign);
   }

   public function testParseAssignExpr2($string){
        return $this->_parseFinal($string,$this->_allowedAssign, array(';'),true);
   }
}




class UTjtplexpr extends jUnitTestCase {

    protected $varexpr = array(
        '$aa'=>'$t->_vars[\'aa\']',
    );

    protected $badvarexpr = array(
        '$'=>array('jelix~errors.tpl.tag.character.invalid',array('','$','')),
    );



    function testVarExpr() {
        $compil = new testJtplCompiler();
        foreach($this->varexpr as $k=>$t){
            //$this->sendMessage("test good datasource ".$k);
            try{
                $res = $compil->testParseVarExpr($k);
                $this->assertEqualOrDiff($res, $t);
            }catch(Exception $e){
                $this->fail("Test '$k', Exception inconnue : ".$e->getMessage());
            }
        }
    }

    function testBadVarExpr() {
        $compil = new testJtplCompiler();
        foreach($this->badvarexpr as $k=>$t){
            //$this->sendMessage("test good datasource ".$k);
            try{
                $res = $compil->testParseVarExpr($k);
                $this->fail("Exception non survenu pour le test '$k' : ".$e->getMessage());
            }catch(jException $e){
                $this->assertEqualOrDiff($e->getMessage(), $t[0]);
                $this->assertEqual($e->localeParams, $t[1]);
            }catch(Exception $e){
                $this->pass("Exception inconnue : ".$e->getMessage());
            }
        }
    }


}

?>