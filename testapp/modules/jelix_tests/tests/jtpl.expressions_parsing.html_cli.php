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

require_once(JELIX_LIB_TPL_PATH.'jTplCompiler.class.php');

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
        'a'=>'a',
        '"aaa"'=>'"aaa"',
        '123'=>'123',
        '  '=>'  ',
        '123.456'=>'123.456',
        '->'=>'->',
        "'aze'"=>"'aze'",
        '$aa'=>'$t->_vars[\'aa\']',
        '$aa.$bb'=>'$t->_vars[\'aa\'].$t->_vars[\'bb\']',
        '$aa."bbb"'=>'$t->_vars[\'aa\']."bbb"',
        '$aa+234'=>'$t->_vars[\'aa\']+234',
        '$aa-234'=>'$t->_vars[\'aa\']-234',
        '$aa*234'=>'$t->_vars[\'aa\']*234',
        '$aa/234'=>'$t->_vars[\'aa\']/234',
        '!$aa'=>'!$t->_vars[\'aa\']',
        'array($aa)'=>'array($t->_vars[\'aa\'])',
        ' array(   $aa  )   '=>' array(   $t->_vars[\'aa\']  )   ',
        ' array("e"=>$aa, "tt"=>987  )'=>' array("e"=>$t->_vars[\'aa\'], "tt"=>987  )',
        '@aa@'=>'jLocale::get(\'aa\')',
        '@aa.$ooo@'=>'jLocale::get(\'aa.\'.$t->_vars[\'ooo\'].\'\')',
        '@aa~bbb@'=>'jLocale::get(\'aa~bbb\')',
        '@aa~trc.$abcd.popo@'=>'jLocale::get(\'aa~trc.\'.$t->_vars[\'abcd\'].\'.popo\')',
        '@$aa~trc.$abcd.popo@'=>'jLocale::get(\'\'.$t->_vars[\'aa\'].\'~trc.\'.$t->_vars[\'abcd\'].\'.popo\')',
        '$aa.@trc.$abcd.popo@'=>'$t->_vars[\'aa\'].jLocale::get(\'trc.\'.$t->_vars[\'abcd\'].\'.popo\')',
        '@aa~trc.234.popo@'=>'jLocale::get(\'aa~trc.234.popo\')',
        '@aa~trc.23.4.popo@'=>'jLocale::get(\'aa~trc.23.4.popo\')',
        '$aa*count($bb)'=>'$t->_vars[\'aa\']*count($t->_vars[\'bb\'])',
        'isset($t[5])'=>'isset($t->_vars[\'t\'][5])',
        '$aa && $bb'=>'$t->_vars[\'aa\'] && $t->_vars[\'bb\']',
        '$aa || $bb'=>'$t->_vars[\'aa\'] || $t->_vars[\'bb\']',
        '$aa & $bb'=>'$t->_vars[\'aa\'] & $t->_vars[\'bb\']',
        '$aa | $bb'=>'$t->_vars[\'aa\'] | $t->_vars[\'bb\']',
        '$aa and $bb'=>'$t->_vars[\'aa\'] and $t->_vars[\'bb\']',
        '$aa or $bb'=>'$t->_vars[\'aa\'] or $t->_vars[\'bb\']',
        '$aa xor $bb'=>'$t->_vars[\'aa\'] xor $t->_vars[\'bb\']',
        'empty($aa)'=>'empty($t->_vars[\'aa\'])',
        '$aa++'=>'$t->_vars[\'aa\']++',
        '$aa--'=>'$t->_vars[\'aa\']--',
        '$aa == 123'=>'$t->_vars[\'aa\'] == 123',
        '$aa != 123'=>'$t->_vars[\'aa\'] != 123',
        '$aa >= 123'=>'$t->_vars[\'aa\'] >= 123',
        '$aa !== 123'=>'$t->_vars[\'aa\'] !== 123',
        '$aa <= 123'=>'$t->_vars[\'aa\'] <= 123',
        '$aa << 123'=>'$t->_vars[\'aa\'] << 123',
        '$aa >> 123'=>'$t->_vars[\'aa\'] >> 123',
        '$bb->bar'=>'$t->_vars[\'bb\']->bar',
        '@abstract.as.break.case.catch.class.clone@'=>'jLocale::get(\'abstract.as.break.case.catch.class.clone\')',
        '@const.continue.declare.default.do.echo.else.elseif.empty@'=>'jLocale::get(\'const.continue.declare.default.do.echo.else.elseif.empty\')',
        '@exit.final.for.foreach.function.global.if.implements.instanceof@'=>'jLocale::get(\'exit.final.for.foreach.function.global.if.implements.instanceof\')',
        '@interface.and.or.xor.new.private.public@'=>'jLocale::get(\'interface.and.or.xor.new.private.public\')',
        '@protected.return.static.switch.throw.try.use.var.while@'=>'jLocale::get(\'protected.return.static.switch.throw.try.use.var.while\')',
        '$aa*(234+$b)'=>'$t->_vars[\'aa\']*(234+$t->_vars[\'b\'])',
        '$aa[$bb[4]]'=>'$t->_vars[\'aa\'][$t->_vars[\'bb\'][4]]',
        '$aa == false'=>'$t->_vars[\'aa\'] == false',
        '$aa == true'=>'$t->_vars[\'aa\'] == true',
        '$aa == null'=>'$t->_vars[\'aa\'] == null',

    );

    protected $badvarexpr = array(
        '$'=>array('jelix~errors.tpl.tag.character.invalid',array('','$','')),
        'foreach($a)'=>array('jelix~errors.tpl.tag.phpsyntax.invalid',array('','foreach','')),
        '@aaa.bbb'=>array('jelix~errors.tpl.tag.locale.end.missing',array('','')),
        '$aaa.PHP_VERSION'=>array('jelix~errors.tpl.tag.constant.notallowed',array('','PHP_VERSION','')),
        '@aaa.b,bb@'=>array('jelix~errors.tpl.tag.character.invalid',array('',',','')),
        '@@'=>array('jelix~errors.tpl.tag.locale.invalid',array('','')),
        '[$aa/234]'=>array('jelix~errors.tpl.tag.character.invalid',array('','[','')),
        '($aa/234)'=>array('jelix~errors.tpl.tag.character.invalid',array('','(','')),
        '$b+($aa/234'=>array('jelix~errors.tpl.tag.bracket.error',array('','')),
        '$b+(($aa/234)'=>array('jelix~errors.tpl.tag.bracket.error',array('','')),
        '$aa/234)'=>array('jelix~errors.tpl.tag.bracket.error',array('','')),
        '$aa/234))'=>array('jelix~errors.tpl.tag.bracket.error',array('','')),
        '$aa[234'=>array('jelix~errors.tpl.tag.bracket.error',array('','')),
        '$aa[[234]'=>array('jelix~errors.tpl.tag.bracket.error',array('','')),
        '$aa234]'=>array('jelix~errors.tpl.tag.bracket.error',array('','')),
    );

    function testVarExpr() {
        $compil = new testJtplCompiler();
        foreach($this->varexpr as $k=>$t){
            //$this->sendMessage("test good datasource ".$k);
            try{
                $res = $compil->testParseVarExpr($k);
                $this->assertEqualOrDiff($t, $res);
            }catch(jException $e){
                $this->fail("Test '$k', Exception jelix inconnue : ".$e->getMessage().' ('.$e->getLocaleKey().')');
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
                $this->fail("Exception non survenu pour le test '$k' ");
            }catch(jException $e){
                //$this->sendMessage($e->getMessage());
                $this->assertEqualOrDiff($t[0], $e->getLocaleKey());
                $this->assertEqual($t[1], $e->getLocaleParameters());
            }catch(Exception $e){
                $this->pass("Exception inconnue : ".$e->getMessage());
            }
        }
    }


}

?>