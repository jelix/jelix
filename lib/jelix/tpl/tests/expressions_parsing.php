<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Laurent Jouanneau
* @copyright   2007-2008 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

define('TEST_JTPL_COMPILER_ASSIGN',1);

class testJtplCompiler extends jTplCompiler {

    public function setUserPlugins($userModifiers, $userFunctions) {
        $this->_modifier = array_merge($this->_modifier, $userModifiers);
        $this->_userFunctions = $userFunctions;
    }


   public function testParseExpr($string, $allowed=array(), $exceptchar=array(';'), $splitArgIntoArray=false){
        return $this->_parseFinal($string, $allowed, $exceptchar, $splitArgIntoArray);
   }

   public function testParseVarExpr($string){
        return $this->_parseFinal($string,$this->_allowedInVar, $this->_excludedInVar);
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

   public function testParseVariable($string){
        return $this->_parseVariable($string);
   }

}

function testjtplcontentUserModifier($s){}


class UTjtplexpr extends jUnitTestCase {

    protected $varexpr = array(
        'a'=>'a',
        '"aaa"'=>'"aaa"',
        '123'=>'123',
        '  '=>' ',
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
        ' array(   $aa  )   '=>' array( $t->_vars[\'aa\'] ) ',
        ' array("e"=>$aa, "tt"=>987  )'=>' array("e"=>$t->_vars[\'aa\'], "tt"=>987 )',
        '@aa@'=>'$t->getLocaleString(\'aa\')',
        '@aa.$ooo@'=>'$t->getLocaleString(\'aa.\'.$t->_vars[\'ooo\'].\'\')',
        '@aa~bbb@'=>'$t->getLocaleString(\'aa~bbb\')',
        '@aa~trc.$abcd.popo@'=>'$t->getLocaleString(\'aa~trc.\'.$t->_vars[\'abcd\'].\'.popo\')',
        '@$aa~trc.$abcd.popo@'=>'$t->getLocaleString(\'\'.$t->_vars[\'aa\'].\'~trc.\'.$t->_vars[\'abcd\'].\'.popo\')',
        '$aa.@trc.$abcd.popo@'=>'$t->_vars[\'aa\'].$t->getLocaleString(\'trc.\'.$t->_vars[\'abcd\'].\'.popo\')',
        '@aa~trc.234.popo@'=>'$t->getLocaleString(\'aa~trc.234.popo\')',
        '@aa~trc.23.4.popo@'=>'$t->getLocaleString(\'aa~trc.23.4.popo\')',
        '@aa~trc.23.4.list@'=>'$t->getLocaleString(\'aa~trc.23.4.list\')',
        '$aa*count($bb)'=>'$t->_vars[\'aa\']*count($t->_vars[\'bb\'])',
        '$aa & $bb'=>'$t->_vars[\'aa\'] & $t->_vars[\'bb\']',
        '$aa | $bb'=>'$t->_vars[\'aa\'] | $t->_vars[\'bb\']',
        '$aa++'=>'$t->_vars[\'aa\']++',
        '$aa--'=>'$t->_vars[\'aa\']--',
        '$bb->bar'=>'$t->_vars[\'bb\']->bar',
        '$bb->$bar'=>'$t->_vars[\'bb\']->{$t->_vars[\'bar\']}',
        '$bb->$bar->yo'=>'$t->_vars[\'bb\']->{$t->_vars[\'bar\']}->yo',
        '@abstract.as.break.case.catch.class.clone@'=>'$t->getLocaleString(\'abstract.as.break.case.catch.class.clone\')',
        '@const.continue.declare.default.do.echo.else.elseif.empty@'=>'$t->getLocaleString(\'const.continue.declare.default.do.echo.else.elseif.empty\')',
        '@exit.final.for.foreach.function.global.if.implements.instanceof@'=>'$t->getLocaleString(\'exit.final.for.foreach.function.global.if.implements.instanceof\')',
        '@interface.and.or.xor.new.private.public@'=>'$t->getLocaleString(\'interface.and.or.xor.new.private.public\')',
        '@protected.return.static.switch.throw.try.use.var.eval.while@'=>'$t->getLocaleString(\'protected.return.static.switch.throw.try.use.var.eval.while\')',
        '$aa*(234+$b)'=>'$t->_vars[\'aa\']*(234+$t->_vars[\'b\'])',
        '$aa[$bb[4]]'=>'$t->_vars[\'aa\'][$t->_vars[\'bb\'][4]]',
    );

    protected $varexprTrustedMode = array(
        '$aaa.PHP_VERSION'=>'$t->_vars[\'aaa\'].PHP_VERSION',
    );

    protected $varexprUnTrustedMode = array(

    );


