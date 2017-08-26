<?php
/**
* @package     jelix
* @subpackage  jtpl_tests
* @author      Laurent Jouanneau
* @copyright   2007 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

require_once('../jTplCompiler.class.php');

class testJtplContentCompiler extends jTplCompiler {

    public function setUserPlugins($userModifiers, $userFunctions) {
        $this->_modifier = array_merge($this->_modifier, $userModifiers);
        $this->_userFunctions = $userFunctions;
    }

    public function compileContent2($content){
        return $this->compileContent($content);
    }

    public function setRemoveASPTags($b) {
        $this->removeASPtags = $b;
    }
    
}

function testjtplcontentUserFunction($t,$a,$b) {

}


class UTjtplcontent extends jUnitTestCase {

    protected $content = array(
0=>array(
        '',
        '',
        ),
1=>array(
        '<p>ok</p>',
        '<p>ok</p>',
        ),
2=>array(
        '<p>ok<?php echo $toto ?></p>',
        '<p>ok</p>',
        ),
3=>array(
        '<p>ok</p>
<script>{literal}
function toto() {
}
{/literal}
</script>
<p>ko</p>',
        '<p>ok</p>
<script>
function toto() {
}

</script>
<p>ko</p>',
        ),
4=>array(
        '<p>ok {* toto $toto *}</p>',
        '<p>ok </p>',
        ),

5=>array(
        '<p>ok {* toto

 $toto *}</p>',
        '<p>ok </p>',
        ),

6=>array(
        '<p>ok {* toto
{$toto} *}</p>',
        '<p>ok </p>',
        ),
7=>array(
        '<p>ok {* toto
{$toto} *}</p> {* hello *}',
        '<p>ok </p> ',
        ),
8=>array(
        '<p>ok {* {if $a == "a"}aaa{/if} *}</p>',
        '<p>ok </p>',
        ),
9=>array(
        '<p>ok<? echo $toto ?></p>',
        '<p>ok</p>',
        ),
10=>array(
        '<p>ok<?= $toto ?></p>',
        '<p>ok</p>',
        ),
11=>array(
        '<p>ok{if $foo} {/if}</p>',
        '<p>ok<?php if($t->_vars[\'foo\']):?> <?php endif;?></p>',
        ),
12=>array(
        '<p>ok{if ($foo)} {/if}</p>',
        '<p>ok<?php if(($t->_vars[\'foo\'])):?> <?php endif;?></p>',
        ),
13=>array(
        '<p>ok{while ($foo)} {/while}</p>',
        '<p>ok<?php while(($t->_vars[\'foo\'])):?> <?php endwhile;?></p>',
        ),
14=>array(
        '<p>ok{while $foo} {/while}</p>',
        '<p>ok<?php while($t->_vars[\'foo\']):?> <?php endwhile;?></p>',
        ),
17=>array(
        '<p>ok{$foo.($truc.$bbb)}</p>',
        '<p>ok<?php echo $t->_vars[\'foo\'].($t->_vars[\'truc\'].$t->_vars[\'bbb\']); ?></p>',
        ),
18=>array(
        '<p>ok{if ($foo || $bar) && $baz} {/if}</p>',
        '<p>ok<?php if(($t->_vars[\'foo\'] || $t->_vars[\'bar\']) && $t->_vars[\'baz\']):?> <?php endif;?></p>',
        ),
19=>array(
        '<p>ok{bla $foo, $params}</p>',
        '<p>ok<?php testjtplcontentUserFunction( $t,$t->_vars[\'foo\'], $t->_vars[\'params\']);?></p>',
        ),
20=>array('{for ($i=0;$i<$p;$i++)} A {/for}',
          '<?php for($t->_vars[\'i\']=0;$t->_vars[\'i\']<$t->_vars[\'p\'];$t->_vars[\'i\']++):?> A <?php endfor;?>'
         ),
21=>array('{for $i=0;$i<$p;$i++} A {/for}',
          '<?php for($t->_vars[\'i\']=0;$t->_vars[\'i\']<$t->_vars[\'p\'];$t->_vars[\'i\']++):?> A <?php endfor;?>'
         ),
22=>array('{for $i=count($o);$i<$p;$i++} A {/for}',
          '<?php for($t->_vars[\'i\']=count($t->_vars[\'o\']);$t->_vars[\'i\']<$t->_vars[\'p\'];$t->_vars[\'i\']++):?> A <?php endfor;?>'
         ),
23=>array(
        '<p>ok {const $foo}</p>',
        '<p>ok <?php echo htmlspecialchars(constant($t->_vars[\'foo\']));?></p>',
        ),
24=>array(
        '<p>ok{=$foo.($truc.$bbb)}</p>',
        '<p>ok<?php echo $t->_vars[\'foo\'].($t->_vars[\'truc\'].$t->_vars[\'bbb\']); ?></p>',
        ),
25=>array(
        '<p>ok{=intval($foo.($truc.$bbb))}</p>',
        '<p>ok<?php echo intval($t->_vars[\'foo\'].($t->_vars[\'truc\'].$t->_vars[\'bbb\'])); ?></p>',
        ),
26=>array(
        '<p>ok<? echo $toto ?></p>',
        '<p>ok</p>',
        ),
27=>array(
        '<p>ok<?
 echo $toto ?></p>',
        '<p>ok</p>',
        ),
28=>array(
        '<p>ok<?=$toto ?></p>',
        '<p>ok</p>',
        ),
29=>array(
        '<p>ok<?xml echo $toto ?></p>',
        '<p>ok<?xml echo $toto ?></p>',
        ),
30=>array(
        '<p>ok<?browser echo $toto ?></p>',
        '<p>ok<?browser echo $toto ?></p>',
        ),
31=>array(
        '<p>ok<?php
 echo $toto ?></p>',
        '<p>ok</p>',
        ),


/*26=>array(
        '',
        '',
        ),
27=>array(
        '',
        '',
        ),*/
    );

    function testCompileContent() {
        $compil = new testJtplContentCompiler();
        $compil->outputType = 'html';
        $compil->trusted = true;
        $compil->setUserPlugins(array(), array('bla'=>'testjtplcontentUserFunction'));
        $compil->setRemoveASPtags(false);
        
        foreach($this->content as $k=>$t){
            try{
                $this->assertEqualOrDiff($t[1], $compil->compileContent2($t[0]));
            }catch(Exception $e){
                $this->fail("Test '$k', Unknown Exception: ".$e->getMessage());
            }
        }

        $compil->setRemoveASPtags(false);
        $this->assertEqualOrDiff('<p>ok<?php echo \'<?xml version="truc"?>\'?></p>', $compil->compileContent2('<p>ok<?xml version="truc"?></p>'));
        $this->assertEqualOrDiff('<p>ok<?php echo \'<?xml version=\\\'truc\\\'?>\'?></p>', $compil->compileContent2('<p>ok<?xml version=\'truc\'?></p>'));
        $this->assertEqualOrDiff('<p>ok<?php echo \'<?xml
  version="truc"?>\'?></p>', $compil->compileContent2('<p>ok<?xml
  version="truc"?></p>'));
        $this->assertEqualOrDiff('<p>ok<%=$truc%></p>', $compil->compileContent2('<p>ok<%=$truc%></p>'));
        $compil->setRemoveASPtags(true);
        $this->assertEqualOrDiff('<p>ok</p>', $compil->compileContent2('<p>ok<%=$truc%></p>'));
    }

    protected $contentUntrusted = array(
0=>array('{for ($i=0;$i<$p;$i++)} A {/for}',
          '<?php for($t->_vars[\'i\']=0;$t->_vars[\'i\']<$t->_vars[\'p\'];$t->_vars[\'i\']++):?> A <?php endfor;?>'
         ),
1=>array('{for $i=0;$i<$p;$i++} A {/for}',
          '<?php for($t->_vars[\'i\']=0;$t->_vars[\'i\']<$t->_vars[\'p\'];$t->_vars[\'i\']++):?> A <?php endfor;?>'
         ),
    );
    
    function testCompileContentUntrusted() {
        $compil = new testJtplContentCompiler();
        $compil->outputType = 'html';
        $compil->trusted = false;
        $compil->setUserPlugins(array(), array('bla'=>'testjtplcontentUserFunction'));
        foreach($this->contentUntrusted as $k=>$t){
            try{
                $this->assertEqualOrDiff($t[1], $compil->compileContent2($t[0]));
            }catch(Exception $e){
                $this->fail("Test '$k', Unknown Exception: ".$e->getMessage());
            }
        }
    }


   protected $contentPlugins = array(
/*1=>array(
        '<p>ok {zone \'toto\'}</p>',
        '<p>ok <?php echo jZone::get(\'toto\');?></p>',
        ),*/
);

    function testCompilePlugins() {
        $compil = new testJtplContentCompiler();
        $compil->outputType = 'html';
        $compil->trusted = true;

        foreach($this->contentPlugins as $k=>$t){
            try{
                $this->assertEqualOrDiff($t[1], $compil->compileContent2($t[0]));
            }catch(Exception $e){
                $this->fail("Test '$k', Unknown Exception: ".$e->getMessage());
            }
        }
    }

    protected $tplerrors = array(
         0=>array('{if $foo}',
                  'Dans le template , la fin d\'un bloc if est manquant'),
         2=>array('{foreach ($t=>$a);} A {/foreach}',
                  'Dans le tag foreach ($t=>$a); du template , le caractère  ; n\'est pas autorisé'),
         3=>array('{for ($i=0;$i<$p;$i++} A {/for}',
                  'Dans le tag for ($i=0;$i<$p;$i++ du template , il y a des erreurs au niveau des parenthèses' ),
         5=>array('{($aaa)}',
                  'Dans le template  La syntaxe de balise ($aaa) est invalide'),
         );

    function testCompileErrors() {

        foreach($this->tplerrors as $k=>$t){
            $compil = new testJtplContentCompiler();
            $compil->outputType = 'html';
            $compil->trusted = true;
            try{
                $compil->compileContent2($t[0]);
                $this->fail("Test '$k', exception didn't happen");
            }catch(Exception $e){
                $this->assertEqual($e->getMessage(), $t[1], "Test '$k': %s ");
            }
        }
    }

    protected $tplerrors2 = array(
         0=>array('{for $i=count($a);$i<$p;$i++} A {/for}',
                  'Dans le tag for $i=count($a);$i<$p;$i++ du template , le caractère  ( n\'est pas autorisé'),
         1=>array('{const \'fff\'}',
                  'Le tag const dans le template  n\'est pas autorisé dans un template sans confiance'),
    );
    function testCompileErrorsUntrusted() {

        foreach($this->tplerrors2 as $k=>$t){
            $compil = new testJtplContentCompiler();
            $compil->outputType = 'html';
            $compil->trusted = false;
            try{
                $compil->compileContent2($t[0]);
                $this->fail("Test '$k', exception didn't happen");
            }catch(Exception $e){
                $this->assertEqual($e->getMessage(), $t[1], "Test '$k': %s ");
            }
        }
    }

}