    function testVarExprTrustedMode() {
        $compil = new testJtplCompiler();
        $compil->trusted = true;
        foreach($this->varexpr as $k=>$t){
            try{
                $res = $compil->testParseVarExpr($k);
                $this->assertEqualOrDiff($t, $res);
            }catch(Exception $e){
                $this->fail("Test '$k', Unknown Exception: ".$e->getMessage());
            }
        }
        foreach($this->varexprTrustedMode as $k=>$t){
            try{
                $res = $compil->testParseVarExpr($k);
                $this->assertEqualOrDiff($t, $res);
            }catch(Exception $e){
                $this->fail("Test '$k', Unknown Exception: ".$e->getMessage());
            }
        }
    }

    function testVarExprUnTrustedMode() {
        $compil = new testJtplCompiler();
        $compil->trusted = false;
        foreach($this->varexpr as $k=>$t){
            try{
                $res = $compil->testParseVarExpr($k);
                $this->assertEqualOrDiff($t, $res);
            }catch(Exception $e){
                $this->fail("Test '$k', Unknown Exception: ".$e->getMessage());
            }
        }
        foreach($this->varexprUnTrustedMode as $k=>$t){
            try{
                $res = $compil->testParseVarExpr($k);
                $this->assertEqualOrDiff($t, $res);
            }catch(Exception $e){
                $this->fail("Test '$k', Unknown Exception: ".$e->getMessage());
            }
        }
    }

    protected $badvarexpr = array(
        '$'             =>array('Dans le tag  du template , le caractère  $ n\'est pas autorisé'),
        'foreach($a)'   =>array('Dans le tag  du template , le code php foreach n\'est pas autorisé'),
        '@aaa.bbb'      =>array('Dans le tag  du template , il manque la fin de la clef de localisation'),
        '@aaa.b,bb@'    =>array('Dans le tag  du template , le caractère  , n\'est pas autorisé'),
        '@@'            =>array('Dans le tag  du template , clef de localisation vide'),
        '[$aa/234]'     =>array('Dans le tag  du template , le caractère  [ n\'est pas autorisé'),
        '$b+($aa/234'   =>array('Dans le tag  du template , il y a des erreurs au niveau des parenthèses'),
        '$b+(($aa/234)' =>array('Dans le tag  du template , il y a des erreurs au niveau des parenthèses'),
        '$aa/234)'      =>array('Dans le tag  du template , il y a des erreurs au niveau des parenthèses'),
        '$aa/234))'     =>array('Dans le tag  du template , il y a des erreurs au niveau des parenthèses'),
        '$aa[234'       =>array('Dans le tag  du template , il y a des erreurs au niveau des parenthèses'),
        '$aa[[234]'     =>array('Dans le tag  du template , il y a des erreurs au niveau des parenthèses'),
        '$aa234]'       =>array('Dans le tag  du template , il y a des erreurs au niveau des parenthèses'),
        'isset($t[5])'  =>array('Dans le tag  du template , le code php isset n\'est pas autorisé'),
        'empty($aa)'    =>array('Dans le tag  du template , le code php empty n\'est pas autorisé'),
        '$aa == 123'    =>array('Dans le tag  du template , le code php == n\'est pas autorisé'),
        '$aa != 123'    =>array('Dans le tag  du template , le code php != n\'est pas autorisé'),
        '$aa >= 123'    =>array('Dans le tag  du template , le code php >= n\'est pas autorisé'),
        '$aa !== 123'   =>array('Dans le tag  du template , le code php !== n\'est pas autorisé'),
        '$aa <= 123'    =>array('Dans le tag  du template , le code php <= n\'est pas autorisé'),
        '$aa << 123'    =>array('Dans le tag  du template , le code php << n\'est pas autorisé'),
        '$aa >> 123'    =>array('Dans le tag  du template , le code php >> n\'est pas autorisé'),
        '$aa == false'  =>array('Dans le tag  du template , le code php == n\'est pas autorisé'),
        '$aa == true'   =>array('Dans le tag  du template , le code php == n\'est pas autorisé'),
        '$aa == null'   =>array('Dans le tag  du template , le code php == n\'est pas autorisé'),
        '$aa && $bb'    =>array('Dans le tag  du template , le code php && n\'est pas autorisé'),
        '$aa || $bb'    =>array('Dans le tag  du template , le code php || n\'est pas autorisé'),
        '$aa and $bb'   =>array('Dans le tag  du template , le code php and n\'est pas autorisé'),
        '$aa or $bb'    =>array('Dans le tag  du template , le code php or n\'est pas autorisé'),
        '$aa xor $bb'   =>array('Dans le tag  du template , le code php xor n\'est pas autorisé'),
        '$aa=$bb'       =>array('Dans le tag  du template , le caractère  = n\'est pas autorisé'),
        '$aa+=$bb'      =>array('Dans le tag  du template , le code php += n\'est pas autorisé'),
        '$aa-=$bb'      =>array('Dans le tag  du template , le code php -= n\'est pas autorisé'),
        '$aa/=$bb'      =>array('Dans le tag  du template , le code php /= n\'est pas autorisé'),
        '$aa*=$bb'      =>array('Dans le tag  du template , le code php *= n\'est pas autorisé'),
    );

    protected $badvarexprTrustedMode = array(

    );

    protected $badvarexprUnTrustedMode = array(
        '$aaa.PHP_VERSION'=>array('Dans le tag  du template , les constantes (PHP_VERSION) sont interdites'),
    );

    function testBadVarExprTrustedMode() {
        $compil = new testJtplCompiler();
        $compil->trusted = true;
        foreach($this->badvarexpr as $k=>$t){
            try{
                $res = $compil->testParseVarExpr($k);
                $this->fail("No Exception for this test '$k' ");
            }catch(Exception $e){
                $this->assertEqualOrDiff($t[0], $e->getMessage());
            }
        }
        foreach($this->badvarexprTrustedMode as $k=>$t){
            try{
                $res = $compil->testParseVarExpr($k);
                $this->fail("No Exception for this test '$k' ");
            }catch(Exception $e){
                $this->assertEqualOrDiff($t[0], $e->getMessage());
            }
        }
    }

    function testBadVarExprUnTrustedMode() {
        $compil = new testJtplCompiler();
        $compil->trusted = false;
        foreach($this->badvarexpr as $k=>$t){
            try{
                $res = $compil->testParseVarExpr($k);
                $this->fail("No Exception for this test '$k' ");
            }catch(Exception $e){
                $this->assertEqualOrDiff($t[0], $e->getMessage());
            }
        }
        foreach($this->badvarexprUnTrustedMode as $k=>$t){
            try{
                $res = $compil->testParseVarExpr($k);
                $this->fail("No Exception for this test '$k' ");
            }catch(Exception $e){
                $this->assertEqualOrDiff($t[0], $e->getMessage());
            }
        }
    }

    protected $varTag = array(
        '$aaa|escxml' => 'htmlspecialchars($t->_vars[\'aaa\'])',
        '$aaa|bla'=>'testjtplcontentUserModifier($t->_vars[\'aaa\'])',
    );


    function testVarTag() {
        $compil = new testJtplCompiler();
        $compil->trusted = true;
        $compil->setUserPlugins(array('bla'=>'testjtplcontentUserModifier'),array());

        foreach($this->varTag as $k=>$t){
            try{
                $res = $compil->testParseVariable($k);
                $this->assertEqualOrDiff($t, $res);
            }catch(Exception $e){
                $this->fail("Test '$k', Unknown Exception: ".$e->getMessage());
            }
        }
    }


    protected $varAssign = array(
        '$aa=$bb'=>'$t->_vars[\'aa\']=$t->_vars[\'bb\']',
        '$aa+=$bb'=>'$t->_vars[\'aa\']+=$t->_vars[\'bb\']',
        '$aa-=$bb'=>'$t->_vars[\'aa\']-=$t->_vars[\'bb\']',
        '$aa/=$bb'=>'$t->_vars[\'aa\']/=$t->_vars[\'bb\']',
        '$aa*=$bb'=>'$t->_vars[\'aa\']*=$t->_vars[\'bb\']',
        'TEST_JTPL_COMPILER_ASSIGN'=>'TEST_JTPL_COMPILER_ASSIGN'
    );

    protected $varAssignUnTrustedMode = array(
        'TEST_JTPL_COMPILER_ASSIGN'=>array('Dans le tag  du template , les constantes (TEST_JTPL_COMPILER_ASSIGN) sont interdites'),
    );


    function testAssign() {
        $compil = new testJtplCompiler();
        $compil->trusted = true;

        foreach($this->varAssign as $k=>$t){
            try{
                $res = $compil->testParseAssignExpr($k);
                $this->assertEqualOrDiff($t, $res);
            }catch(Exception $e){
                $this->fail("Test '$k', Unknown Exception: ".$e->getMessage());
            }
        }

        $compil->trusted = false;

        foreach($this->varAssignUnTrustedMode as $k=>$t){
            try{
                $res = $compil->testParseAssignExpr($k);
                $this->fail("No Exception for this test '$k' ");
            }catch(Exception $e){
                $this->assertEqualOrDiff($t[0], $e->getMessage());
            }

        }
    }
}

